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
        Schema::table('odp', function (Blueprint $table) {
            $table->string('port_out')->nullable()->after('rasio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('odp', function (Blueprint $table) {
            $table->dropColumn('port_out');
        });
    }
};
