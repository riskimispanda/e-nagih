<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odc', function (Blueprint $table) {
            $table->unsignedBigInteger('splitter_id')->nullable()->after('pon_port');
            $table->string('port_out')->nullable()->after('splitter_id');
            $table->index('splitter_id');
        });
    }

    public function down(): void
    {
        Schema::table('odc', function (Blueprint $table) {
            $table->dropIndex(['splitter_id']);
            $table->dropColumn('port_out');
            $table->dropColumn('splitter_id');
        });
    }
};
