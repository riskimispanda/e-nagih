<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index untuk halaman /user/management (UserController@index).
 *
 * Sebelumnya ketiga query halaman ini (listing, COUNT pagination, dan rekap
 * per role) melakukan full table scan + filesort/temporary table karena:
 * - tidak ada index pada users.name (dipakai ORDER BY name + pagination), dan
 * - tidak ada index pada users.roles_id (dipakai WHERE roles_id != '8' dan GROUP BY).
 *
 * Catatan: roles_id bertipe varchar, jadi query HARUS membandingkan sebagai
 * string ('8'), bukan integer (8) — perbandingan integer memicu implicit CAST
 * yang membuat index roles_id tidak terpakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('users', 'users_name_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('name', 'users_name_index');
            });
        }

        if (! Schema::hasIndex('users', 'users_roles_id_name_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['roles_id', 'name'], 'users_roles_id_name_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_name_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_name_index');
            });
        }

        if (Schema::hasIndex('users', 'users_roles_id_name_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_roles_id_name_index');
            });
        }
    }
};
