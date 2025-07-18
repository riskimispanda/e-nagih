<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rab;
use App\Models\Pengeluaran;
use Spatie\Activitylog\Models\Activity;

class RabController extends Controller
{
    public function index()
    {
        $data = Rab::with('pengeluaran')->latest()->paginate(10);

        // Calculate initial totals for all data
        $totalAnggaran = Rab::sum('jumlah_anggaran');
        $totalTerealisasi = Pengeluaran::where('status_id', 3)->sum('jumlah_pengeluaran');
        $sisaAnggaran = $totalAnggaran - $totalTerealisasi;

        return view('/rab/rab',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data,
            'totalAnggaran' => $totalAnggaran,
            'totalTerealisasi' => $totalTerealisasi,
            'sisaAnggaran' => $sisaAnggaran,
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
        $query = Rab::with('pengeluaran');

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun_anggaran', $request->tahun);
        }

        $data = $query->latest()->paginate(10);

        // Calculate totals
        $totalAnggaran = $query->sum('jumlah_anggaran');

        // Get RAB IDs from current query for calculating realized budget
        $rabIds = $query->pluck('id');

        // Calculate total realized budget (pengeluaran) for filtered RABs
        $totalTerealisasi = Pengeluaran::whereIn('rab_id', $rabIds)
            ->where('status_id', 3) // Only approved pengeluaran
            ->sum('jumlah_pengeluaran');

        // Calculate remaining budget
        $sisaAnggaran = $totalAnggaran - $totalTerealisasi;

        $html = view('rab.partials.data-table', compact('data'))->render();

        return response()->json([
            'html' => $html,
            'total' => $totalAnggaran,
            'terealisasi' => $totalTerealisasi,
            'sisa' => $sisaAnggaran
        ]);
    }

}
