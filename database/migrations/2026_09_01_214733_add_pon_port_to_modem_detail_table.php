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
        if (Schema::hasTable('ModemDetail') && !Schema::hasColumn('ModemDetail', 'pon_port')) {
            Schema::table('ModemDetail', function (Blueprint $table) {
                $table->string('pon_port', 20)->nullable()->after('rasio');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ModemDetail') && Schema::hasColumn('ModemDetail', 'pon_port')) {
            Schema::table('ModemDetail', function (Blueprint $table) {
                $table->dropColumn('pon_port');
            });
        }
    }
};
