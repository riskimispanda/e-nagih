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
        Schema::table('BeritaAcara', function (Blueprint $table) {
            $table->foreignId('noc_id')->nullable()->constrained('users')->nullOnDelete();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('BeritaAcara', function (Blueprint $table) {
            $table->dropForeign(['noc_id']); // hapus constraint FK
            $table->dropColumn('noc_id');    // hapus kolom
        });        
    }
};
