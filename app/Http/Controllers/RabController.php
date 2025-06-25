<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rab;
use Spatie\Activitylog\Models\Activity;

class RabController extends Controller
{
    public function index()
    {
        $data = Rab::latest()->paginate(10);
        // dd($data);
        // dd($rab);
        return view('/rab/rab',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data,
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
        $query = Rab::query();
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
    
        if ($request->filled('tahun')) {
            $query->where('tahun_anggaran', $request->tahun);
        }
    
        $data = $query->latest()->paginate(10);
        // dd($data);
        $total = $query->sum('jumlah_anggaran');

        $html = view('rab.partials.data-table', compact('data'))->render();
        return response()->json([
            'html' => $html,
            'total' => $total
        ]);
    }

}
