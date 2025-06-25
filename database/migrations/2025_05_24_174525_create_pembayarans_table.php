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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('jumlah_bayar');
            $table->date('tanggal_bayar');
            $table->string('metode_bayar');
            $table->string('keterangan')->nullable();
            $table->string('bukti_bayar')->nullable();
            // Foreign key constraints
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('invoice_id')->references('id')->on('invoice');
            $table->foreignId('status_id')->references('id')->on('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
        Schema::dropForeign(['user_id']);
        Schema::dropForeign(['invoice_id']);
        Schema::dropForeign(['status_id']);
    }
};
