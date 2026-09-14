<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Models\Kas;
use App\Services\MikrotikServices;
use App\Services\ChatServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class PaymentCorrectionService
{
    /**
     * Analisa data kasus salah bayar (Read-only / Safe check)
     *
     * @param int $payerCustomerId ID Pelanggan yang seharusnya bayar (contoh: 12141)
     * @param int $wrongCustomerId ID Pelanggan yang salah dibayar (contoh: 14552)
     * @param int|null $wrongInvoiceId ID Invoice 14552 (opsional, jika null ambil invoice paid terbaru)
     * @param int|null $targetInvoiceId ID Invoice 12141 (opsional, jika null ambil invoice unpaid tertua/terbaru)
     * @return array
     */
    public function analyze(
        int $payerCustomerId,
        int $wrongCustomerId,
        ?int $wrongInvoiceId = null,
        ?int $targetInvoiceId = null
    ): array {
        try {
            // 1. Ambil Data Pelanggan
            $payerCustomer = Customer::with(['paket', 'status', 'router'])->find($payerCustomerId);
            if (!$payerCustomer) {
                return [
                    'can_proceed' => false,
                    'message' => "Customer pembayar (ID: {$payerCustomerId}) tidak ditemukan."
                ];
            }

            $wrongCustomer = Customer::with(['paket', 'status', 'router'])->find($wrongCustomerId);
            if (!$wrongCustomer) {
                return [
                    'can_proceed' => false,
                    'message' => "Customer yang salah dibayar (ID: {$wrongCustomerId}) tidak ditemukan."
                ];
            }

            // 2. Cari Invoice Keliru milik Customer 14552
            $wrongInvoice = null;
            if ($wrongInvoiceId) {
                $wrongInvoice = Invoice::with(['pembayaran', 'status', 'paket'])
                    ->where('customer_id', $wrongCustomerId)
                    ->where('id', $wrongInvoiceId)
                    ->first();
            } else {
                // Prioritas 1: Invoice status 8 (Sudah Bayar) terbaru
                $wrongInvoice = Invoice::with(['pembayaran', 'status', 'paket'])
                    ->where('customer_id', $wrongCustomerId)
                    ->where('status_id', 8)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                // Prioritas 2: Invoice yang memiliki reference Tripay
                if (!$wrongInvoice) {
                    $wrongInvoice = Invoice::with(['pembayaran', 'status', 'paket'])
                        ->where('customer_id', $wrongCustomerId)
                        ->whereNotNull('reference')
                        ->orderBy('updated_at', 'desc')
                        ->first();
                }

                // Prioritas 3: Invoice yang memiliki record di tabel pembayaran
                if (!$wrongInvoice) {
                    $wrongInvoice = Invoice::with(['pembayaran', 'status', 'paket'])
                        ->where('customer_id', $wrongCustomerId)
                        ->whereHas('pembayaran')
                        ->orderBy('updated_at', 'desc')
                        ->first();
                }
            }

            if (!$wrongInvoice) {
                return [
                    'can_proceed' => false,
                    'message' => "Invoice lunas / berbayar milik Customer {$wrongCustomerId} ({$wrongCustomer->nama_customer}) tidak ditemukan."
                ];
            }

            // Cari record Pembayaran terkait invoice keliru
            $pembayaran = Pembayaran::where('invoice_id', $wrongInvoice->id)
                ->orderBy('id', 'desc')
                ->first();

            // Cari record Kas terkait pembayaran / invoice keliru
            // Catatan: Kolom tabel kas tidak memiliki 'pembayaran_id', gunakan customer_id atau matching keterangan
            $kas = Kas::where('customer_id', $wrongCustomerId)
                ->where('debit', '>', 0)
                ->orderBy('id', 'desc')
                ->first();

            if (!$kas) {
                $kas = Kas::where(function ($q) use ($wrongInvoice, $wrongCustomer) {
                    $q->where('keterangan', 'like', "%#{$wrongInvoice->id}%")
                      ->orWhere('keterangan', 'like', "%{$wrongCustomer->nama_customer}%");
                })
                ->where('debit', '>', 0)
                ->orderBy('id', 'desc')
                ->first();
            }

            // 3. Cari Invoice Target milik Customer 12141 yang seharusnya dibayar
            $targetInvoice = null;
            if ($targetInvoiceId) {
                $targetInvoice = Invoice::with(['status', 'paket'])
                    ->where('customer_id', $payerCustomerId)
                    ->where('id', $targetInvoiceId)
                    ->first();
            } else {
                // Prioritas 1: Invoice belum bayar (status_id = 7) tertua / jatuh tempo terdekat
                $targetInvoice = Invoice::with(['status', 'paket'])
                    ->where('customer_id', $payerCustomerId)
                    ->where('status_id', 7)
                    ->orderBy('jatuh_tempo', 'asc')
                    ->first();

                // Prioritas 2: Invoice terbaru apa pun statusnya jika belum ada yang status 7
                if (!$targetInvoice) {
                    $targetInvoice = Invoice::with(['status', 'paket'])
                        ->where('customer_id', $payerCustomerId)
                        ->orderBy('jatuh_tempo', 'desc')
                        ->first();
                }
            }

            if (!$targetInvoice) {
                return [
                    'can_proceed' => false,
                    'message' => "Invoice target milik Customer {$payerCustomerId} ({$payerCustomer->nama_customer}) tidak ditemukan."
                ];
            }

            // 4. Deteksi apakah ada invoice bulan depan yang terbuat otomatis untuk Customer 14552
            $autoGeneratedNextInvoice = null;
            if (!empty($wrongInvoice->jatuh_tempo)) {
                try {
                    $bulanDepan = Carbon::parse($wrongInvoice->jatuh_tempo)->addMonthNoOverflow();
                    $autoGeneratedNextInvoice = Invoice::where('customer_id', $wrongCustomerId)
                        ->where('id', '!=', $wrongInvoice->id)
                        ->whereMonth('jatuh_tempo', $bulanDepan->month)
                        ->whereYear('jatuh_tempo', $bulanDepan->year)
                        ->where('status_id', 7) // Belum bayar
                        ->first();
                } catch (Throwable $e) {
                    Log::warning("Gagal parsing jatuh tempo untuk invoice {$wrongInvoice->id}: " . $e->getMessage());
                }
            }

            // 5. Analisa Selisih Nominal Harga Paket
            $paidAmount = $pembayaran
                ? (float) $pembayaran->jumlah_bayar
                : (float) ($wrongInvoice->tagihan + ($wrongInvoice->tambahan ?? 0));

            $targetBill = (float) (
                $targetInvoice->tagihan +
                ($targetInvoice->tambahan ?? 0) +
                ($targetInvoice->tunggakan ?? 0) -
                ($targetInvoice->saldo ?? 0)
            );

            $diff = $paidAmount - $targetBill;

            $pricingStatus = 'exact_match';
            if ($diff > 0) {
                $pricingStatus = 'overpaid'; // Kelebihan bayar
            } elseif ($diff < 0) {
                $pricingStatus = 'underpaid'; // Kurang bayar
            }

            $payerPaketName = $payerCustomer->paket?->nama_paket ?? $payerCustomer->paket?->paket_name ?? 'N/A';
            $wrongPaketName = $wrongCustomer->paket?->nama_paket ?? $wrongCustomer->paket?->paket_name ?? 'N/A';

            return [
                'can_proceed' => true,
                'payer_customer' => [
                    'id' => $payerCustomer->id,
                    'nama' => $payerCustomer->nama_customer,
                    'no_hp' => $payerCustomer->no_hp,
                    'status_id' => $payerCustomer->status_id,
                    'status_nama' => $payerCustomer->status?->nama_status ?? 'N/A',
                    'paket' => $payerPaketName,
                    'harga_paket' => $payerCustomer->paket?->harga ?? 0,
                    'is_blocked' => ($payerCustomer->status_id == 9),
                ],
                'wrong_customer' => [
                    'id' => $wrongCustomer->id,
                    'nama' => $wrongCustomer->nama_customer,
                    'no_hp' => $wrongCustomer->no_hp,
                    'status_id' => $wrongCustomer->status_id,
                    'status_nama' => $wrongCustomer->status?->nama_status ?? 'N/A',
                    'paket' => $wrongPaketName,
                    'harga_paket' => $wrongCustomer->paket?->harga ?? 0,
                ],
                'wrong_invoice' => [
                    'id' => $wrongInvoice->id,
                    'merchant_ref' => $wrongInvoice->merchant_ref,
                    'reference' => $wrongInvoice->reference,
                    'status_id' => $wrongInvoice->status_id,
                    'status_nama' => $wrongInvoice->status?->nama_status ?? 'N/A',
                    'tagihan' => $wrongInvoice->tagihan,
                    'tambahan' => $wrongInvoice->tambahan,
                    'metode_bayar' => $wrongInvoice->metode_bayar,
                    'jatuh_tempo' => $wrongInvoice->jatuh_tempo,
                ],
                'target_invoice' => [
                    'id' => $targetInvoice->id,
                    'merchant_ref' => $targetInvoice->merchant_ref,
                    'status_id' => $targetInvoice->status_id,
                    'status_nama' => $targetInvoice->status?->nama_status ?? 'N/A',
                    'tagihan' => $targetInvoice->tagihan,
                    'tambahan' => $targetInvoice->tambahan,
                    'tunggakan' => $targetInvoice->tunggakan ?? 0,
                    'saldo' => $targetInvoice->saldo ?? 0,
                    'jatuh_tempo' => $targetInvoice->jatuh_tempo,
                ],
                'pembayaran' => $pembayaran ? [
                    'id' => $pembayaran->id,
                    'invoice_id' => $pembayaran->invoice_id,
                    'jumlah_bayar' => $pembayaran->jumlah_bayar,
                    'metode_bayar' => $pembayaran->metode_bayar,
                    'tanggal_bayar' => $pembayaran->tanggal_bayar,
                    'keterangan' => $pembayaran->keterangan,
                ] : null,
                'kas' => $kas ? [
                    'id' => $kas->id,
                    'debit' => $kas->debit,
                    'tanggal_kas' => $kas->tanggal_kas,
                    'keterangan' => $kas->keterangan,
                ] : null,
                'auto_generated_next_invoice_wrong_customer' => $autoGeneratedNextInvoice ? [
                    'id' => $autoGeneratedNextInvoice->id,
                    'customer_id' => $autoGeneratedNextInvoice->customer_id,
                    'tagihan' => $autoGeneratedNextInvoice->tagihan,
                    'status_id' => $autoGeneratedNextInvoice->status_id,
                    'jatuh_tempo' => $autoGeneratedNextInvoice->jatuh_tempo,
                    'created_at' => $autoGeneratedNextInvoice->created_at,
                ] : null,
                'pricing_analysis' => [
                    'paid_amount' => $paidAmount,
                    'target_bill' => $targetBill,
                    'difference' => $diff,
                    'status' => $pricingStatus,
                    'notes' => match ($pricingStatus) {
                        'exact_match' => 'Nominal pembayaran sama persis dengan total tagihan target.',
                        'overpaid' => 'Terdapat kelebihan pembayaran sebesar Rp ' . number_format($diff, 0, ',', '.') . ' yang akan dialokasikan ke saldo.',
                        'underpaid' => 'Terdapat kekurangan pembayaran sebesar Rp ' . number_format(abs($diff), 0, ',', '.') . '.',
                    }
                ],
                'actions_preview' => [
                    "Alihkan Pembayaran ID " . ($pembayaran?->id ?? 'Baru') . " dari Invoice #{$wrongInvoice->id} ke Invoice #{$targetInvoice->id}.",
                    "Ubah status Invoice #{$targetInvoice->id} (Customer 12141) menjadi Lunas (status_id: 8).",
                    "Kembalikan status Invoice #{$wrongInvoice->id} (Customer 14552) menjadi Belum Bayar (status_id: 7).",
                    $autoGeneratedNextInvoice
                        ? "Hapus invoice bulan depan #{$autoGeneratedNextInvoice->id} milik Customer 14552 yang terbuat otomatis saat salah bayar."
                        : "Tidak ada invoice bulan depan 14552 yang perlu dihapus.",
                    "Buat invoice bulan depan untuk Customer 12141 (jika belum ada).",
                    $payerCustomer->status_id == 9
                        ? "Unblock koneksi MikroTik dan ubah status Customer 12141 menjadi Aktif (status_id: 3)."
                        : "Status Customer 12141 saat ini: " . ($payerCustomer->status?->nama_status ?? 'Aktif') . ".",
                    "Perbarui catatan kas agar mencantumkan Customer 12141 ({$payerCustomer->nama_customer}).",
                    "Catat log aktivitas di audit trail."
                ]
            ];
        } catch (Throwable $e) {
            Log::error("Error in PaymentCorrectionService@analyze: " . $e->getMessage(), [
                'payer_id' => $payerCustomerId,
                'wrong_id' => $wrongCustomerId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'can_proceed' => false,
                'message' => 'Terjadi kesalahan saat menganalisa: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ];
        }
    }

    /**
     * Eksekusi Perbaikan Kasus Salah Bayar secara atomik
     *
     * @param array $options
     * @return array
     */
    public function execute(array $options): array
    {
        $payerCustomerId = (int) ($options['payer_customer_id'] ?? 0);
        $wrongCustomerId = (int) ($options['wrong_customer_id'] ?? 0);
        $wrongInvoiceId = isset($options['wrong_invoice_id']) ? (int) $options['wrong_invoice_id'] : null;
        $targetInvoiceId = isset($options['target_invoice_id']) ? (int) $options['target_invoice_id'] : null;

        $dryRun = (bool) ($options['dry_run'] ?? false);
        $sendWa = (bool) ($options['send_wa'] ?? false);
        $revertWrongCustomerStatus = (bool) ($options['revert_wrong_customer_status'] ?? true);
        $deleteWrongNextInvoice = (bool) ($options['delete_wrong_next_invoice'] ?? true);

        // 1. Jalankan analisa awal
        $analysis = $this->analyze($payerCustomerId, $wrongCustomerId, $wrongInvoiceId, $targetInvoiceId);
        if (!$analysis['can_proceed']) {
            return [
                'success' => false,
                'message' => $analysis['message'],
                'data' => null
            ];
        }

        // Jika dry run, return analisa dan rencana aksi tanpa mengubah database
        if ($dryRun) {
            return [
                'success' => true,
                'mode' => 'DRY_RUN',
                'message' => 'Simulasi perbaikan berhasil. Tidak ada data yang diubah di database.',
                'analysis' => $analysis
            ];
        }

        // 2. Eksekusi Perubahan di Database dalam Transaksi Atomik
        DB::beginTransaction();
        try {
            $payerCustomer = Customer::with(['paket', 'router'])->lockForUpdate()->findOrFail($payerCustomerId);
            $wrongCustomer = Customer::with(['paket', 'router'])->lockForUpdate()->findOrFail($wrongCustomerId);
            $targetInvoice = Invoice::lockForUpdate()->findOrFail($analysis['target_invoice']['id']);
            $wrongInvoice = Invoice::lockForUpdate()->findOrFail($analysis['wrong_invoice']['id']);

            $pembayaran = null;
            if (!empty($analysis['pembayaran']['id'])) {
                $pembayaran = Pembayaran::lockForUpdate()->find($analysis['pembayaran']['id']);
            }

            $kas = null;
            if (!empty($analysis['kas']['id'])) {
                $kas = Kas::lockForUpdate()->find($analysis['kas']['id']);
            }

            $paidAmount = (float) $analysis['pricing_analysis']['paid_amount'];
            $metodeBayar = $pembayaran?->metode_bayar ?? $wrongInvoice->metode_bayar ?? 'Tripay';

            // --- A. Alihkan Pembayaran ke Invoice 12141 ---
            if ($pembayaran) {
                $pembayaran->invoice_id = $targetInvoice->id;
                $pembayaran->user_id = $payerCustomer->user_id ?? $pembayaran->user_id;
                $pembayaran->keterangan = "Pembayaran Paket Langganan Via {$metodeBayar} dari: {$payerCustomer->nama_customer} (Koreksi salah bayar link Customer ID {$wrongCustomer->id} - {$wrongCustomer->nama_customer})";
                $pembayaran->save();
            } else {
                // Buat record pembayaran baru jika sebelumnya belum tercatat
                $pembayaran = Pembayaran::create([
                    'invoice_id' => $targetInvoice->id,
                    'user_id' => $payerCustomer->user_id ?? null,
                    'jumlah_bayar' => $paidAmount,
                    'tanggal_bayar' => now(),
                    'metode_bayar' => $metodeBayar,
                    'keterangan' => "Pembayaran Paket Langganan Via {$metodeBayar} dari: {$payerCustomer->nama_customer} (Koreksi salah bayar link Customer ID {$wrongCustomer->id})",
                    'status_id' => 8,
                    'tipe_pembayaran' => Pembayaran::TIPE_REGULER,
                    'saldo' => $targetInvoice->saldo ?? 0,
                ]);
            }

            // --- B. Perbarui Kas ---
            if ($kas) {
                $kas->keterangan = "Pembayaran langganan dari {$payerCustomer->nama_customer} via {$metodeBayar} (Koreksi dari Customer {$wrongCustomer->nama_customer})";
                $kas->customer_id = $payerCustomer->id;
                $kas->save();
            }

            // --- C. Update Target Invoice Customer 12141 (Lunas) ---
            $diff = (float) $analysis['pricing_analysis']['difference'];
            $saldoBaru = (float) ($targetInvoice->saldo ?? 0);

            if ($diff > 0) {
                // Ada kelebihan bayar, tambahkan ke saldo
                $saldoBaru += $diff;
            }

            $targetInvoice->status_id = 8; // Sudah Bayar
            $targetInvoice->metode_bayar = $metodeBayar;
            $targetInvoice->reference = $wrongInvoice->reference;
            $targetInvoice->saldo = $saldoBaru;
            $targetInvoice->save();

            // --- D. Kembalikan Invoice Customer 14552 (Belum Bayar) ---
            $wrongInvoice->status_id = 7; // Belum Bayar
            $wrongInvoice->reference = null; // Kosongkan reference agar bisa dibuat transaksi baru di Tripay
            $wrongInvoice->save();

            // --- E. Hapus Invoice Bulan Depan yang Salah Terbuat untuk 14552 ---
            $deletedNextInvoiceId = null;
            if ($deleteWrongNextInvoice && !empty($analysis['auto_generated_next_invoice_wrong_customer']['id'])) {
                $wrongNextInvoice = Invoice::find($analysis['auto_generated_next_invoice_wrong_customer']['id']);
                if ($wrongNextInvoice && $wrongNextInvoice->status_id == 7) {
                    $deletedNextInvoiceId = $wrongNextInvoice->id;
                    $wrongNextInvoice->delete();
                    Log::info("Invoice bulan depan keliru milik Customer {$wrongCustomer->id} berhasil dihapus (Invoice #{$deletedNextInvoiceId})");
                }
            }

            // --- F. Buat Invoice Bulan Depan untuk Customer 12141 (Jika Belum Ada) ---
            $createdNextInvoiceId = null;
            $jatuhTempo = $targetInvoice->jatuh_tempo;
            if ($jatuhTempo) {
                try {
                    $bulanDepan = Carbon::parse($jatuhTempo)->addMonthNoOverflow();
                    $sudahAda = Invoice::where('customer_id', $payerCustomer->id)
                        ->whereMonth('jatuh_tempo', $bulanDepan->month)
                        ->whereYear('jatuh_tempo', $bulanDepan->year)
                        ->exists();

                    if (!$sudahAda) {
                        $merchantRefBaru = 'INV-' . $payerCustomer->id . '-' . time();
                        $nextInvoice = Invoice::create([
                            'customer_id' => $payerCustomer->id,
                            'paket_id' => $payerCustomer->paket_id,
                            'tagihan' => $payerCustomer->paket?->harga ?? 0,
                            'tambahan' => 0,
                            'saldo' => $saldoBaru > 0 ? $saldoBaru : 0,
                            'merchant_ref' => $merchantRefBaru,
                            'status_id' => 7, // Belum bayar
                            'jatuh_tempo' => $bulanDepan->copy()->endOfMonth()->setTime(23, 59, 59),
                            'tanggal_blokir' => $targetInvoice->tanggal_blokir,
                            'metode_bayar' => $metodeBayar,
                        ]);
                        $createdNextInvoiceId = $nextInvoice->id;
                        Log::info("Invoice bulan depan untuk Customer {$payerCustomer->id} berhasil dibuat (Invoice #{$createdNextInvoiceId})");
                    }
                } catch (Throwable $e) {
                    Log::warning("Gagal membuat invoice bulan depan untuk customer {$payerCustomer->id}: " . $e->getMessage());
                }
            }

            // --- G. Tangani Status MikroTik & Customer 12141 ---
            $payerUnblocked = false;
            if ($payerCustomer->status_id == 9) {
                try {
                    if ($payerCustomer->router) {
                        $mikrotik = new MikrotikServices();
                        $client = MikrotikServices::connect($payerCustomer->router);
                        $mikrotik->removeActiveConnections($client, $payerCustomer->usersecret);
                        $profileName = $payerCustomer->paket?->nama_paket ?? $payerCustomer->paket?->paket_name ?? 'default';
                        $mikrotik->unblokUser($client, $payerCustomer->usersecret, $profileName);
                    }
                    $payerCustomer->update(['status_id' => 3]); // Aktif
                    $payerUnblocked = true;
                    Log::info("Customer 12141 ({$payerCustomer->nama_customer}) berhasil di-unblock.");
                } catch (Throwable $e) {
                    Log::error("Gagal unblock Customer 12141 di MikroTik: " . $e->getMessage());
                }
            }

            // --- H. Tangani Status Customer 14552 (Jika Perlu Diisolir Kembali) ---
            $wrongCustomerReverted = false;
            if ($revertWrongCustomerStatus) {
                try {
                    $tanggalBlokir14552 = $wrongInvoice->tanggal_blokir
                        ? Carbon::parse($wrongInvoice->tanggal_blokir)
                        : ($wrongInvoice->jatuh_tempo ? Carbon::parse($wrongInvoice->jatuh_tempo) : null);

                    if ($tanggalBlokir14552 && now()->greaterThanOrEqualTo($tanggalBlokir14552) && $wrongCustomer->status_id == 3) {
                        if ($wrongCustomer->router) {
                            $errorInfo = [];
                            $client = MikrotikServices::connect($wrongCustomer->router);
                            MikrotikServices::changeUserProfileSingle($client, $wrongCustomer->usersecret, 'ISOLIREBILLING', $errorInfo);
                            $mikrotik = new MikrotikServices();
                            $mikrotik->removeActiveConnections($client, $wrongCustomer->usersecret);
                        }
                        $wrongCustomer->update(['status_id' => 9]); // Blokir
                        $wrongCustomerReverted = true;
                        Log::info("Customer 14552 ({$wrongCustomer->nama_customer}) dikembalikan ke status isolir karena tagihannya belum lunas.");
                    }
                } catch (Throwable $e) {
                    Log::error("Gagal isolir kembali Customer 14552 di MikroTik: " . $e->getMessage());
                }
            }

            // --- I. Audit Activity Log ---
            try {
                activity('koreksi-pembayaran')
                    ->performedOn($targetInvoice)
                    ->log("Koreksi salah bayar: Pemindahan pembayaran Rp " . number_format($paidAmount, 0, ',', '.') .
                        " dari Customer ID {$wrongCustomer->id} ({$wrongCustomer->nama_customer}) ke Customer ID {$payerCustomer->id} ({$payerCustomer->nama_customer})");
            } catch (Throwable $e) {
                Log::warning("Gagal mencatat activity log: " . $e->getMessage());
            }

            DB::commit();

            // --- J. Kirim WhatsApp Notifikasi ke Customer 12141 (Opsional, di luar transaksi DB) ---
            $waSent = false;
            if ($sendWa && !empty($payerCustomer->no_hp)) {
                try {
                    $pembayaran->load('invoice.customer');
                    $chat = new ChatServices();
                    $chat->pembayaranBerhasil($payerCustomer->no_hp, $pembayaran);
                    $waSent = true;
                    Log::info("WhatsApp konfirmasi bayar berhasil dikirim ke {$payerCustomer->nama_customer} ({$payerCustomer->no_hp})");
                } catch (Throwable $e) {
                    Log::error("Gagal mengirim WhatsApp ke {$payerCustomer->nama_customer}: " . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'mode' => 'EXECUTE',
                'message' => 'Koreksi pembayaran berhasil dilakukan secara atomik.',
                'result' => [
                    'payer_customer' => [
                        'id' => $payerCustomer->id,
                        'nama' => $payerCustomer->nama_customer,
                        'invoice_id' => $targetInvoice->id,
                        'status_invoice' => 'Sudah Bayar (8)',
                        'unblocked' => $payerUnblocked,
                        'next_invoice_created_id' => $createdNextInvoiceId,
                        'wa_sent' => $waSent
                    ],
                    'wrong_customer' => [
                        'id' => $wrongCustomer->id,
                        'nama' => $wrongCustomer->nama_customer,
                        'invoice_id' => $wrongInvoice->id,
                        'status_invoice' => 'Belum Bayar (7)',
                        'deleted_next_invoice_id' => $deletedNextInvoiceId,
                        'reverted_to_blocked' => $wrongCustomerReverted
                    ],
                    'payment' => [
                        'pembayaran_id' => $pembayaran->id,
                        'jumlah_bayar' => $paidAmount,
                        'metode_bayar' => $metodeBayar,
                        'saldo_ditambahkan' => max($diff, 0),
                    ]
                ]
            ];

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Gagal mengeksekusi koreksi pembayaran: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses koreksi pembayaran: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}
