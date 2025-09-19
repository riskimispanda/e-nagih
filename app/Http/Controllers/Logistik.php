<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perangkat;
use App\Models\KategoriLogistik;
use App\Models\ModemDetail;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class Logistik extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perangkat = Perangkat::all();
        $terpakai = Customer::with('perangkat')->count();
        $tersedia = Perangkat::sum('jumlah_stok');
        return view('dashboard.dashboard-logistik', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'perangkat' => $perangkat,
            'terpakai' => $terpakai,
            'tersedia' => $tersedia
        ]);
    }

    public function tambahKategori(Request $request)
    {
        $kategori = new KategoriLogistik();
        $kategori->nama_logistik = $request->nama_logistik;
        $kategori->save();
        return redirect('/data/logistik')->with('toast_success','Berhasil Menambah Kategori Untuk Logistik');
    }

    public function editLogistik($id)
    {
        $log = Perangkat::with('kategori')->findOrFail($id);
        $data = KategoriLogistik::all();
        return view('/data/edit-logistik',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'log' => $log,
            'data' => $data
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */

    public function TiketBarang()
    {
        $kategori = KategoriLogistik::whereNotIn('nama_logistik', ['Modem', 'Tenda', 'HTB', 'Kabel'])->get();
        $perangkat = Perangkat::with('kategori')
            ->whereHas('kategori', function ($q) {
                $q->whereNotIn('nama_logistik', ['Tenda', 'Modem', 'Kabel', 'HTB']);
            })->get();
        return view('logistik.tiket-barang-keluar', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'kategori' => $kategori,
            'perangkat' => $perangkat
        ]);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $nama_perangkat = $request->input('nama_perangkat');
        $jumlah = (int)$request->input('jumlah_stok');
        $harga = (int) str_replace(['Rp', '.', ' '], '', $request->input('harga'));
        // dd($total);
        $data = [
            'nama_perangkat' => $nama_perangkat,
            'jumlah_stok' => $jumlah,
            'harga' => $harga,
            'kategori_id' => $request->input('kategori_id')
        ];
        Perangkat::create($data);
        return redirect('/data/logistik')->with('toast_success', 'Perangkat Berhasil ditambahkan');
    }

    public function updateLogistik(Request $request, $id)
    {
        $perangkat = Perangkat::with('kategori')->findOrFail($id);
        $perangkat->update([
            'nama_perangkat' => $request->nama_perangkat,
            'jumlah_stok' => $request->stok,
            'harga' => $request->harga,
            'kategori_id' => $request->kategori,
        ]);

        return redirect('/data/logistik')->with('toast_success', 'Data Berhasil diperbarui');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deleteLogistik($id)
    {
        try {
            $logistik = Perangkat::findOrFail($id); // pakai findOrFail biar langsung throw error kalau tidak ada
            $logistik->delete();

            return redirect('/data/logistik')->with('toast_success', 'Berhasil hapus perangkat');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/data/logistik')->with('toast_error', 'Perangkat tidak ditemukan');
        } catch (\Exception $e) {
            // log error untuk debugging
            Log::error('Gagal hapus perangkat: '.$e->getMessage());

            return redirect('/data/logistik')->with('toast_error', 'Terjadi kesalahan saat menghapus perangkat');
        }
    }

    public function tracking(Request $request)
    {
        $search = $request->input('search');

        $data = ModemDetail::with('perangkat.kategori', 'status', 'customer')
            ->whereHas('perangkat.kategori', function ($q) {
                $q->whereIn('nama_logistik', ['modem', 'tenda']);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($qc) use ($search) {
                        $qc->where('nama_customer', 'like', "%{$search}%");
                    })
                        ->orWhereHas('perangkat', function ($qp) use ($search) {
                            $qp->where('nama_perangkat', 'like', "%{$search}%");
                        })
                        ->orWhere('mac_address', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('logistik.tracking-tools', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data
        ]);
    }

}
