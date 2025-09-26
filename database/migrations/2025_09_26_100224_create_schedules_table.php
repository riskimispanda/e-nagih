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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul jadwal
            $table->enum('category', ['meeting', 'task', 'event', 'reminder', 'personal']); // Kategori
            $table->text('description')->nullable(); // Deskripsi (opsional)
            $table->date('date'); // Tanggal jadwal
            $table->enum('time_type', ['allday', 'specific'])->default('specific'); // Jenis waktu
            $table->time('start_time')->nullable(); // Waktu mulai (null jika seharian)
            $table->time('end_time')->nullable(); // Waktu selesai (null jika seharian)
            $table->string('color', 7)->default('#696cff'); // Warna (hex code)
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium'); // Prioritas
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Pemilik jadwal
            $table->boolean('is_active')->default(true); // Status aktif
            $table->timestamps();
            
            // Indexes untuk performa
            $table->index(['user_id', 'date']);
            $table->index(['category', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};