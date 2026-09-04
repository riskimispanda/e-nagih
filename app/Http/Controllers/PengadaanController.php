<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengadaan;
use App\Models\Rab;
use App\Models\Pengeluaran;
use App\Models\Kas;
use App\Models\Perangkat;
use App\Models\KategoriLogistik;
use App\Models\Status;
use App\Models\ModemDetail;
use App\Helpers\LogistikStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $query = Pengadaan::with(['user', 'approver', 'status', 'rab', 'perangkat', 'perangkat.kategori', 'kategori'])
            ->orderBy('created_at', 'desc');

        if ($statusFilter) {
            $query->where('status_id', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('jenis_pengadaan', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $pengadaans = $query->paginate(15)->withQueryString();

        // Hitung count per status
        $counts = [
            'all' => Pengadaan::count(),
            'menunggu' => Pengadaan::where('status_id', 1)->count(),
            'disetujui' => Pengadaan::where('status_id', 2)->count(),
            'selesai' => Pengadaan::where('status_id', 3)->count(),
            'ditolak' => Pengadaan::where('status_id', 18)->count(),
        ];

        $perangkatList = Perangkat::with('kategori')->orderBy('nama_perangkat')->get();
        $kategoriList = KategoriLogistik::orderBy('nama_logistik')->get();
        $rabList = Rab::all();

        return view('pengadaan.index', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pengadaans' => $pengadaans,
            'counts' => $counts,
            'currentStatus' => $statusFilter,
            'search' => $search,
            'perangkatList' => $perangkatList,
            'kategoriList' => $kategoriList,
            'rabList' => $rabList,
        ]);
    }

    public function create()
    {
        return view('pengadaan.create', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'perangkat' => Perangkat::all(),
            'kategori' => KategoriLogistik::all(),
            'rab' => Rab::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jenis_pengadaan' => 'required|in:perangkat_lama,perangkat_baru',
            'perangkat_id' => 'required_if:jenis_pengadaan,perangkat_lama|nullable|exists:perangkat,id',
            'kategori_id' => 'required|exists:KategoriLogistik,id',
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'rab_id' => 'nullable|exists:rab,id',
            'keterangan' => 'nullable|string',
            'bukti_pembelian' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_pembelian')) {
            $buktiPath = $request->file('bukti_pembelian')->store('pengadaan', 'public');
        }

        // Tentukan nama barang: jika perangkat lama, ambil nama dari master perangkat
        $namaBarang = $request->nama_barang;
        if ($request->jenis_pengadaan === 'perangkat_lama' && $request->perangkat_id) {
            $perangkat = Perangkat::find($request->perangkat_id);
            if ($perangkat) {
                $namaBarang = $perangkat->nama_perangkat;
            }
        }

        Pengadaan::create([
            'nama_barang' => $namaBarang,
            'perangkat_id' => $request->jenis_pengadaan === 'perangkat_lama' ? $request->perangkat_id : null,
            'kategori_id' => $request->kategori_id,
            'jenis_pengadaan' => $request->jenis_pengadaan,
            'jumlah' => $request->jumlah,
            'harga_satuan' => $request->harga_satuan,
            'total_harga' => $request->total_harga,
            'keterangan' => $request->keterangan,
            'user_id' => auth()->id(),
            'status_id' => 1, // Menunggu Persetujuan
            'rab_id' => $request->rab_id,
            'tanggal_permintaan' => now(),
            'bukti_pembelian' => $buktiPath,
        ]);

        return redirect('/pengadaan')->with('success', 'Permintaan pengadaan barang berhasil diajukan dan menunggu persetujuan.');
    }

    public function approval(Request $request)
    {
        $pengadaans = Pengadaan::with(['user', 'rab', 'perangkat', 'kategori'])
            ->where('status_id', 1) // Menunggu Persetujuan
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $kasList = Kas::where('status_id', 3)
            ->distinct('kas_id')
            ->pluck('kas_id')
            ->filter();

        return view('pengadaan.approval', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pengadaans' => $pengadaans,
            'pendingPengadaan' => $pengadaans,
            'kasList' => $kasList,
        ]);
    }

    public function approvalList(Request $request)
    {
        return $this->approval($request);
    }

    public function accPengadaan(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        if ($pengadaan->status_id != 1) {
            return back()->with('error', 'Pengadaan ini sudah diproses sebelumnya.');
        }

        $selectedKasId = $request->kas_id ?? 1;

        DB::transaction(function () use ($pengadaan, $selectedKasId) {
            // 1. Update status Pengadaan menjadi Disetujui (status_id = 2)
            $pengadaan->update([
                'status_id' => 2, // Disetujui
                'approved_by' => auth()->id(),
                'tanggal_disetujui' => now(),
            ]);

            // 2. Buat entri Pengeluaran otomatis
            $pengeluaran = new Pengeluaran();
            $pengeluaran->nama_pengeluaran = 'Pengadaan Barang: ' . $pengadaan->nama_barang;
            $pengeluaran->kategori_id = $pengadaan->kategori_id;
            $pengeluaran->jumlah_pengeluaran = $pengadaan->total_harga;
            $pengeluaran->tanggal_pengeluaran = now();
            $pengeluaran->keterangan = 'Persetujuan pengadaan barang ' . $pengadaan->nama_barang . ' (' . $pengadaan->jumlah . ' unit). Disetujui oleh: ' . auth()->user()->name;
            $pengeluaran->kas_id = $selectedKasId;
            $pengeluaran->rab_id = $pengadaan->rab_id;
            $pengeluaran->save();

            // Hubungkan ID pengeluaran ke pengadaan
            $pengadaan->update(['pengeluaran_id' => $pengeluaran->id]);

            // 3. Buat entri kas
            $kas = new Kas();
            $kas->kredit = $pengadaan->total_harga;
            $kas->keterangan = 'Pengadaan: ' . $pengadaan->nama_barang . ' (' . $pengadaan->jumlah . ' unit)';
            $kas->tanggal_kas = now();
            $kas->kas_id = $selectedKasId;
            $kas->user_id = auth()->id();
            $kas->pengeluaran_id = $pengeluaran->id;
            $kas->status_id = 3;
            $kas->save();

            // 4. Update status RAB jika anggaran habis
            if ($pengadaan->rab_id) {
                $rab = Rab::find($pengadaan->rab_id);
                if ($rab) {
                    $totalRealisasi = $rab->pengeluaran->sum('jumlah_pengeluaran') ?? 0;
                    $sisa = $rab->jumlah_anggaran - $totalRealisasi;
                    if ($sisa <= 0) {
                        $rab->update(['status_id' => 11]); // Terealisasi
                    }
                }
            }
        });

        return redirect('/pengadaan/approval')->with('success', 'Pengadaan disetujui! Pengeluaran kas telah dicatat.');
    }

    public function tolakPengadaan(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        if ($pengadaan->status_id != 1) {
            return back()->with('error', 'Pengadaan ini sudah diproses sebelumnya.');
        }

        $pengadaan->update([
            'status_id' => 18, // Ditolak
            'approved_by' => auth()->id(),
            'alasan_tolak' => $request->alasan_tolak ?? 'Permintaan pengadaan ditolak oleh pimpinan.',
        ]);

        return redirect('/pengadaan/approval')->with('success', 'Permintaan pengadaan berhasil ditolak.');
    }

    /**
     * Konfirmasi penerimaan fisik barang & update stok secara manual oleh staf logistik.
     * Mengadopsi mekanisme pencatatan detail unit (Serial Number, MAC, Rasio Splitter, Unit Rusak)
     * sebagaimana terdapat pada form Tambah Stok di /data/logistik.
     */
    public function terimaBarang(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        if ($pengadaan->status_id != 2) {
            return back()->with('error', 'Hanya pengadaan berstatus Disetujui yang dapat dikonfirmasi penerimaannya.');
        }

        $request->validate([
            'tipe_penerimaan' => 'required|in:update_existing,new_item,non_stok',
            'perangkat_id' => 'required_if:tipe_penerimaan,update_existing|nullable|exists:perangkat,id',
            'nama_perangkat' => 'required_if:tipe_penerimaan,new_item|nullable|string|max:255',
            'kategori_id' => 'required_if:tipe_penerimaan,new_item|nullable|exists:KategoriLogistik,id',
            'jumlah_diterima' => 'required|integer|min:1',
            'catatan_penerimaan' => 'nullable|string',
            'rasio' => 'nullable|string',
            'serial_number' => 'nullable|array',
            'mac_address' => 'nullable|array',
            'is_rusak' => 'nullable|array',
            'jumlah_rusak' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($pengadaan, $request) {
            $perangkatId = null;
            $jumlah = (int) $request->jumlah_diterima;
            $jumlahRusak = (int) $request->input('jumlah_rusak', 0);
            if ($jumlahRusak < 0) {
                $jumlahRusak = 0;
            }

            $perangkat = null;
            $kategoriNama = '';

            if ($request->tipe_penerimaan === 'update_existing') {
                $perangkat = Perangkat::with('kategori')->findOrFail($request->perangkat_id);
                $perangkat->increment('jumlah_stok', $jumlah);
                $perangkatId = $perangkat->id;
                $kategoriNama = strtolower($perangkat->kategori->nama_logistik ?? '');
            } elseif ($request->tipe_penerimaan === 'new_item') {
                $perangkat = Perangkat::create([
                    'nama_perangkat' => $request->nama_perangkat,
                    'jumlah_stok' => $jumlah,
                    'jumlah_rusak' => 0,
                    'harga' => $pengadaan->harga_satuan,
                    'kategori_id' => $request->kategori_id,
                ]);
                $perangkatId = $perangkat->id;
                $kategori = KategoriLogistik::find($request->kategori_id);
                $kategoriNama = strtolower($kategori->nama_logistik ?? '');
            }

            if ($perangkat) {
                // Splitter: input rasio + SN per unit, buat ModemDetail per unit
                if ($kategoriNama === 'splitter') {
                    $rasio = $request->input('rasio');
                    $serials = $request->input('serial_number', []);
                    $isRusakArr = $request->input('is_rusak', []);
                    $hasPerUnitRusak = is_array($isRusakArr) && count($isRusakArr) > 0;
                    $terpakai = max(0, $jumlah - $jumlahRusak);

                    for ($i = 0; $i < $jumlah; $i++) {
                        $sn = trim($serials[$i] ?? '');
                        $rusakPerUnit = $hasPerUnitRusak ? !empty($isRusakArr[$i] ?? null) : ($i >= $terpakai);
                        ModemDetail::create([
                            'logistik_id'   => $perangkat->id,
                            'serial_number' => $sn ?: null,
                            'mac_address'   => null,
                            'rasio'         => $rasio,
                            'status_id'     => $rusakPerUnit ? LogistikStatus::RUSAK : LogistikStatus::TERSEIDA,
                        ]);
                    }
                }
                // Jika serialized lainnya (modem, tenda, sfp, olt, odp, odc, htb) atau jika input SN dikirim
                elseif (LogistikStatus::isSerialized($kategoriNama) || ($request->has('serial_number') && is_array($request->serial_number))) {
                    $serials = $request->serial_number ?? [];
                    $macs = $request->mac_address ?? [];
                    $isRusakArr = $request->input('is_rusak', []);

                    $units = [];
                    foreach ($serials as $i => $sn) {
                        $sn = trim($sn);
                        $mac = trim($macs[$i] ?? '');
                        if (empty($sn)) continue;
                        $units[] = [
                            'sn'    => $sn,
                            'mac'   => $mac ?: null,
                            'rusak' => !empty($isRusakArr[$i] ?? null),
                        ];
                    }

                    foreach ($units as $unit) {
                        ModemDetail::create([
                            'logistik_id'   => $perangkat->id,
                            'serial_number' => $unit['sn'],
                            'mac_address'   => $unit['mac'],
                            'status_id'     => $unit['rusak'] ? LogistikStatus::RUSAK : LogistikStatus::TERSEIDA,
                        ]);
                    }
                } else {
                    // Non-serialized (kabel dropcore, dsb): update jumlah rusak di tabel perangkat jika ada
                    if ($jumlahRusak > 0) {
                        $perangkat->increment('jumlah_rusak', min($jumlahRusak, $jumlah));
                    }
                }
            }

            $pengadaan->update([
                'status_id' => 3, // Selesai
                'perangkat_id' => $perangkatId ?? $pengadaan->perangkat_id,
                'tanggal_diterima' => now(),
                'keterangan' => $pengadaan->keterangan . ($request->catatan_penerimaan ? ' [Penerimaan: ' . $request->catatan_penerimaan . ']' : ''),
            ]);
        });

        return back()->with('success', 'Barang berhasil diterima dan stok inventaris gudang telah diperbarui!');
    }
}
