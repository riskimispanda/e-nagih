<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('odc') && !Schema::hasColumn('odc', 'panjang_kabel')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->decimal('panjang_kabel', 10, 2)->nullable()->after('gps');
            });
        }

        if (Schema::hasTable('odp') && !Schema::hasColumn('odp', 'panjang_kabel')) {
            Schema::table('odp', function (Blueprint $table) {
                $table->decimal('panjang_kabel', 10, 2)->nullable()->after('gps');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('odc') && Schema::hasColumn('odc', 'panjang_kabel')) {
            Schema::table('odc', function (Blueprint $table) {
                $table->dropColumn('panjang_kabel');
            });
        }

        if (Schema::hasTable('odp') && Schema::hasColumn('odp', 'panjang_kabel')) {
            Schema::table('odp', function (Blueprint $table) {
                $table->dropColumn('panjang_kabel');
            });
        }
    }
};
