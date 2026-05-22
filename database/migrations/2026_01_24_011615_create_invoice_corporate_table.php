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
    Schema::create('invoice_corporate', function (Blueprint $table) {
      $table->id();

      // Relasi ke Perusahaan (paket diambil dari relasi perusahaan)
      $table->foreignId('perusahaan_id')->constrained('perusahaan')->onDelete('cascade');
      $table->foreignId('status_id')->constrained('status')->onDelete('cascade');

      // Nomor Invoice (auto-generated, unique)
      $table->string('invoice_number')->unique();

      // Tagihan & Pembayaran
      $table->decimal('tagihan', 15, 2); // Tagihan total
      $table->decimal('tambahan', 15, 2)->default(0)->nullable(); // Biaya tambahan
      $table->text('keterangan_tambahan')->nullable(); // Keterangan biaya tambahan

      // Tanggal
      $table->date('tanggal_invoice'); // Tanggal invoice dibuat
      $table->date('jatuh_tempo'); // Tanggal jatuh tempo

      // Payment Gateway Reference
      $table->string('reference')->nullable();
      $table->string('merchant_ref')->nullable();
      $table->string('metode_bayar')->nullable();

      // Saldo & Tunggakan
      $table->decimal('saldo', 15, 2)->default(0);
      $table->decimal('tunggakan', 15, 2)->default(0);

      $table->boolean('cek')->default(0); // Flag untuk pengecekan

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('invoice_corporate');
  }
};
