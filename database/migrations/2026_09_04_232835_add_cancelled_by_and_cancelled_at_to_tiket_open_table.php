<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiket_open', function (Blueprint $table) {
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('alasan_batal');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');

            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tiket_open', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_by', 'cancelled_at']);
        });
    }
};
