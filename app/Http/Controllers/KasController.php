<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kas; // Assuming you have a Kas model
use App\Models\Pembayaran;
use App\Models\Pendapatan;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class KasController extends Controller
{
    public function index()
    {
        // Logic to display the list of kas entries
        $kas = Kas::latest()->where('status_id', 3)->paginate(10);

        $tt = Kas::where('status_id', 3)->sum('debit');
        $tt2 = Kas::where('status_id', 3)->sum('kredit');
        $saldo = $tt - $tt2;

        // Kas Besar
        $debitBesar = Kas::where('kas_id', 1)->where('status_id', 3)->sum('debit');
        $kreditBesar = Kas::where('kas_id', 1)->where('status_id', 3)->sum('kredit');
        $jumlah = $debitBesar - $kreditBesar;

        // Kas Kecil
        $debitKecil = Kas::where('kas_id', 2)->where('status_id', 3)->sum('debit');
        $kreditKecil = Kas::where('kas_id', 2)->where('status_id', 3)->sum('kredit');
        $totalKasKecil = $debitKecil - $kreditKecil;

        $tanggal = Kas::latest()->first('tanggal_kas');
        // dd($tanggal);
        // dd($jumlahKasLatest);
        return view('/keuangan/kas',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'kas' => $kas,
            'jumlah' => $jumlah,
            'tanggal' => $tanggal,
            'totalKasKecil' => $totalKasKecil,
            'saldo' => $saldo
        ]);
    }

    public function tambahKas(Request $request)
    {
        // dd($totalKasKecil);

        $kasBesar = DB::table('kas')
            ->where('kas_id', 1)
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(kredit), 0) as saldo')
            ->value('saldo');


        // dd($kasBesar);

        // Cek apakah cukup
        if ($request->jumlah > $kasBesar) {
            return back()->with('toast_error', 'Saldo kas besar tidak cukup.');
        }

        $kas = new Kas();
        $kas->debit = $request->jumlah;
        $kas->tanggal_kas = $request->tanggal;
        $kas->keterangan = 'Deposit dari Kas Besar';
        $kas->kas_id = 2;
        $kas->user_id = auth()->user()->id;
        $kas->status_id = 3;
        $kas->save();

        $kasBesar = new Kas();
        $kasBesar->kredit = $request->jumlah;
        $kasBesar->tanggal_kas = $request->tanggal;
        $kasBesar->keterangan = 'Pindah ke Kas Kecil';
        $kasBesar->kas_id = 1;
        $kasBesar->user_id = auth()->user()->id;
        $kasBesar->status_id = 3;
        $kasBesar->save();

        activity('kas')
            ->performedOn($kas)
            ->causedBy(auth()->user())
            ->log('Menambah Kas Kecil');

        return redirect('/kas')->with('toast_success', 'Kas Kecil Berhasil di Tambah');
    }

    public function kecil()
    {
        $kasKecil = Kas::where('kas_id', 2)->latest()->paginate(10);

        $bulanKemarin = Carbon::now()->subMonth();

        $sisaKasKecilBulanLalu = Kas::where('kas_id', 2)
            ->whereMonth('tanggal_kas', $bulanKemarin->month)
            ->whereYear('tanggal_kas', $bulanKemarin->year)
            ->selectRaw('SUM(debit) - SUM(kredit) as sisa')
            ->value('sisa');
        // dd($sisaKasKecilBulanLalu);
        $jumlahKecil = Kas::where('kas_id', 2)->sum('debit');
        $kasKecill = Kas::where('kas_id', 2)->sum('kredit');
        $total = $jumlahKecil - $kasKecill;
        $transaksi = Kas::where('kas_id', 2)->count();
        // dd($transaksi);
        return view('/keuangan/kas-kecil',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'kas' => $kasKecil,
            'jumlah' => $total,
            'transaksi' => $transaksi,
            'sisa' => $sisaKasKecilBulanLalu,
        ]);
    }

    public function besar()
    {
        $kasBesar = Kas::where('kas_id', 1)->latest('tanggal_kas')->paginate(10);
        return view('/keuangan/kas-besar',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'kas' => $kasBesar,
        ]);
    }

}
