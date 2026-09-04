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
        Schema::table('odc', function (Blueprint $table) {
            $table->string('rasio')->nullable()->after('nama_odc');
        });

        Schema::table('odp', function (Blueprint $table) {
            $table->string('rasio')->nullable()->after('nama_odp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('odc', function (Blueprint $table) {
            $table->dropColumn('rasio');
        });

        Schema::table('odp', function (Blueprint $table) {
            $table->dropColumn('rasio');
        });
    }
};
