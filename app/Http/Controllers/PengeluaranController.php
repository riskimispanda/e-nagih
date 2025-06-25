<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Metode;
use App\Models\Pengeluaran;
use App\Models\JenisKas;
use App\Models\Kas;
use App\Models\Rab;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    public function index()
    {
        $tahunSekarang = Carbon::now()->year;
        $rab = Rab::where('status_id', 12)
                ->where('tahun_anggaran', $tahunSekarang)
                ->get();

        $kas = JenisKas::all();
        $metodes = Metode::all();
        $pengeluarans = Pengeluaran::with('user')
            ->orderBy('created_at', 'desc')
            ->orderBy('tanggal_pengeluaran', 'desc')
            ->get();

        $totalPengeluaran = Pengeluaran::sum('jumlah_pengeluaran');
        $dailyPengeluaran = Pengeluaran::whereDate('tanggal_pengeluaran', now())->sum('jumlah_pengeluaran');
        $mounthlyPengeluaran = Pengeluaran::whereMonth('tanggal_pengeluaran', now()->month)->sum('jumlah_pengeluaran');


        return view('keuangan.pengeluaran',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'metodes' => $metodes,
            'pengeluarans' => $pengeluarans,
            'totalPengeluaran' => $totalPengeluaran,
            'dailyPengeluaran' => $dailyPengeluaran,
            'mounthlyPengeluaran' => $mounthlyPengeluaran,
            'kas' => $kas,
            'rab' => $rab
        ]);
    }

    public function tambahPengeluaran(Request $request)
    {
        // dd($request->all());
        $path = $request->file('buktiPengeluaran')->getClientOriginalName();
        $request->file('buktiPengeluaran')->storeAs('public/uploads', $path);


        $pengeluaran = new Pengeluaran();
        $pengeluaran->jumlah_pengeluaran = $request->jumlahPengeluaran;
        $pengeluaran->jenis_pengeluaran = $request->jenisPengeluaran;
        $pengeluaran->keterangan = $request->keterangan;
        $pengeluaran->metode_bayar = $request->metodePengeluaran;
        $pengeluaran->user_id = auth()->id();
        $pengeluaran->bukti_pengeluaran = $path;
        $pengeluaran->kas_id = $request->kas_id;
        $pengeluaran->tanggal_pengeluaran = $request->tanggalPengeluaran; // Set current date and time
        $pengeluaran->save();

        
        $kas = new Kas();
        $kas->kredit = $request->jumlahPengeluaran;
        $kas->keterangan = $request->keterangan;
        $kas->tanggal_kas = $request->tanggalPengeluaran;
        $kas->kas_id = $request->kas_id;
        $kas->user_id = auth()->id();
        $kas->save();

        return redirect('/pengeluaran/global')->with('success', 'Pengeluaran berhasil ditambahkan');
    }

}
