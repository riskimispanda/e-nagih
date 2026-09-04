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
        if (Schema::hasTable('lokasi') && !Schema::hasColumn('lokasi', 'jumlah_pon')) {
            Schema::table('lokasi', function (Blueprint $table) {
                $table->unsignedInteger('jumlah_pon')->default(8)->after('nama_lokasi');
            });
        }

        if (Schema::hasTable('odc') && !Schema::hasColumn('odc', 'pon_port')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->string('pon_port', 20)->nullable()->after('lokasi_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lokasi') && Schema::hasColumn('lokasi', 'jumlah_pon')) {
            Schema::table('lokasi', function (Blueprint $table) {
                $table->dropColumn('jumlah_pon');
            });
        }

        if (Schema::hasTable('odc') && Schema::hasColumn('odc', 'pon_port')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->dropColumn('pon_port');
            });
        }
    }
};
