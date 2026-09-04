<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_rusak')->default(0)->after('jumlah_stok');
        });
    }

    public function down(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            $table->dropColumn('jumlah_rusak');
        });
    }
};