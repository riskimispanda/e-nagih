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
    Schema::create('pembayaran_corporate', function (Blueprint $table) {
      $table->id();

      // Relasi ke invoice corporate
      $table->unsignedBigInteger('invoice_corporate_id');
      $table->foreign('invoice_corporate_id')
        ->references('id')
        ->on('invoice_corporate')
        ->onDelete('cascade');

      // Relasi ke perusahaan (untuk kemudahan query)
      $table->unsignedBigInteger('perusahaan_id');
      $table->foreign('perusahaan_id')
        ->references('id')
        ->on('perusahaan')
        ->onDelete('cascade');

      // Informasi pembayaran
      $table->decimal('jumlah_bayar', 15, 2)->comment('Jumlah yang dibayarkan');
      $table->date('tanggal_bayar')->comment('Tanggal pembayaran dilakukan');

      // Metode pembayaran
      $table->string('metode_bayar');

      // Bukti pembayaran
      $table->string('bukti_bayar')->nullable()->comment('Path file bukti pembayaran');

      // Keterangan
      $table->text('keterangan')->nullable()->comment('Catatan tambahan pembayaran');

      // Status konfirmasi
      $table->unsignedBigInteger('status_id')->default(8)->comment('7=pending, 8=confirmed, 9=rejected');
      $table->foreign('status_id')
        ->references('id')
        ->on('status')
        ->onDelete('restrict');

      $table->unsignedBigInteger('user_id')->comment('User yang mengkonfirmasi pembayaran');
      $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');

      $table->timestamps();

      // Indexes untuk performa query
      $table->index('invoice_corporate_id');
      $table->index('perusahaan_id');
      $table->index('tanggal_bayar');
      $table->index('status_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pembayaran_corporate');
  }
};
