<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\BeritaAcara;
use App\Models\Customer;
use Carbon\Carbon;
use App\Services\MikrotikServices;
use Illuminate\Support\Facades\Log;
use App\Services\ChatServices;
use Illuminate\Support\Facades\DB;

class CekPayment extends Command
{
    protected $signature = 'app:cek-payment {--force : Force check all unpaid invoices regardless of due date} {--dry-run : Show what would be done without actually doing it}';
    protected $description = 'Cek invoice: kirim WA jika jatuh tempo, blokir otomatis jika ada invoice yang belum dibayar dan melewati tanggal blokir';

    private $stats = [
        'processed' => 0,
        'wa_success' => 0,
        'wa_failed' => 0,
        'blocked' => 0,
        'errors' => 0,
        'skipped' => 0,
        'with_berita_acara' => 0,
        'ba_expired' => 0,
        'ba_paid_early' => 0,
        'ba_no_longer_needed' => 0,
        'prorate_cases' => 0,
        'full_payment_cases' => 0,
        'soft_deleted_customers' => 0,
        'multiple_unpaid_invoices' => 0,
        'unpaid_invoice_found' => 0,
        'already_blocked' => 0
    ];

    public function handle()
    {
        $startTime = microtime(true);
        $isDryRun = $this->option('dry-run');

        Log::info('🚀 Memulai pengecekan invoice', [
            'mode' => $this->option('force') ? 'force' : 'normal',
            'dry_run' => $isDryRun,
            'logic' => 'Blokir jika ada minimal 1 invoice belum bayar yang melewati tanggal blokir'
        ]);

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - Tidak ada perubahan yang akan disimpan');
        }

        $tanggalHariIni = now('Asia/Jakarta');
        $this->info("📅 Tanggal hari ini: {$tanggalHariIni->format('Y-m-d H:i:s')}");

