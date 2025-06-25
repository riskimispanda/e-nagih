<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Services\MikrotikServices;
use App\Models\Koneksi;
use App\Models\Perangkat;
use App\Models\Server as ServerModels;
use App\Models\Lokasi;
use App\Models\ODC;
use App\Models\ODP;
use App\Models\MediaKoneksi;
use App\Models\Invoice;
use App\Models\Perusahaan;

class TeknisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    //  Rumus Prorate
     private function calculateProrate($hargaPaket, $tanggalInstalasi)
     {
         // Mendapatkan jumlah hari dalam bulan
         $jumlahHariDalamBulan = $tanggalInstalasi->daysInMonth;
         // Mendapatkan hari terakhir bulan
         $akhirBulan = $tanggalInstalasi->copy()->endOfMonth();
         // Menghitung jumlah hari tersisa dalam bulan (termasuk hari instalasi)
         $jumlahHariTersisa = $tanggalInstalasi->diffInDays($akhirBulan) + 1;
         // Menghitung tagihan prorata
         $tagihanProrata = ($hargaPaket / $jumlahHariDalamBulan) * $jumlahHariTersisa;
         // Membulatkan ke bilangan bulat (opsional, sesuaikan dengan kebutuhan)
         return round($tagihanProrata);
     }

    public function index()
    {
        $teknisi = auth()->user()->id;
        $customers = Customer::all();
        $customersWithTeknisi = Customer::whereNotNull('teknisi_id')->exists();
        $user = auth()->user();

        $corp = Perusahaan::where('status_id', 1)
            ->when($user->roles_id != 4, function ($query) use ($user) {
                $query->where('admin_id', $user->id);
            })
            ->get();

        return view('/teknisi/data-antrian-teknisi', [
            'data' => $customers,
            'antrian' => Customer::where('status_id', 2)->get(),
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customersWithTeknisi' => $customersWithTeknisi,
            'corp' => $corp
        ]);
    }

    public function selesai($id)
    {
        $customer = Customer::find($id);
        $customer->status_id = 2;
        $customer->teknisi_id = auth()->user()->id;
        $customer->save();

        return redirect()->back()->with('toast_success', 'Antrian dipilih');
    }

    public function print($id)
    {
        $customer = Customer::find($id);
        $customer->status_id = 2;
        $customer->save();

        $server = ServerModels::all();

        return view('/teknisi/detail-antrian', [
            'customer' => $customer,
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'mikrotik' => (new MikrotikServices())->getProfile(),
            'paket' => (new MikrotikServices())->getProfiles(),
            'koneksi' => Koneksi::all(),
            'modem' => Perangkat::all(),
            'server' => ServerModels::all(),
            'olt' => Lokasi::all(),
            'odc' => ODC::all(),
            'odp' => ODP::all(),
            'media' => MediaKoneksi::all(),
        ]);
    }

    public function getByServer($serverId)
    {
        $olt = Lokasi::where('id_server', $serverId)->get();
        return response()->json($olt);
    }

    public function getByOdc($odcId)
    {
        $odc = ODC::where('lokasi_id', $odcId)->get();
        // dd($odc);
        return response()->json($odc);
    }

    public function getByOdp($odpId)
    {
        $odp = ODP::where('odc_id', $odpId)->get();
        return response()->json($odp);
    }

    public function konfirmasi(Request $request, $id)
    {
        // Cari customer atau tampilkan error 404 jika tidak ditemukan
        $customer = Customer::findOrFail($id);
        // dd($customer);
        if($request->hasFile('foto_rumah')) {
            $foto_rumah = $request->file('foto_rumah');
            $fileName = time() . '_' . str_replace(' ', '_', $foto_rumah->getClientOriginalName());
            $foto_rumah->move(public_path('uploads/foto_rumah'), $fileName);
            $foto_rumah = 'uploads/foto_rumah/' . $fileName;
        } else {
            $foto_rumah = null;
        }
        if($request->hasFile('foto_perangkat')) {
            $foto_perangkat = $request->file('foto_perangkat');
            $fileName = time() . '_' . str_replace(' ', '_', $foto_perangkat->getClientOriginalName());
            $foto_perangkat->move(public_path('uploads/foto_perangkat'), $fileName);
            $foto_perangkat = 'uploads/foto_perangkat/' . $fileName;
        } else {
            $foto_perangkat = null;
        }
        // Tanggal selesai instalasi
        $tanggalSelesai = now();

        // Update data customer
        $customer->update([
            'panjang_kabel' => $request->panjang_kabel,
            'redaman' => $request->redaman,
            'transiver' => $request->transiver,
            'receiver' => $request->receiver,
            'access_point' => $request->access_point,
            'station' => $request->station,
            'media_id' => $request->media_id,
            'tanggal_selesai' => $tanggalSelesai,
            'lokasi_id' => $request->odp,
            'status_id' => 3,
            'foto_rumah' => $foto_rumah,
            'foto_perangkat' => $foto_perangkat,
            'mac_address' => $request->mac_address,
            'perangkat_id' => $request->modem,
            'seri_perangkat' => $request->serial_number,
        ]);

        // Hitung jatuh tempo sampai akhir bulan
        $jatuhTempo = $tanggalSelesai->copy()->endOfMonth();
        $tambahan = $request->panjang_kabel;
        $tagihanTambahan = 0;
        if($tambahan > 200)
        {
            $tagihanTambahan = ($tambahan - 200) * 1000;
        }

        // Buat invoice
        Invoice::create([
            'customer_id' => $id,
            'status_id' => 7,
            'paket_id' => $customer->paket_id,
            'tagihan' => $this->calculateProrate($customer->paket->harga, $tanggalSelesai),
            'jatuh_tempo' => $jatuhTempo,
            'tambahan' => $tagihanTambahan,
        ]);

        return redirect()->route('teknisi')->with('toast_success', 'Instalasi Selesai');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
}
