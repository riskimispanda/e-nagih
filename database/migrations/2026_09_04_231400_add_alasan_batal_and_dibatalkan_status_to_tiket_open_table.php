<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom alasan pembatalan tiket
        Schema::table('tiket_open', function (Blueprint $table) {
            $table->text('alasan_batal')->nullable()->after('status_id');
        });

        // Status baru "Dibatalkan" untuk membedakan tiket yang dibatalkan dari yang selesai (status 3)
        $exists = DB::table('status')->where('nama_status', 'Dibatalkan')->exists();
        if (!$exists) {
            DB::table('status')->insert([
                'nama_status' => 'Dibatalkan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Hapus status "Dibatalkan" bila tidak dipakai entity lain (aman karena hanya tiket_open yang menggunakannya)
        DB::table('status')->where('nama_status', 'Dibatalkan')->delete();

        Schema::table('tiket_open', function (Blueprint $table) {
            $table->dropColumn('alasan_batal');
        });
    }
};
