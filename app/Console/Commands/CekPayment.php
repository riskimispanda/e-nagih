<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\BeritaAcara;
use Carbon\Carbon;
use App\Services\MikrotikServices;
use Illuminate\Support\Facades\Log;
use App\Services\ChatServices;
use Illuminate\Support\Facades\DB;

class CekPayment extends Command
{
    protected $signature = 'app:cek-payment {--force : Force check all unpaid invoices regardless of due date} {--dry-run : Show what would be done without actually doing it}';
    protected $description = 'Cek invoice: kirim WA jika jatuh tempo, blokir otomatis jika lewat tanggal_blokir atau tanggal_selesai_ba';

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
        'full_payment_cases' => 0
    ];

    public function handle()
    {
        $startTime = microtime(true);
        $isDryRun = $this->option('dry-run');

        Log::info('🚀 Memulai pengecekan invoice', [
            'mode' => $this->option('force') ? 'force' : 'normal',
            'dry_run' => $isDryRun
        ]);

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - Tidak ada perubahan yang akan disimpan');
        }

        $tanggalHariIni = now('Asia/Jakarta');
        $this->info("📅 Tanggal hari ini: {$tanggalHariIni->format('Y-m-d H:i:s')}");

        try {
            $this->processInvoicesInChunks($tanggalHariIni, $isDryRun);
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

    private function processInvoicesInChunks(Carbon $tanggalHariIni, bool $isDryRun)
    {
        $query = Invoice::where('status_id', 7)->where('paket_id', '!=', 11);

        if (!$this->option('force')) {
            $this->info("🔍 Mode normal: proses semua invoice belum bayar.");
        } else {
            $this->info('⚠️ Mode force aktif: proses semua invoice belum bayar.');
        }

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('✅ Tidak ada invoice yang perlu diproses.');
            return;
        }

        $this->info("📦 Total invoice ditemukan: {$totalCount}");

        // Process in chunks - Load basic relationships only
        $query->with(['customer.router', 'paket'])->chunk(50, function ($invoices) use ($tanggalHariIni, $isDryRun) {
            $this->processInvoiceChunk($invoices, $tanggalHariIni, $isDryRun);
        });
    }

    private function processInvoiceChunk($invoices, Carbon $tanggalHariIni, bool $isDryRun)
    {
        $chatService = new ChatServices();

        // Group invoices by router to optimize connections
        $invoicesByRouter = $invoices->groupBy('customer.router.id');

        foreach ($invoicesByRouter as $routerId => $routerInvoices) {
            $router = $routerInvoices->first()->customer->router;

            if (!$router) {
                $this->warn("⚠️ Router tidak ditemukan untuk beberapa invoice");
                $this->stats['skipped'] += $routerInvoices->count();
                continue;
            }

            $this->processRouterInvoices($routerInvoices, $router, $chatService, $tanggalHariIni, $isDryRun);
        }
    }

    private function processRouterInvoices($invoices, $router, $chatService, Carbon $tanggalHariIni, bool $isDryRun)
    {
        $client = null;

        try {
            if (!$isDryRun) {
                $client = MikrotikServices::connect($router);
                $this->info("🔌 Terhubung ke router: {$router->nama_router}");
            }
        } catch (\Exception $e) {
            Log::error("❌ Gagal konek ke router {$router->nama_router}", [
                'error' => $e->getMessage(),
                'router_id' => $router->id
            ]);
            $this->error("❌ Gagal konek ke router {$router->nama_router}: " . $e->getMessage());
            $this->stats['errors'] += $invoices->count();
            return;
        }

        foreach ($invoices as $invoice) {
            $this->stats['processed']++;
            $this->processSingleInvoice($invoice, $client, $chatService, $tanggalHariIni, $isDryRun);
        }
    }

    private function processSingleInvoice($invoice, $client, $chatService, Carbon $tanggalHariIni, bool $isDryRun)
    {
        $customer = $invoice->customer;

        if (!$customer || empty($customer->no_hp)) {
            $this->warn("⚠️ Customer ID {$invoice->customer_id} tidak valid atau nomor HP kosong");
            $this->stats['skipped']++;
            return;
        }

        // Check for active Berita Acara first
        $activeBeritaAcara = $this->getActiveBeritaAcara($customer);

        if ($activeBeritaAcara) {
            // Cek apakah BA masih diperlukan (invoice terkait sudah dibayar atau belum)
            $baStillNeeded = $this->checkIfBeritaAcaraStillNeeded($activeBeritaAcara, $customer);

            if (!$baStillNeeded) {
                // BA tidak diperlukan lagi karena invoice sudah dibayar
                $this->stats['ba_no_longer_needed']++;
                $this->handleBeritaAcaraNoLongerNeeded($activeBeritaAcara, $isDryRun);

                // Gunakan logic normal karena BA tidak diperlukan
                $tanggalBlokir = $this->calculateNormalBlockingDate($invoice);

                Log::info("📋 BA tidak diperlukan lagi, menggunakan logic normal", [
                    'customer' => $customer->nama_customer,
                    'berita_acara_id' => $activeBeritaAcara->id,
                    'reason' => 'Invoice terkait BA sudah dibayar'
                ]);
            } else {
                // BA masih diperlukan
                $this->stats['with_berita_acara']++;
                $tanggalBlokir = $this->calculateBlockingDateWithBeritaAcara($invoice, $activeBeritaAcara);

                Log::info("📋 Customer memiliki Berita Acara aktif", [
                    'customer' => $customer->nama_customer,
                    'berita_acara_id' => $activeBeritaAcara->id,
                    'tanggal_selesai_ba' => $activeBeritaAcara->tanggal_selesai_ba,
                    'tanggal_blokir_ba' => $tanggalBlokir->format('Y-m-d H:i:s')
                ]);
            }
        } else {
            // Use normal blocking date calculation
            $tanggalBlokir = $this->calculateNormalBlockingDate($invoice);
        }

        // Check if should be blocked
        if ($tanggalHariIni >= $tanggalBlokir) {
            if ($activeBeritaAcara && $baStillNeeded) {
                $this->stats['ba_expired']++;
                $this->handleBeritaAcaraExpired($activeBeritaAcara, $isDryRun);
            }

            $this->handleBlocking($invoice, $customer, $client, $chatService, $isDryRun, $activeBeritaAcara);
            return;
        }

        // Check if should send WA reminder
        if ($tanggalHariIni->toDateString() == $invoice->jatuh_tempo) {
            $this->handleWAReminder($invoice, $customer, $chatService, $isDryRun, $activeBeritaAcara);
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
     * Check if Berita Acara is still needed - NEW METHOD
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
                return true; // Tetap gunakan BA jika invoice tidak ditemukan
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

                return false; // BA tidak diperlukan lagi
            }

            // Cek apakah ada invoice lain yang lebih baru dan belum dibayar
            $newerUnpaidInvoice = $customer->invoice()
                ->where('status_id', 7) // Belum bayar
                ->where('id', '>', $relatedInvoice->id) // Invoice lebih baru
                ->exists();

            if ($newerUnpaidInvoice) {
                Log::info("📋 Ada invoice baru yang belum dibayar", [
                    'customer' => $customer->nama_customer,
                    'berita_acara_id' => $beritaAcara->id,
                    'old_invoice_id' => $relatedInvoice->id,
                    'reason' => 'Ada invoice baru yang perlu diproses'
                ]);

                return false; // BA untuk invoice lama, gunakan logic normal untuk invoice baru
            }

            return true; // BA masih diperlukan

        } catch (\Exception $e) {
            Log::error("Error checking if BA still needed", [
                'berita_acara_id' => $beritaAcara->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            return true; // Default: tetap gunakan BA jika ada error
        }
    }

    /**
     * Handle when Berita Acara is no longer needed - NEW METHOD
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
     * Calculate blocking date when customer has active Berita Acara
     */
    private function calculateBlockingDateWithBeritaAcara($invoice, $beritaAcara): Carbon
    {
        // Use tanggal_selesai_ba as blocking date
        $tanggalBlokir = Carbon::parse($beritaAcara->tanggal_selesai_ba)->endOfDay();

        Log::info("📋 Menggunakan tanggal selesai BA sebagai tanggal blokir", [
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'berita_acara_id' => $beritaAcara->id,
            'tanggal_selesai_ba' => $beritaAcara->tanggal_selesai_ba,
            'tanggal_blokir' => $tanggalBlokir->format('Y-m-d H:i:s')
        ]);

        return $tanggalBlokir;
    }

    /**
     * Calculate normal blocking date - MENGGUNAKAN LOGIC ASLI USER
     */
    private function calculateNormalBlockingDate($invoice): Carbon
    {
        $customer = $invoice->customer;
        $jatuhTempo = Carbon::parse($invoice->jatuh_tempo);
        $tanggalBlokirHari = (int) ($invoice->tanggal_blokir ?? 10);
        $hariIni = now('Asia/Jakarta');

        if ($invoice->tagihan != $invoice->paket->harga) {
            // PRORATE: Blokir tanggal 10 bulan depan (sesuai permintaan user)
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
            // FULL PAYMENT: Ketika sudah melewati tanggal blokir langsung blokir (sesuai permintaan user)
            $this->stats['full_payment_cases']++;

            // Hitung tanggal blokir berdasarkan jatuh tempo
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
     * Handle when Berita Acara expires - SIMPLIFIED
     */
    private function handleBeritaAcaraExpired($beritaAcara, bool $isDryRun)
    {
        if ($isDryRun) {
            $this->info("🧪 [DRY RUN] BA sudah expired: ID {$beritaAcara->id}");
            return;
        }

        // Tidak perlu update apapun karena tidak ada field status/expired_at
        // Cukup log saja bahwa BA sudah expired
        Log::info("📋 Berita Acara expired", [
            'berita_acara_id' => $beritaAcara->id,
            'customer_id' => $beritaAcara->customer_id,
            'tanggal_selesai_ba' => $beritaAcara->tanggal_selesai_ba->format('Y-m-d'),
            'days_overdue' => now()->diffInDays($beritaAcara->tanggal_selesai_ba)
        ]);

        $this->info("📋 BA expired: ID {$beritaAcara->id}");
    }

    /**
     * Handle customer blocking - SIMPLIFIED
     */
    private function handleBlocking($invoice, $customer, $client, $chatService, bool $isDryRun, $beritaAcara = null)
    {
        if ($customer->status_id == 9) {
            $this->info("⏭️ Customer sudah diblokir: {$customer->nama_customer}");
            $this->stats['skipped']++;
            return;
        }

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

                    Log::info("🔒 Customer diblokir", [
                        'customer_id' => $customer->id,
                        'invoice_id' => $invoice->id,
                        'nama_customer' => $customer->nama_customer,
                        'had_berita_acara' => $beritaAcara ? true : false,
                        'berita_acara_id' => $beritaAcara ? $beritaAcara->id : null,
                        'blocking_reason' => $beritaAcara ? 'BA expired' : 'Normal blocking date reached',
                        'is_prorate' => $invoice->tagihan != $invoice->paket->harga,
                        'tagihan' => $invoice->tagihan,
                        'harga_paket' => $invoice->paket->harga
                    ]);
                });

                // Send blocking notification
                $this->sendBlockingNotification($customer, $invoice, $chatService, $beritaAcara);

                // Remove active connections
                $this->removeActiveConnections($customer, $client, $beritaAcara);

                $this->stats['blocked']++;
            } else {
                $this->error("❌ Gagal memblokir: {$customer->nama_customer}");
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
     * Send blocking notification - EXTRACTED METHOD
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
     * Remove active connections - EXTRACTED METHOD
     */
    private function removeActiveConnections($customer, $client, $beritaAcara = null)
    {
        try {
            $removed = MikrotikServices::removeActiveConnections($client, $customer->usersecret);

            $baInfo = $beritaAcara ? " (setelah BA expired)" : "";
            if ($removed) {
                $this->info("🔒 Blokir sukses & koneksi dihapus: {$customer->nama_customer}{$baInfo}");
            } else {
                $this->warn("⚠️ Blokir sukses, tapi gagal hapus koneksi: {$customer->nama_customer}{$baInfo}");
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Error removing connections", [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle WA reminder - IMPROVED
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
        $this->info("📦 Total diproses: {$this->stats['processed']}");
        $this->info("📩 WA berhasil: {$this->stats['wa_success']}");
        $this->info("❌ WA gagal: {$this->stats['wa_failed']}");
        $this->info("🔒 Diblokir: {$this->stats['blocked']}");
        $this->info("📋 Dengan BA aktif: {$this->stats['with_berita_acara']}");
        $this->info("⏰ BA expired: {$this->stats['ba_expired']}");
        $this->info("💳 BA dibayar lebih awal: {$this->stats['ba_paid_early']}");
        $this->info("🚫 BA tidak diperlukan lagi: {$this->stats['ba_no_longer_needed']}");
        $this->info("💰 Prorate cases: {$this->stats['prorate_cases']}");
        $this->info("💳 Full payment cases: {$this->stats['full_payment_cases']}");
        $this->info("⏭️ Dilewat: {$this->stats['skipped']}");
        $this->info("🚨 Error: {$this->stats['errors']}");
        $this->info("⏱️ Durasi: {$duration}s");

        if ($isDryRun) {
            $this->warn("🧪 DRY RUN - Tidak ada perubahan yang disimpan");
        }

        $this->info('========================================');

        Log::info('📊 CekPayment Summary', array_merge($this->stats, [
            'duration' => $duration,
            'dry_run' => $isDryRun
        ]));
    }
}