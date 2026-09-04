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
        Schema::table('ModemDetail', function (Blueprint $table) {
            $table->string('rasio')->nullable()->after('mac_address');
            $table->unsignedBigInteger('odc_id')->nullable()->after('rasio');
            $table->unsignedBigInteger('odp_id')->nullable()->after('odc_id');

            $table->index('odc_id');
            $table->index('odp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ModemDetail', function (Blueprint $table) {
            $table->dropIndex(['odc_id']);
            $table->dropIndex(['odp_id']);
            $table->dropColumn(['rasio', 'odc_id', 'odp_id']);
        });
    }
};