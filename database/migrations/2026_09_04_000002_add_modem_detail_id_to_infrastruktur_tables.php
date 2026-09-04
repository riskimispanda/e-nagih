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
        if (Schema::hasTable('lokasi') && !Schema::hasColumn('lokasi', 'modem_detail_id')) {
            Schema::table('lokasi', function (Blueprint $table) {
                $table->foreignId('modem_detail_id')->nullable()->after('id_server');
            });
        }

        if (Schema::hasTable('odc') && !Schema::hasColumn('odc', 'modem_detail_id')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->foreignId('modem_detail_id')->nullable()->after('port_out');
            });
        }

        if (Schema::hasTable('odp') && !Schema::hasColumn('odp', 'modem_detail_id')) {
            Schema::table('odp', function (Blueprint $table) {
                $table->foreignId('modem_detail_id')->nullable()->after('port_out');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lokasi') && Schema::hasColumn('lokasi', 'modem_detail_id')) {
            Schema::table('lokasi', function (Blueprint $table) {
                $table->dropColumn('modem_detail_id');
            });
        }

        if (Schema::hasTable('odc') && Schema::hasColumn('odc', 'modem_detail_id')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->dropColumn('modem_detail_id');
            });
        }

        if (Schema::hasTable('odp') && Schema::hasColumn('odp', 'modem_detail_id')) {
            Schema::table('odp', function (Blueprint $table) {
                $table->dropColumn('modem_detail_id');
            });
        }
    }
};
