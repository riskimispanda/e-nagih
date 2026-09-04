<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Status;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan status "Ditolak" tersedia di tabel status
        Status::firstOrCreate(['nama_status' => 'Ditolak']);

        if (!Schema::hasTable('pengadaan')) {
            Schema::create('pengadaan', function (Blueprint $table) {
                $table->id();
                $table->string('nama_barang');
                $table->foreignId('perangkat_id')->nullable()->constrained('perangkat')->nullOnDelete();
                $table->foreignId('kategori_id')->nullable()->constrained('KategoriLogistik')->nullOnDelete();
                $table->string('jenis_pengadaan')->nullable();
                $table->integer('jumlah')->default(1);
                $table->bigInteger('harga_satuan')->default(0);
                $table->bigInteger('total_harga')->default(0);
                $table->text('keterangan')->nullable();
                $table->text('alasan_tolak')->nullable();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('status_id')->default(1)->constrained('status');
                $table->foreignId('rab_id')->nullable()->constrained('rab')->nullOnDelete();
                $table->foreignId('pengeluaran_id')->nullable()->constrained('pengeluaran')->nullOnDelete();
                $table->timestamp('tanggal_permintaan')->useCurrent();
                $table->timestamp('tanggal_disetujui')->nullable();
                $table->timestamp('tanggal_diterima')->nullable();
                $table->string('bukti_pembelian')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengadaan');
    }
};
