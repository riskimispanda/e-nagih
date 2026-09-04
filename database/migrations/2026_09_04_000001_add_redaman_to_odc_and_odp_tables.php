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
        if (Schema::hasTable('odc') && !Schema::hasColumn('odc', 'redaman')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->string('redaman')->nullable()->after('panjang_kabel');
            });
        }

        if (Schema::hasTable('odp') && !Schema::hasColumn('odp', 'redaman')) {
            Schema::table('odp', function (Blueprint $table) {
                $table->string('redaman')->nullable()->after('panjang_kabel');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('odc') && Schema::hasColumn('odc', 'redaman')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->dropColumn('redaman');
            });
        }

        if (Schema::hasTable('odp') && Schema::hasColumn('odp', 'redaman')) {
            Schema::table('odp', function (Blueprint $table) {
                $table->dropColumn('redaman');
            });
        }
    }
};