        try {
            $this->processCustomersInChunks($tanggalHariIni, $isDryRun);
        } catch (\Exception $e) {
            $this->error("❌ Fatal error: " . $e->getMessage());
            Log::error("❌ Fatal error in CekPayment", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->displaySummary(microtime(true) - $startTime, $isDryRun);
        return 0;
    }

    /**
     * Process customers in chunks - FIXED RELATIONSHIP NAME
     */
    private function processCustomersInChunks(Carbon $tanggalHariIni, bool $isDryRun)
    {
        // PERBAIKAN: Gunakan 'invoice' (bukan 'invoices') sesuai nama relasi di model
        $query = Customer::whereHas('invoice', function ($query) {
            $query->where('status_id', 7) // Belum bayar
                ->where('paket_id', '!=', 11);
        })
            ->whereNull('deleted_at') // Hanya yang tidak soft deleted
            ->with(['invoice' => function ($query) { // PERBAIKAN: 'invoice' bukan 'invoices'
                $query->where('status_id', 7) // Hanya invoice belum bayar
                    ->where('paket_id', '!=', 11)
                    ->orderBy('jatuh_tempo', 'asc'); // Urutkan dari yang paling lama
            }, 'router']);

        $totalCustomers = $query->count();

        if ($totalCustomers === 0) {
            $this->info('✅ Tidak ada customer dengan invoice belum bayar.');
            return;
        }

        $this->info("📦 Total customer dengan invoice belum bayar: {$totalCustomers}");

        // Process per customer
        $query->chunk(50, function ($customers) use ($tanggalHariIni, $isDryRun) {
            foreach ($customers as $customer) {
                $this->processSingleCustomer($customer, $tanggalHariIni, $isDryRun);
            }
        });
    }

    /**
     * Process single customer and check all their unpaid invoices - FIXED
     */
    private function processSingleCustomer($customer, Carbon $tanggalHariIni, bool $isDryRun)
    {
        $this->stats['processed']++;

        // Skip jika customer sudah diblokir
        if ($customer->status_id == 9) {
            $this->info("⏭️ Customer sudah diblokir: {$customer->nama_customer}");
            $this->stats['already_blocked']++;
            $this->stats['skipped']++;
            return;
        }

        // PERBAIKAN: Gunakan $customer->invoice (bukan $customer->invoices)
        $unpaidInvoices = $customer->invoice;

        if ($unpaidInvoices->isEmpty()) {
            $this->warn("⚠️ Tidak ada invoice belum bayar untuk: {$customer->nama_customer}");
            $this->stats['skipped']++;
            return;
        }

        $this->stats['unpaid_invoice_found'] += $unpaidInvoices->count();

        if ($unpaidInvoices->count() > 1) {
            $this->stats['multiple_unpaid_invoices']++;
            $this->info("📄 {$customer->nama_customer} memiliki {$unpaidInvoices->count()} invoice belum bayar");
        }

        // Cek Berita Acara aktif
        $activeBeritaAcara = $this->getActiveBeritaAcara($customer);

        // Cari invoice yang harus diproses (yang sudah melewati tanggal blokir)
        $invoiceToProcess = $this->findInvoiceToProcess($unpaidInvoices, $tanggalHariIni, $activeBeritaAcara, $customer);

        if ($invoiceToProcess) {
            $this->processInvoiceForBlocking($invoiceToProcess, $customer, $tanggalHariIni, $isDryRun, $activeBeritaAcara);
        } else {
            // Cek jika ada invoice yang jatuh tempo hari ini untuk kirim WA
            $this->checkForWAReminders($unpaidInvoices, $customer, $tanggalHariIni, $isDryRun, $activeBeritaAcara);
        }
    }

    /**
     * Cari invoice yang harus diproses untuk blokir
     */
    private function findInvoiceToProcess($unpaidInvoices, Carbon $tanggalHariIni, $activeBeritaAcara, $customer)
    {
        foreach ($unpaidInvoices as $invoice) {
            $tanggalBlokir = $this->calculateBlockingDate($invoice, $activeBeritaAcara);

            Log::info("🔍 Cek invoice untuk blokir", [
                'customer' => $customer->nama_customer,
                'invoice_id' => $invoice->id
            ]);

            if ($tanggalHariIni >= $tanggalBlokir) {
                $this->info("🎯 Invoice ditemukan untuk blokir: {$customer->nama_customer} - Invoice ID: {$invoice->id}");
                return $invoice;
            }
        }

        return null;
    }

    /**
     * Calculate blocking date dengan mempertimbangkan Berita Acara
     */
    private function calculateBlockingDate($invoice, $activeBeritaAcara)
    {
        // Jika ada Berita Acara aktif dan masih diperlukan
        if ($activeBeritaAcara && $this->checkIfBeritaAcaraStillNeeded($activeBeritaAcara, $invoice->customer)) {
            $this->stats['with_berita_acara']++;
            return Carbon::parse($activeBeritaAcara->tanggal_selesai_ba)->endOfDay();
        }

        // Logic normal untuk menghitung tanggal blokir
        return $this->calculateNormalBlockingDate($invoice);
    }

    /**
     * Process invoice untuk blokir
     */
    private function processInvoiceForBlocking($invoice, $customer, Carbon $tanggalHariIni, bool $isDryRun, $activeBeritaAcara)
    {
        $chatService = new ChatServices();
        $client = null;

        if (!$isDryRun) {
            try {
                $client = MikrotikServices::connect($customer->router);
                $this->info("🔌 Terhubung ke router: {$customer->router->nama_router}");
            } catch (\Exception $e) {
                $this->error("❌ Gagal konek ke router {$customer->router->nama_router}: " . $e->getMessage());
                Log::error("❌ Gagal konek ke router", [
                    'router_id' => $customer->router->id,
                    'router_name' => $customer->router->nama_router,
                    'error' => $e->getMessage()
                ]);
                $this->stats['errors']++;
                return;
            }
        }

        // Handle Berita Acara jika expired
        if ($activeBeritaAcara && $this->checkIfBeritaAcaraStillNeeded($activeBeritaAcara, $customer)) {
            $this->stats['ba_expired']++;
            $this->handleBeritaAcaraExpired($activeBeritaAcara, $isDryRun);
        }

        $this->handleBlocking($invoice, $customer, $client, $chatService, $isDryRun, $activeBeritaAcara);
    }

    /**
     * Cek untuk kirim WA reminder
     */
    private function checkForWAReminders($unpaidInvoices, $customer, Carbon $tanggalHariIni, bool $isDryRun, $activeBeritaAcara)
    {
        $chatService = new ChatServices();

        foreach ($unpaidInvoices as $invoice) {
            $jatuhTempo = \Carbon\Carbon::parse($invoice->jatuh_tempo); // konversi string jadi Carbon
            if ($tanggalHariIni->toDateString() == $jatuhTempo->toDateString()) {
                $this->handleWAReminder($invoice, $customer, $chatService, $isDryRun, $activeBeritaAcara);
                break; // Hanya kirim 1x per customer
            }
        }
    }

    /**
     * Get active Berita Acara for customer
     */
    private function getActiveBeritaAcara($customer)
    {
        try {
            return BeritaAcara::where('customer_id', $customer->id)
                ->where('tanggal_selesai_ba', '>=', now()->toDateString())
                ->whereHas('customer', function ($query) {
                $query->withTrashed();
                })
                ->orderBy('tanggal_selesai_ba', 'desc')
                ->first();
        } catch (\Exception $e) {
            Log::warning("⚠️ Error getting BeritaAcara for customer {$customer->id}", [
                'customer_id' => $customer->id,
                'customer_name' => $customer->nama_customer ?? 'Unknown',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if Berita Acara is still needed - PERBAIKAN: tambahkan logic untuk invoice collection
     */
    private function checkIfBeritaAcaraStillNeeded($beritaAcara, $customer): bool
    {
        try {
            // Cek apakah invoice yang terkait dengan BA sudah dibayar
            $relatedInvoice = Invoice::find($beritaAcara->invoice_id);

            if (!$relatedInvoice) {
                Log::warning("Invoice terkait BA tidak ditemukan", [
                    'berita_acara_id' => $beritaAcara->id,
                    'invoice_id' => $beritaAcara->invoice_id
                ]);
                return true;
            }

            // Jika invoice sudah dibayar (status_id != 7), maka BA tidak diperlukan
            if ($relatedInvoice->status_id != 7) {
                $this->stats['ba_paid_early']++;

                Log::info("📋 Invoice terkait BA sudah dibayar", [
                    'customer' => $customer->nama_customer,
                    'berita_acara_id' => $beritaAcara->id,
                    'invoice_id' => $relatedInvoice->id,
                    'invoice_status' => $relatedInvoice->status_id,
                    'tanggal_selesai_ba' => $beritaAcara->tanggal_selesai_ba,
                    'reason' => 'Invoice sudah dibayar sebelum BA expired'
                ]);

                return false;
            }

            // PERBAIKAN: Gunakan $customer->invoice (bukan $customer->invoices())
            $newerUnpaidInvoice = $customer->invoice()
                ->where('status_id', 7)
                ->where('id', '>', $relatedInvoice->id)
                ->exists();

            if ($newerUnpaidInvoice) {
                Log::info("📋 Ada invoice baru yang belum dibayar", [
                    'customer' => $customer->nama_customer,
                    'berita_acara_id' => $beritaAcara->id,
                    'old_invoice_id' => $relatedInvoice->id,
                    'reason' => 'Ada invoice baru yang perlu diproses'
                ]);

                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error checking if BA still needed", [
                'berita_acara_id' => $beritaAcara->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            return true;
        }
    }

    /**
     * Handle when Berita Acara is no longer needed
     */
    private function handleBeritaAcaraNoLongerNeeded($beritaAcara, bool $isDryRun)
    {
        if ($isDryRun) {
            $this->info("🧪 [DRY RUN] BA tidak diperlukan lagi: ID {$beritaAcara->id}");
            return;
        }

        Log::info("📋 Berita Acara tidak diperlukan lagi", [
            'berita_acara_id' => $beritaAcara->id,
            'customer_id' => $beritaAcara->customer_id,
            'invoice_id' => $beritaAcara->invoice_id,
            'tanggal_selesai_ba' => $beritaAcara->tanggal_selesai_ba->format('Y-m-d'),
            'reason' => 'Invoice terkait sudah dibayar atau ada invoice baru'
        ]);

        $this->info("📋 BA tidak diperlukan lagi: ID {$beritaAcara->id}");
    }

    /**
     * Calculate normal blocking date
     */
    private function calculateNormalBlockingDate($invoice): Carbon
    {
        $customer = $invoice->customer;
        $jatuhTempo = Carbon::parse($invoice->jatuh_tempo);
        $tanggalBlokirHari = (int) ($invoice->tanggal_blokir ?? 10);
        $hariIni = now('Asia/Jakarta');

        if ($invoice->tagihan != $invoice->paket->harga) {
            // PRORATE: Blokir tanggal 10 bulan depan
            $this->stats['prorate_cases']++;

            $tanggalBlokir = $jatuhTempo->copy()->addMonthNoOverflow()
                ->day($tanggalBlokirHari)
                ->endOfDay();

            Log::info("📊 Prorate: Blokir bulan depan", [
                'customer' => $customer->nama_customer,
                'tagihan' => $invoice->tagihan,
                'harga_paket' => $invoice->paket->harga,
                'jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
                'tanggal_blokir_hari' => $tanggalBlokirHari,
                'tanggal_blokir' => $tanggalBlokir->format('Y-m-d H:i:s'),
                'reason' => 'Prorate - blokir bulan depan sesuai setting'
            ]);
        } else {
            // FULL PAYMENT: Ketika sudah melewati tanggal blokir langsung blokir
            $this->stats['full_payment_cases']++;

            $tanggalBlokir = $jatuhTempo->copy()
                ->day($tanggalBlokirHari)
                ->endOfDay();

            Log::info("📊 Full payment: Blokir sesuai tanggal blokir", [
                'customer' => $customer->nama_customer,
                'tagihan' => $invoice->tagihan,
                'harga_paket' => $invoice->paket->harga,
                'jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
                'tanggal_blokir_hari' => $tanggalBlokirHari,
                'tanggal_blokir' => $tanggalBlokir->format('Y-m-d H:i:s'),
                'hari_ini' => $hariIni->format('Y-m-d H:i:s'),
                'akan_diblokir' => $hariIni >= $tanggalBlokir ? 'Ya' : 'Tidak',
                'reason' => 'Full payment - blokir jika sudah melewati tanggal blokir'
            ]);
        }

        return $tanggalBlokir;
    }

    /**
     * Handle when Berita Acara expires
     */
    private function handleBeritaAcaraExpired($beritaAcara, bool $isDryRun)
    {
        if ($isDryRun) {
            $this->info("🧪 [DRY RUN] BA sudah expired: ID {$beritaAcara->id}");
            return;
        }

        Log::info("📋 Berita Acara expired", [
            'berita_acara_id' => $beritaAcara->id,
            'customer_id' => $beritaAcara->customer_id,
            'tanggal_selesai_ba' => $beritaAcara->tanggal_selesai_ba->format('Y-m-d'),
            'days_overdue' => now()->diffInDays($beritaAcara->tanggal_selesai_ba)
        ]);

        $this->info("📋 BA expired: ID {$beritaAcara->id}");
    }

    /**
     * Handle customer blocking
     */
    private function handleBlocking($invoice, $customer, $client, $chatService, bool $isDryRun, $beritaAcara = null)
    {
        if ($isDryRun) {
            $baInfo = $beritaAcara ? " (BA ID: {$beritaAcara->id} expired)" : "";
            $invoiceType = $invoice->tagihan != $invoice->paket->harga ? " (Prorate)" : " (Full Payment)";
            $this->info("🧪 [DRY RUN] Akan memblokir: {$customer->nama_customer}{$baInfo}{$invoiceType}");
            $this->stats['blocked']++;
            return;
        }

        try {
            // Block user in MikroTik
            $blok = MikrotikServices::changeUserProfile($client, $customer->usersecret);

            if ($blok) {
                // Update customer status in transaction
                DB::transaction(function () use ($customer, $invoice, $beritaAcara) {
                    $customer->status_id = 9;
                    $customer->save();

                    activity('Sistem')
                        ->log($customer->nama_customer . ' Di blokir oleh sistem karena belum melakukan pembayaran bulanan');
                });

                // Send blocking notification
                $this->sendBlockingNotification($customer, $invoice, $chatService, $beritaAcara);

                // Remove active connections
                $this->removeActiveConnections($customer, $client, $beritaAcara);

                $this->stats['blocked']++;
                $this->info("🔒 Blokir sukses: {$customer->nama_customer}");
            } else {
                $this->error("❌ Gagal memblokir: {$customer->nama_customer}");
                Log::error("❌ Gagal memblokir customer", [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->nama_customer,
                    'usersecret' => $customer->usersecret
                ]);
                $this->stats['errors']++;
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception saat memblokir {$customer->nama_customer}: " . $e->getMessage());
            Log::error("❌ Exception blocking customer", [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->stats['errors']++;
        }
    }

    /**
     * Send blocking notification
     */
    private function sendBlockingNotification($customer, $invoice, $chatService, $beritaAcara = null)
    {
        try {
            if ($beritaAcara && method_exists($chatService, 'kirimNotifikasiBlokirSetelahBA')) {
                $chatService->kirimNotifikasiBlokirSetelahBA($customer->no_hp, $invoice, $beritaAcara);
            } else {
                $chatService->kirimNotifikasiBlokir($customer->no_hp, $invoice);
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Gagal kirim notifikasi blokir", [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove active connections
     */
    private function removeActiveConnections($customer, $client, $beritaAcara = null)
    {
        try {
            $removed = MikrotikServices::removeActiveConnections($client, $customer->usersecret);

            $baInfo = $beritaAcara ? " (setelah BA expired)" : "";
            if ($removed) {
                $this->info("🔌 Koneksi dihapus: {$customer->nama_customer}{$baInfo}");
            } else {
                $this->warn("⚠️ Gagal hapus koneksi: {$customer->nama_customer}{$baInfo}");
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Error removing connections", [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle WA reminder
     */
    private function handleWAReminder($invoice, $customer, $chatService, bool $isDryRun, $beritaAcara = null)
    {
        if ($isDryRun) {
            $baInfo = $beritaAcara ? " (memiliki BA aktif)" : "";
            $this->info("🧪 [DRY RUN] Akan kirim WA ke: {$customer->nama_customer}{$baInfo}");
            $this->stats['wa_success']++;
            return;
        }

        try {
            // Send different message if customer has active BA
            if ($beritaAcara && method_exists($chatService, 'kirimInvoiceDenganBA')) {
                $res = $chatService->kirimInvoiceDenganBA($customer->no_hp, $invoice, $beritaAcara);
            } else {
                $res = $chatService->kirimInvoice($customer->no_hp, $invoice);
            }

            if (isset($res['error']) && $res['error']) {
                $msg = "❌ Gagal kirim WA ke {$customer->no_hp}: " . ($res['pesan'] ?? 'Unknown error');
                $this->error($msg);
                Log::error($msg, [
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'has_berita_acara' => $beritaAcara ? true : false,
                    'response' => $res
                ]);
                $this->stats['wa_failed']++;
            } else {
                $baInfo = $beritaAcara ? " (dengan info BA)" : "";
                $msg = "📩 WA berhasil dikirim ke {$customer->nama_customer}{$baInfo}";
                $this->info($msg);
                Log::info($msg, [
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                    'has_berita_acara' => $beritaAcara ? true : false,
                    'berita_acara_id' => $beritaAcara ? $beritaAcara->id : null
                ]);
                $this->stats['wa_success']++;
            }
        } catch (\Exception $e) {
            $msg = "❌ Exception kirim WA ke {$customer->nama_customer}: " . $e->getMessage();
            $this->error($msg);
            Log::error($msg, [
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->stats['wa_failed']++;
        }
    }

    /**
     * Display execution summary
     */
    private function displaySummary(float $duration, bool $isDryRun)
    {
        $duration = round($duration, 2);

        $this->info('');
        $this->info('📊 =============== SUMMARY ===============');
        $this->info("👥 Total customer diproses: {$this->stats['processed']}");
        $this->info("📄 Total invoice belum bayar: {$this->stats['unpaid_invoice_found']}");
        $this->info("🔢 Multiple unpaid invoices: {$this->stats['multiple_unpaid_invoices']}");
        $this->info("📩 WA berhasil: {$this->stats['wa_success']}");
        $this->info("❌ WA gagal: {$this->stats['wa_failed']}");
        $this->info("🔒 Diblokir: {$this->stats['blocked']}");
        $this->info("⏭️ Sudah diblokir: {$this->stats['already_blocked']}");
        $this->info("📋 Dengan BA aktif: {$this->stats['with_berita_acara']}");
        $this->info("⏰ BA expired: {$this->stats['ba_expired']}");
        $this->info("💳 BA dibayar lebih awal: {$this->stats['ba_paid_early']}");
        $this->info("💰 Prorate cases: {$this->stats['prorate_cases']}");
        $this->info("💳 Full payment cases: {$this->stats['full_payment_cases']}");
        $this->info("🗑️ Customer soft deleted: {$this->stats['soft_deleted_customers']}");
        $this->info("⏭️ Dilewat: {$this->stats['skipped']}");
        $this->info("🚨 Error: {$this->stats['errors']}");
        $this->info("⏱️ Durasi: {$duration}s");

        if ($isDryRun) {
            $this->warn("🧪 DRY RUN - Tidak ada perubahan yang disimpan");
        }

        $this->info('========================================');

        Log::info('📊 CekPayment Summary - Multi Invoice Logic', array_merge($this->stats, [
            'duration' => $duration,
            'dry_run' => $isDryRun
        ]));
    }
}