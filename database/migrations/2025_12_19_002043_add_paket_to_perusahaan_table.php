<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            // Tambah kolom paket
            $table->string('paket', 255)->nullable()->after('nama_perusahaan');
            
            // Atau jika ingin dengan default value:
            // $table->string('paket', 100)->default('basic')->after('nama_perusahaan');
            
            // Untuk index (opsional)
            // $table->index('paket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn('paket');
        });
    }
};