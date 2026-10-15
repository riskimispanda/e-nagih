<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rab;
use App\Models\Pengeluaran;
use Spatie\Activitylog\Models\Activity;
use App\Models\Pembayaran;
use App\Models\Pendapatan;
use App\Models\Perusahaan;
use Carbon\Carbon;

class RabController extends Controller
{
    public function index()
    {
        return view('/rab/rab',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rab = new Rab();
        $rab->jumlah_anggaran = $request->pagu;
        $rab->tahun_anggaran = $request->tahun;
        $rab->keterangan = $request->keterangan;
        $rab->kegiatan = $request->kegiatan;
        $rab->bulan = $request->bulan;
        $rab->item = $request->item;
        $rab->user_id = auth()->id();
        $rab->status_id = 12;
        $rab->save();

        // Log Activity
        activity('rab')
            ->performedOn($rab)
            ->causedBy(auth()->user())
            ->log('Menambah Pagu Anggaran Tahun ' . $request->tahun);

        return redirect('/rab')->with('toast_success','Pagu Berhasil Di tambah');
    }

    public function search(Request $request)
    {
        $query = Rab::with(['usr', 'status'])->withSum('pengeluaran', 'jumlah_pengeluaran');
 
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
 
        if ($request->filled('tahun')) {
            $query->where('tahun_anggaran', $request->tahun);
        }
 
        if ($request->filled('kegiatan')) {
            $query->where('kegiatan', 'like', '%' . $request->kegiatan . '%');
        }

        // Global search from DataTable
        if ($request->has('search') && $request->search['value'] != '') {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('kegiatan', 'like', "%{$searchValue}%")
                  ->orWhere('keterangan', 'like', "%{$searchValue}%")
                  ->orWhere('tahun_anggaran', 'like', "%{$searchValue}%");
            });
        }
 
        // Clone the builder before pagination to get full filtered totals
        $totalQuery = clone $query;

        // Total records
        $totalRecords = Rab::count();
        $filteredRecords = $query->count();
 
        // Ordering
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
 
            $columns = [
                0 => 'id',
                1 => 'bulan',
                2 => 'keterangan',
                3 => 'kegiatan',
                4 => 'jumlah_anggaran',
                5 => 'pengeluaran_sum_jumlah_pengeluaran',
                7 => 'status_id',
                8 => 'user_id'
            ];
 
