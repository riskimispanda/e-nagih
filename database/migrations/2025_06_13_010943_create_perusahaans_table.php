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
        Schema::create('perusahaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->string('nama_pic');
            $table->string('no_hp');
            $table->string('foto');
            $table->string('alamat');
            $table->string('gps');
            $table->string('speed')->nullable();
            $table->string('limit')->nullable();
            $table->string('harga')->nullable();
            $table->string('tanggal');
            $table->foreignId('status_id')->references('id')->on('status');
            $table->foreignId('paket_id')->references('id')->on('paket');
            $table->foreignId('admin_id')->references('id')->on('users');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perusahaan');
    }
};
