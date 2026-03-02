<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Jika tabel BELUM ADA sama sekali (Misal Production Server Pertama Kali / Fresh Install)
        if (!Schema::hasTable('whats_log')) {
            Schema::create('whats_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
                $table->text('pesan');
                $table->enum('jenis_pesan', [
                    'customer_baru',
                    'pembayaran_berhasil',
                    'kirim_invoice',
                    'invoice_prorate',
                    'notifikasi_blokir',
                    'notifikasi_teknisi',
                    'notifikasi_noc',
                    'notifikasi_tiket_open',
                    'warning_bayar'
                ]);
                $table->string('qontak_broadcast_id')->nullable()->comment('ID Balikan dari API Qontak');
                $table->string('status_pengiriman')->default('pending')->comment('pending, sent, delivered, read, failed');
                $table->string('no_tujuan', 20)->nullable()->comment('Nomor WA saat pesan dikirim');
                $table->text('error_message')->nullable()->comment('Alasan gagal jika status failed');
                $table->timestamps();
            });
        }
        // 2. Jika tabel SUDAH ADA (Production Server yang update fitur)
        else {
            Schema::table('whats_log', function (Blueprint $table) {
                // Tambahkan field pelacak Qontak hanya jika belum tersedia
                if (!Schema::hasColumn('whats_log', 'qontak_broadcast_id')) {
                    $table->string('qontak_broadcast_id')->nullable()->after('jenis_pesan')->comment('ID Balikan dari API Qontak');
                }
                if (!Schema::hasColumn('whats_log', 'status_pengiriman')) {
                    $table->string('status_pengiriman')->default('pending')->after('qontak_broadcast_id')->comment('pending, sent, delivered, read, failed');
                }
                if (!Schema::hasColumn('whats_log', 'no_tujuan')) {
                    $table->string('no_tujuan', 20)->nullable()->after('status_pengiriman')->comment('Nomor WA saat pesan dikirim');
                }
                if (!Schema::hasColumn('whats_log', 'error_message')) {
                    $table->text('error_message')->nullable()->after('no_tujuan')->comment('Alasan gagal jika status failed');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika Rollback, kita hanya drop kolom tambahannya saja agar data log yg lama tidak hilang total.
        Schema::table('whats_log', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('whats_log', 'qontak_broadcast_id'))
                $columnsToDrop[] = 'qontak_broadcast_id';
            if (Schema::hasColumn('whats_log', 'status_pengiriman'))
                $columnsToDrop[] = 'status_pengiriman';
            if (Schema::hasColumn('whats_log', 'no_tujuan'))
                $columnsToDrop[] = 'no_tujuan';
            if (Schema::hasColumn('whats_log', 'error_message'))
                $columnsToDrop[] = 'error_message';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
