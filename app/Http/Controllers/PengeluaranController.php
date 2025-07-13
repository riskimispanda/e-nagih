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
use Spatie\Activitylog\Models\Activity;

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
            ->where('status_id', 3)
            ->get();

        $totalPengeluaran = Pengeluaran::sum('jumlah_pengeluaran');
        $dailyPengeluaran = Pengeluaran::whereDate('tanggal_pengeluaran', now())->sum('jumlah_pengeluaran');
        $mounthlyPengeluaran = Pengeluaran::whereMonth('tanggal_pengeluaran', now()->month)->sum('jumlah_pengeluaran');

        $totalRequest = Pengeluaran::where('status_id', 1)->count();

        return view('keuangan.pengeluaran',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'metodes' => $metodes,
            'pengeluarans' => $pengeluarans,
            'totalPengeluaran' => $totalPengeluaran,
            'dailyPengeluaran' => $dailyPengeluaran,
            'mounthlyPengeluaran' => $mounthlyPengeluaran,
            'kas' => $kas,
            'rab' => $rab,
            'totalRequest' => $totalRequest
        ]);
    }

    public function tambahPengeluaran(Request $request)
    {
        // dd($request->all());
        $rab = $request->rab_id ?? null;

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
        $pengeluaran->tanggal_pengeluaran = $request->tanggalPengeluaran;
        $pengeluaran->status_id = 3;
        $pengeluaran->rab_id = $request->rab_id;
        $pengeluaran->save();

        
        $kas = new Kas();
        $kas->kredit = $request->jumlahPengeluaran;
        $kas->keterangan = $request->keterangan;
        $kas->tanggal_kas = $request->tanggalPengeluaran;
        $kas->kas_id = $request->kas_id;
        $kas->user_id = auth()->id();
        $kas->pengeluaran_id = $pengeluaran->id;
        $kas->status_id = 3;
        $kas->save();

        if($rab != null){
            $rab = Rab::find($rab);
            if($rab){
                $rab->update([
                    'status_id' => 11
                ]);
            }
        }

        activity('pengeluaran')
            ->performedOn($pengeluaran)
            ->causedBy(auth()->user())
            ->log('Menambah Pengeluaran');

        return redirect('/pengeluaran/global')->with('success', 'Pengeluaran berhasil ditambahkan');
    }

    public function hapusPengeluaran(Request $request, $id)
    {
        $hapus = Pengeluaran::find($id);

        $kas = Kas::where('pengeluaran_id', $id);
        $rab = Rab::where('id', $hapus->rab_id);

        $hapus->update([
            'alasan' => $request->alasan,
            'status_id' => 1
        ]);

        $kas->update([
            'status_id' => 1
        ]);

        $rab->update([
            'status_id' => 1
        ]);

        return redirect()->back()->with('success', 'Pengeluaran berhasil direquest');
    }

    public function requestHapus()
    {
        $pengeluarans = Pengeluaran::with('user')
            ->where('status_id', 1)
            ->orderBy('created_at', 'desc')
            ->orderBy('tanggal_pengeluaran', 'desc')
            ->get();

        return view('keuangan.pengeluaran-acc',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pengeluarans' => $pengeluarans,
        ]);
    }

    public function tolakHapus($id)
    {
        $hapus = Pengeluaran::find($id);

        $kas = Kas::where('pengeluaran_id', $id);

        if ($hapus) {
            $hapus->update([
                'status_id' => 3
            ]);

            $kas->update([
                'status_id' => 3
            ]);

            Rab::where('id', $hapus->rab_id)->update([
                'status_id' => 11
            ]);

            return redirect('/pengeluaran/global')->with('success', 'Pengeluaran berhasil ditolak');
        }

        return redirect('/pengeluaran/global')->with('error', 'Data pengeluaran tidak ditemukan');
    }

    public function konfirmasiHapus($id)
    {
        $hapus = Pengeluaran::find($id);

        if ($hapus) {
            $hapus->delete();
            Kas::where('pengeluaran_id', $id)->delete();
            Rab::where('id', $hapus->rab_id)->update([
                'status_id' => 12
            ]);

            return redirect('/pengeluaran/global')->with('success', 'Pengeluaran berhasil dihapus');
        }

        return redirect('/pengeluaran/global')->with('error', 'Data pengeluaran tidak ditemukan');
    }

}
