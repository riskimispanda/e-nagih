<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            // Hapus foreign lama
            $table->dropForeign(['perangkat_id']);

            // Ubah jadi nullable
            $table->unsignedBigInteger('perangkat_id')->nullable()->change();

            // Tambahkan constraint baru dengan nullOnDelete
            $table->foreign('perangkat_id')->references('id')->on('perangkat')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->dropForeign(['perangkat_id']);

            // Balik ke NOT NULL kalau mau revert
            $table->unsignedBigInteger('perangkat_id')->nullable(false)->change();

            $table->foreign('perangkat_id')->references('id')->on('perangkat');
        });
    }
};