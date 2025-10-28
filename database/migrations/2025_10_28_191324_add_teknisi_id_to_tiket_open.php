<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('tiket_open', function (Blueprint $table) {
            // Tambahkan kolom teknisi_id (nullable, foreign key opsional)
            $table->unsignedBigInteger('teknisi_id')->nullable()->after('status_id');

            // Jika ada relasi ke tabel 'users' atau 'teknisis', aktifkan baris berikut:
            $table->foreign('teknisi_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::table('tiket_open', function (Blueprint $table) {
            // Hapus foreign key dulu (jika ada)
            $table->dropForeign(['teknisi_id']);

            // Hapus kolom teknisi_id
            $table->dropColumn('teknisi_id');
        });
    }
};
