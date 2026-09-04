<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ModemDetail', function (Blueprint $table) {
            $table->unsignedBigInteger('lokasi_id')->nullable()->after('pon_port');
            $table->index('lokasi_id');
        });
    }

    public function down(): void
    {
        Schema::table('ModemDetail', function (Blueprint $table) {
            $table->dropIndex(['lokasi_id']);
            $table->dropColumn('lokasi_id');
        });
    }
};
