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
        Schema::create('BeritaAcara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->references('id')->on('customer');
            $table->foreignId('invoice_id')->references('id')->on('invoice');
            $table->string('tanggal_ba')->nullable();
            $table->string('tanggal_selesai_ba')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('kategori_tiket')->references('id')->on('kategori_tiket');
            $table->foreignId('admin_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BeritaAcara');
    }
};
