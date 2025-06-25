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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('nama_customer');
            $table->string('email')->unique();
            $table->string('no_hp');
            $table->string('alamat');
            $table->string('identitas');
            $table->string('no_identitas');
            $table->string('gps');
            $table->foreignId('paket_id')->referances('id')->on('paket');
            $table->foreignId('lokasi_id')->referances('id')->on('lokasi');
            $table->foreignId('teknisi_id')->referances('id')->on('users');
            $table->foreignId('logistik_id')->referances('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