            if (isset($columns[$orderColumn])) {
                $colName = $columns[$orderColumn];
                if ($colName === 'pengeluaran_sum_jumlah_pengeluaran') {
                    $query->orderBy('pengeluaran_sum_jumlah_pengeluaran', $orderDir);
                } else {
                    $query->orderBy($colName, $orderDir);
                }
            }
        } else {
            $query->latest();
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
 
        $data = $query->skip($start)->take($length)->get();
 
        // ====== Aggregate memakai subquery + rentang tanggal (sargable) ======

        // Total anggaran untuk data terfilter.
        $totalAnggaran = (clone $totalQuery)->sum('jumlah_anggaran');

        // Subquery id RAB terfilter → penyaringan pengeluaran dilakukan di database
        // (sebelumnya semua id ditarik ke memori lalu dikirim sebagai whereIn besar).
        $filteredRabIds = (clone $totalQuery)->select('id');

        $totalTerealisasi = Pengeluaran::where('status_id', 3)
            ->whereIn('rab_id', $filteredRabIds)
            ->sum('jumlah_pengeluaran');

        $sisaAnggaran = $totalAnggaran - $totalTerealisasi;

        // ====== Saldo (pendapatan - pengeluaran) per periode terfilter ======
        // Catatan: filter periode memakai rentang tanggal (whereBetween), bukan
        // whereYear/whereMonth yang membungkus kolom dengan fungsi → index terpakai.

        $pembayaranQuery = Pembayaran::where('status_id', 8);
        $this->applyPeriodFilter($pembayaranQuery, 'tanggal_bayar', $request);
        $pembayaran = $pembayaranQuery->sum('jumlah_bayar');

        $perusahaanQuery = Perusahaan::where('status_id', 3);
        $this->applyPeriodFilter($perusahaanQuery, 'tanggal', $request);
        $pendapatanCorporate = $perusahaanQuery->sum('harga');

        $pendapatanQuery = Pendapatan::query();
        $this->applyPeriodFilter($pendapatanQuery, 'tanggal', $request);
        $pendapatan = $pendapatanQuery->sum('jumlah_pendapatan');

        $pengeluaranQuery = Pengeluaran::where('status_id', 3);
        $this->applyPeriodFilter($pengeluaranQuery, 'tanggal_pengeluaran', $request);
        $pengeluaran = $pengeluaranQuery->sum('jumlah_pengeluaran');

        $totalSaldo = $pembayaran + $pendapatanCorporate + $pendapatan - $pengeluaran;
 
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September'
        ];

        $formattedData = $data->map(function ($item, $index) use ($start, $namaBulan) {
            $bulanName = $namaBulan[$item->bulan] ?? $item->bulan;
            $terealisasi = $item->pengeluaran_sum_jumlah_pengeluaran ?? 0;
            $sisa = $item->jumlah_anggaran - $terealisasi;

            return [
                'DT_RowIndex' => $start + $index + 1,
                'id' => $item->id,
                'bulan_tahun' => "$bulanName / $item->tahun_anggaran",
                'keterangan' => $item->keterangan ?? '',
                'kegiatan' => $item->kegiatan,
                'jumlah_anggaran' => $item->jumlah_anggaran,
                'terealisasi' => $terealisasi,
                'sisa' => $sisa,
                'status_id' => $item->status_id,
                'status_nama' => $item->status ? $item->status->nama_status : '-',
                'admin_nama' => $item->usr ? $item->usr->name : '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData,
            'total' => $totalAnggaran,
            'terealisasi' => $totalTerealisasi,
            'sisa' => $sisaAnggaran,
            'saldo' => $totalSaldo,
        ]);
    }

    /**
     * Terapkan filter periode (bulan/tahun) secara sargable.
     *
     * whereYear()/whereMonth() membungkus kolom dengan fungsi (YEAR()/MONTH())
     * sehingga index pada kolom tanggal tidak bisa dipakai. Di sini dipakai
     * rentang tanggal (whereBetween) agar index tetap terpakai.
     *
     * Catatan: kolom `pendapatan.tanggal` & `perusahaan.tanggal` bertipe varchar
     * dengan format ISO (YYYY-MM-DD) — perbandingan rentang string tetap setara.
     */
    private function applyPeriodFilter($query, string $column, Request $request): void
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : null;
        $bulan = $request->filled('bulan') ? (int) $request->bulan : null;

        if ($tahun && $bulan) {
            $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $query->whereBetween($column, [$start, $start->copy()->endOfMonth()]);

            return;
        }

        if ($tahun) {
            $start = Carbon::create($tahun, 1, 1)->startOfYear();
            $query->whereBetween($column, [$start, $start->copy()->endOfYear()]);

            return;
        }

        if ($bulan) {
            // Bulan tanpa tahun (perilaku lama: semua tahun) tidak bisa diwakili
            // satu rentang tanggal, jadi tetap memakai whereMonth.
            $query->whereMonth($column, $bulan);
        }
    }

    public function detail($id)
    {
        try {
            $rab = Rab::with(['usr', 'status', 'pengeluaran'])->findOrFail($id);

            // Calculate totals for this specific RAB - include all pengeluaran for debugging
            $allPengeluaran = $rab->pengeluaran;
            $approvedPengeluaran = $rab->pengeluaran->where('status_id', 3);
            
            $totalTerealisasi = $approvedPengeluaran->sum('jumlah_pengeluaran');
            $sisaAnggaran = $rab->jumlah_anggaran - $totalTerealisasi;

            return response()->json([
                'success' => true,
                'rab' => $rab,
                'totalTerealisasi' => $totalTerealisasi,
                'sisaAnggaran' => $sisaAnggaran,
                'bulanNama' => \Carbon\Carbon::createFromFormat('m', $rab->bulan)->translatedFormat('F'),
                'pengeluaranCount' => $allPengeluaran->count(),
                'approvedPengeluaranCount' => $approvedPengeluaran->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getKegiatan()
    {
        $kegiatan = Rab::select('kegiatan')
            ->distinct()
            ->orderBy('kegiatan')
            ->pluck('kegiatan');

        return response()->json($kegiatan);
    }

    public function editRab($id)
    {
        $rab = Rab::findOrFail($id);
        return view('rab.edit-rab',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'rab' => $rab,
        ]);
    }

    public function updateRab(Request $request, $id)
    {
        $rab = Rab::findOrFail($id);

        $rab->jumlah_anggaran = $request->jumlah_anggaran;
        $rab->tahun_anggaran = $request->tahun_anggaran;
        $rab->bulan = $request->bulan;
        $rab->kegiatan = $request->kegiatan;
        $rab->item = $request->item;
        $rab->keterangan = $request->keterangan;
        $rab->save();

        return redirect('/rab')->with('toast_success', 'Update RAB Berhasil');
    }

    public function hapusRab($id)
    {
        $rab = Rab::findOrFail($id);
        $rab->delete();

        // Pengeluaran
        $pengeluaran = Pengeluaran::where('rab_id', $id)->get();
        foreach ($pengeluaran as $peng) {
            $peng->delete();
        }

        return redirect('/rab')->with('toast_success', 'Hapus RAB Berhasil');
    }

}