<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ubah kolom whats_log.jenis_pesan dari ENUM menjadi VARCHAR(50)
     * agar nilai baru seperti 'reminder_pembayaran' tidak terkena SQL truncation.
     */
    public function up(): void
    {
        if (!Schema::hasTable('whats_log')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $column = DB::selectOne("
                SELECT DATA_TYPE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'whats_log'
                  AND COLUMN_NAME = 'jenis_pesan'
            ");

            if ($column && strtolower($column->DATA_TYPE) === 'enum') {
                DB::statement('ALTER TABLE `whats_log` MODIFY `jenis_pesan` VARCHAR(50) NOT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sengaja tidak mengembalikan ke ENUM agar data log 'reminder_pembayaran' tetap aman.
    }
};
