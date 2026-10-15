<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index untuk halaman /rab (RabController@search — DataTables server-side).
 *
 * Temuan sebelum optimasi:
 * - pengeluaran.rab_id tidak punya index, padahal dipakai:
 *   (a) subquery withSum('pengeluaran') untuk setiap baris tabel, dan
 *   (b) agregat realisasi per RAB.
 * - pembayaran/pendapatan/perusahaan tidak punya index tanggal, sementara
 *   filter periode sebelumnya memakai whereYear()/whereMonth() (non-sargable).
 *   Setelah filter diubah menjadi rentang tanggal (whereBetween), index ini
 *   membuat agregat saldo memakai index, bukan full table scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // pengeluaran: agregat per RAB + subquery withSum
        if (! Schema::hasIndex('pengeluaran', 'pengeluaran_rab_id_status_id_index')) {
            Schema::table('pengeluaran', function (Blueprint $table) {
                $table->index(['rab_id', 'status_id'], 'pengeluaran_rab_id_status_id_index');
            });
        }

        // pengeluaran: total pengeluaran per periode (status selesai)
        if (! Schema::hasIndex('pengeluaran', 'pengeluaran_status_id_tanggal_index')) {
            Schema::table('pengeluaran', function (Blueprint $table) {
                $table->index(['status_id', 'tanggal_pengeluaran'], 'pengeluaran_status_id_tanggal_index');
            });
        }

        // pembayaran: saldo masuk per periode
        if (! Schema::hasIndex('pembayaran', 'pembayaran_status_id_tanggal_bayar_index')) {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->index(['status_id', 'tanggal_bayar'], 'pembayaran_status_id_tanggal_bayar_index');
            });
        }

        // pendapatan: total pendapatan per periode
        if (! Schema::hasIndex('pendapatan', 'pendapatan_tanggal_index')) {
            Schema::table('pendapatan', function (Blueprint $table) {
                $table->index('tanggal', 'pendapatan_tanggal_index');
            });
        }

        // perusahaan: pendapatan corporate per periode
        if (! Schema::hasIndex('perusahaan', 'perusahaan_status_id_tanggal_index')) {
            Schema::table('perusahaan', function (Blueprint $table) {
                $table->index(['status_id', 'tanggal'], 'perusahaan_status_id_tanggal_index');
            });
        }

        // rab: filter tahun/bulan pada DataTables
        if (! Schema::hasIndex('rab', 'rab_tahun_anggaran_bulan_index')) {
            Schema::table('rab', function (Blueprint $table) {
                $table->index(['tahun_anggaran', 'bulan'], 'rab_tahun_anggaran_bulan_index');
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'pengeluaran' => ['pengeluaran_rab_id_status_id_index', 'pengeluaran_status_id_tanggal_index'],
            'pembayaran' => ['pembayaran_status_id_tanggal_bayar_index'],
            'pendapatan' => ['pendapatan_tanggal_index'],
            'perusahaan' => ['perusahaan_status_id_tanggal_index'],
            'rab' => ['rab_tahun_anggaran_bulan_index'],
        ];

        foreach ($indexes as $tableName => $names) {
            foreach ($names as $name) {
                if (Schema::hasIndex($tableName, $name)) {
                    Schema::table($tableName, function (Blueprint $table) use ($name) {
                        $table->dropIndex($name);
                    });
                }
            }
        }
    }
};
