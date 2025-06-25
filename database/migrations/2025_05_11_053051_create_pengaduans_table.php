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
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->referances('id')->on('customer');
            $table->foreignId('pengaduan_id')->referances('id')->on('j_pengaduan');
            $table->foreignId('status_id')->referances('id')->on('status');
            $table->foreignId('teknisi_id')->referances('id')->on('users')->nullable();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('lampiran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};
