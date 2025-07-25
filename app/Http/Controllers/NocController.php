<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\Koneksi;
use App\Models\Router;
use App\Services\MikrotikServices;
use App\Models\Perusahaan;
use App\Models\Paket;


class NocController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function interface($id)
     {
         $router = Router::findOrFail($id);
         $client = MikrotikServices::connect($router);
         $status = MikrotikServices::status($router);
         return view('NOC.interface-mikrotik', [
             'router_id' => $router->id,
             'users' => auth()->user(),
             'roles' => auth()->user()->roles,
             'status' => $status,
         ]);
     }

     public function realtime($id)
     {
         $router = Router::findOrFail($id);
         $client = MikrotikServices::connect($router);
     
         // Pastikan koneksi berhasil
         if (!$client) {
             return response()->json(['error' => 'Gagal koneksi ke router'], 500);
         }
     
         // Pilih interface berdasarkan ID router
         if ($router->id === 2) {
             $interface = 'vlan-5Mbps';
         } elseif ($router->id === 3) {
             $interface = 'sfp+2';
         } else {
             $interface = 'vlan-5Mbps'; // Default interface
         }
     
         $traffic = MikrotikServices::getInterfaceTraffic($client, $interface);
     
         return response()->json($traffic);
     }
     


    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function antrian()
    {
        return view('NOC.data-antrian-noc',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => Customer::where('status_id', 1)->where('teknisi_id', null)->get(),
            'perusahaan' => Perusahaan::where('status_id', 5)->get(),
        ]);
    }

    public function prosesAntrian($id)
    {
        return view('NOC.proses-antrian',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => Customer::findOrFail($id),
            'teknisi' => User::where('roles_id', 5)->get(),
            'koneksi' => Koneksi::all(),
            'router' => Router::all(),
        ]);
    }

    public function assign(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $router = Router::findOrFail($request->router_id);
        // dd($router);
        $paket = $customer->paket->paket_name;
        // dd($paket);
        $koneksi = Koneksi::findOrFail($request->koneksi_id);
        $konek = strtolower($koneksi->nama_koneksi);

        // Update data customer
        $customer->update([
            'koneksi_id' => $request->koneksi_id,
            'usersecret' => $request->usersecret,
            'remote_address' => $request->remote_address,
            'router_id' => $request->router_id,
            'local_address' => $request->local_address,
            'pass_secret' => $request->password,
            'status_id' => 5,
            'remote' => $request->remote,
        ]);

        // 🔌 Connect ke router sekali saja
        $client = MikrotikServices::connect($router);

        // ➕ Tambahkan PPP Secret
        MikrotikServices::addPPPSecret($client, [
            'name' => $request->usersecret,
            'password' => $request->password,
            'remoteAddress' => $request->remote_address,
            'localAddress' => $request->local_address,
            'profile' => $paket,
            'service' => $konek,
        ]);

        return redirect('/data/antrian-noc')->with('success', 'Antrian assigned successfully');
    }

    public function antrianPerusahaan($id)
    {
        $corp = Perusahaan::findOrFail($id);
        // dd($corp);
        $teknisi = User::where('roles_id', 5)->get();
        return view('/NOC/perusahaan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'corp' => $corp,
            'teknisi' => $teknisi
        ]);
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

    public function profilePaket(Request $request)
{
    $search = $request->input('search'); // Tangkap search query

    $router = Router::paginate(10);

    // Transformasi status koneksi
    $router->getCollection()->transform(function ($r) {
        $status = app(MikrotikServices::class)->status($r);
        $r->status_koneksi = $status['connected'] ? '🟢 Online' : '🔴 Offline';
        $r->uptime = $status['uptime'] ?? null;
        $r->platform = $status['platform'] ?? null;
        $r->cpu_load = $status['cpu_load'] ?? null;
        return $r;
    });

    $paket = Paket::with('customer', 'router')
        ->when($search, function ($query) use ($search) {
            $query->where('nama_paket', 'like', '%' . $search . '%');
        })
        ->orderByRaw("CASE WHEN nama_paket = 'ISOLIREBILLING' THEN 1 ELSE 0 END")
        ->orderBy('nama_paket', 'asc')
        ->paginate(10)
        ->withQueryString();

    return view('NOC.profile-paket', [
        'users' => auth()->user(),
        'roles' => auth()->user()->roles,
        'paket' => $paket,
        'router' => $router,
    ]);
}



    public function tambahPaket(Request $request)
    {
        $paket = new Paket();
        $paket->router_id = $request->router_id;
        $paket->nama_paket = $request->nama_paket;
        $paket->harga = $request->hargaRaw;
        $paket->paket_name = $request->profile_name;
        $paket->save();

        return redirect()->back()->with('success', 'Paket berhasil ditambahkan');
    }

    public function hapusPaket($id)
    {
        $paket = Paket::findOrFail($id);
        $paket->delete();

        return redirect()->back()->with('success', 'Paket berhasil dihapus');
    }

    public function tambahRouter(Request $request)
    {
        $router = new Router();
        $router->nama_router = $request->nama_router;
        $router->ip_address = $request->ip_address;
        $router->port = $request->port;
        $router->username = $request->username;
        $router->password = $request->password;
        $router->save();

        return redirect()->back()->with('success', 'Router berhasil ditambahkan');
    }

    public function editRouter($id)
    {
        $router = Router::findOrFail($id);
        return response()->json($router);
    }

    public function updateRouter(Request $request, $id)
    {
        $router = Router::findOrFail($id);
        $router->nama_router = $request->nama_router;
        $router->ip_address = $request->ip_address;
        $router->port = $request->port;
        $router->username = $request->username;
        $router->password = $request->password;
        $router->save();

        return redirect()->back()->with('success', 'Router berhasil diperbarui');
    }

    public function editPaket($id)
    {
        $paket = Paket::with('router')->findOrFail($id);
        return response()->json($paket);
    }

    public function updatePaket(Request $request, $id)
    {
        $paket = Paket::findOrFail($id);
        $paket->nama_paket = $request->nama_paket;
        $paket->paket_name = $request->profile_name;
        $paket->router_id = $request->router_id;
        $paket->harga = $request->hargaRaw;
        $paket->save();

        return redirect()->back()->with('success', 'Paket berhasil diperbarui');
    }

    // PaketController.php
    public function ajaxPaket(Request $request)
    {
        $paket = Paket::with('customer', 'router')->get();

        return response()->json([
            'data' => $paket,
        ]);
    }


}
