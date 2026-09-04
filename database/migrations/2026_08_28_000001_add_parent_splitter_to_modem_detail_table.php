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
            $table->unsignedBigInteger('parent_splitter_id')->nullable()->after('odp_id');
            $table->string('parent_port_out')->nullable()->after('parent_splitter_id');

            $table->index('parent_splitter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ModemDetail', function (Blueprint $table) {
            $table->dropIndex(['parent_splitter_id']);
            $table->dropColumn(['parent_splitter_id', 'parent_port_out']);
        });
    }
};
