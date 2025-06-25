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
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->references('id')->on('customer');
            $table->foreignId('status_id')->references('id')->on('status');
            $table->foreignId('paket_id')->references('id')->on('paket');
            $table->string('tagihan');
            $table->string('jatuh_tempo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
            $table->dropForeign(['paket_id']);
            $table->dropColumn('paket_id');
        });
    }
};
