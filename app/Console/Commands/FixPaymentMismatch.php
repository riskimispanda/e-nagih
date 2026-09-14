<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentCorrectionService;

class FixPaymentMismatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:fix-mismatch
                            {payer_id : ID Customer yang seharusnya bayar (contoh: 12141)}
                            {wrong_id : ID Customer yang salah dibayar (contoh: 14552)}
                            {--wrong-invoice= : ID invoice spesifik milik customer yang salah}
                            {--target-invoice= : ID invoice spesifik milik customer pembayar}
                            {--dry-run : Jalankan simulasi tanpa mengubah database}
                            {--send-wa : Kirim notifikasi WhatsApp kwitansi ke pelanggan pembayar}
                            {--no-revert-wrong : Jangan kembalikan status isolir customer yang salah dibayar}
                            {--keep-wrong-next-invoice : Jangan hapus invoice bulan depan customer salah bayar}
                            {--force : Lewati prompt konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbaiki kasus kesalahan pembayaran karena admin salah memberikan link pembayaran';

    protected PaymentCorrectionService $service;

    public function __construct(PaymentCorrectionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payerId = (int) $this->argument('payer_id');
        $wrongId = (int) $this->argument('wrong_id');
        $wrongInvoiceId = $this->option('wrong-invoice') ? (int) $this->option('wrong-invoice') : null;
        $targetInvoiceId = $this->option('target-invoice') ? (int) $this->option('target-invoice') : null;
        $isDryRun = (bool) $this->option('dry-run');
        $sendWa = (bool) $this->option('send-wa');
        $revertWrong = !$this->option('no-revert-wrong');
        $deleteNext = !$this->option('keep-wrong-next-invoice');
        $force = (bool) $this->option('force');

        $this->info("================================================================================");
        $this->info("🔍 MENGANALISA KASUS SALAH BAYAR (Customer {$payerId} vs Customer {$wrongId})");
        $this->info("================================================================================");

        $analysis = $this->service->analyze($payerId, $wrongId, $wrongInvoiceId, $targetInvoiceId);

        if (!$analysis['can_proceed']) {
            $this->error("❌ Gagal: " . $analysis['message']);
            return 1;
        }

        // Tampilkan Tabel Rincian Pelanggan
        $this->line("");
        $this->info("👤 PELANGGAN PEMBAYAR (YANG SEHARUSNYA BAYAR):");
        $this->table(
            ['ID', 'Nama', 'No HP', 'Status Saat Ini', 'Paket', 'Harga'],
            [[
                $analysis['payer_customer']['id'],
                $analysis['payer_customer']['nama'],
                $analysis['payer_customer']['no_hp'],
                $analysis['payer_customer']['status_nama'] . ($analysis['payer_customer']['is_blocked'] ? ' (TERISOLIR)' : ''),
                $analysis['payer_customer']['paket'],
                'Rp ' . number_format($analysis['payer_customer']['harga_paket'], 0, ',', '.')
            ]]
        );

        $this->line("");
        $this->info("👤 PELANGGAN SALAH BAYAR (YANG LINKNYA TERBAYAR):");
        $this->table(
            ['ID', 'Nama', 'No HP', 'Status Saat Ini', 'Paket', 'Harga'],
            [[
                $analysis['wrong_customer']['id'],
                $analysis['wrong_customer']['nama'],
                $analysis['wrong_customer']['no_hp'],
                $analysis['wrong_customer']['status_nama'],
                $analysis['wrong_customer']['paket'],
                'Rp ' . number_format($analysis['wrong_customer']['harga_paket'], 0, ',', '.')
            ]]
        );

        // Tampilkan Tabel Invoice & Pembayaran
        $this->line("");
        $this->info("📄 DATA INVOICE & PEMBAYARAN:");
        $this->table(
            ['Kategori', 'Invoice ID', 'Customer', 'Status', 'Tagihan', 'Reference Tripay'],
            [
                [
                    'Invoice Salah Dibayar',
                    $analysis['wrong_invoice']['id'],
                    $analysis['wrong_customer']['nama'],
                    $analysis['wrong_invoice']['status_nama'],
                    'Rp ' . number_format($analysis['wrong_invoice']['tagihan'], 0, ',', '.'),
                    $analysis['wrong_invoice']['reference'] ?? '-'
                ],
                [
                    'Invoice Target (Akan Dilunasi)',
                    $analysis['target_invoice']['id'],
                    $analysis['payer_customer']['nama'],
                    $analysis['target_invoice']['status_nama'],
                    'Rp ' . number_format($analysis['target_invoice']['tagihan'], 0, ',', '.'),
                    $analysis['target_invoice']['reference'] ?? '-'
                ],
            ]
        );

        // Tampilkan Analisa Nominal
        $this->line("");
        $pricing = $analysis['pricing_analysis'];
        $this->info("💰 ANALISA NOMINAL:");
        $this->line("   - Nominal yang Dibayar: Rp " . number_format($pricing['paid_amount'], 0, ',', '.'));
        $this->line("   - Tagihan Target:       Rp " . number_format($pricing['target_bill'], 0, ',', '.'));
        $this->line("   - Selisih:              Rp " . number_format($pricing['difference'], 0, ',', '.'));
        $this->line("   - Catatan:              " . $pricing['notes']);

        // Tampilkan Invoice Bulan Depan jika ada
        if (!empty($analysis['auto_generated_next_invoice_wrong_customer'])) {
            $nextInv = $analysis['auto_generated_next_invoice_wrong_customer'];
            $this->warn("⚠️  Ditemukan invoice bulan depan otomatis pada Customer {$wrongId} (Invoice #{$nextInv['id']}) yang akan dihapus.");
        }

        // Tampilkan Aksi yang akan Dijalankan
        $this->line("");
        $this->info("📋 TINDAKAN YANG AKAN DILAKUKAN:");
        foreach ($analysis['actions_preview'] as $idx => $action) {
            $this->line("   " . ($idx + 1) . ". " . $action);
        }

        if ($isDryRun) {
            $this->line("");
            $this->warn("🧪 DRY RUN SELESAI - Tidak ada perubahan yang disimpan ke database.");
            return 0;
        }

        if (!$force) {
            $this->line("");
            if (!$this->confirm('Apakah Anda yakin ingin mengeksekusi koreksi pembayaran ini di database?', false)) {
                $this->warn("Operasi dibatalkan oleh pengguna.");
                return 0;
            }
        }

        $this->info("⏳ Mengeksekusi koreksi data di database...");

        $result = $this->service->execute([
            'payer_customer_id' => $payerId,
            'wrong_customer_id' => $wrongId,
            'wrong_invoice_id' => $wrongInvoiceId,
            'target_invoice_id' => $targetInvoiceId,
            'dry_run' => false,
            'send_wa' => $sendWa,
            'revert_wrong_customer_status' => $revertWrong,
            'delete_wrong_next_invoice' => $deleteNext
        ]);

        if (!$result['success']) {
            $this->error("❌ Gagal mengeksekusi koreksi: " . $result['message']);
            return 1;
        }

        $this->line("");
        $this->info("✅ KOREKSI PEMBAYARAN BERHASIL DIEKSEKUSI!");
        $this->line("--------------------------------------------------------------------------------");
        $res = $result['result'];
        $this->line("• Invoice Customer {$payerId} (#{$res['payer_customer']['invoice_id']}): Diubah menjadi LUNAS");
        if ($res['payer_customer']['unblocked']) {
            $this->line("• Customer {$payerId} MikroTik: Berhasil di-unblock dan status diubah jadi Aktif (3)");
        }
        if ($res['payer_customer']['next_invoice_created_id']) {
            $this->line("• Invoice Bulan Depan Customer {$payerId}: Berhasil dibuat (#{$res['payer_customer']['next_invoice_created_id']})");
        }
        $this->line("• Invoice Customer {$wrongId} (#{$res['wrong_customer']['invoice_id']}): Dikembalikan ke BELUM BAYAR");
        if ($res['wrong_customer']['deleted_next_invoice_id']) {
            $this->line("• Invoice Bulan Depan Keliru Customer {$wrongId}: Berhasil dihapus (#{$res['wrong_customer']['deleted_next_invoice_id']})");
        }
        if ($res['wrong_customer']['reverted_to_blocked']) {
            $this->line("• Customer {$wrongId} MikroTik: Dikembalikan ke status isolir (9)");
        }
        if ($res['payer_customer']['wa_sent']) {
            $this->line("• Notifikasi WhatsApp: Berhasil dikirim ke {$res['payer_customer']['nama']}");
        }
        $this->info("--------------------------------------------------------------------------------");

        return 0;
    }
}
