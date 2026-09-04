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
        Schema::create('dismantles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modem_detail_id')->nullable();
            $table->unsignedBigInteger('perangkat_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac_address')->nullable();
            $table->unsignedBigInteger('customer_id_lama')->nullable();
            $table->string('customer_nama');
            $table->string('perangkat_nama');
            $table->unsignedBigInteger('teknisi_id')->nullable();
            $table->unsignedInteger('status_barang');
            $table->text('keterangan_dismantle')->nullable();
            $table->dateTime('tanggal_dismantle');
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dismantles');
    }
};
