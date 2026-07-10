<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('failed_block_logs')) {
            Schema::create('failed_block_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
                $table->foreignId('invoice_id')->nullable()->constrained('invoice')->onDelete('set null');
                $table->foreignId('router_id')->nullable()->constrained('router')->onDelete('set null');
                $table->enum('error_type', [
                    'connection_failed',
                    'secret_empty',
                    'user_not_found',
                    'multiple_users',
                    'name_mismatch',
                    'api_exception',
                    'unknown_error'
                ]);
                $table->text('error_message');
                $table->text('error_detail')->nullable();
                $table->enum('source', ['auto', 'manual'])->default('auto');
                $table->string('usersecret', 100)->nullable();
                $table->timestamps();

                $table->index('error_type');
                $table->index('source');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_block_logs');
    }
};
