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
            $table->timestamp('tanggal_terpakai')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ModemDetail', function (Blueprint $table) {
            $table->dropColumn('tanggal_terpakai');
        });
    }
};
