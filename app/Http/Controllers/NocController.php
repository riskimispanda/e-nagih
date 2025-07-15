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
         return view('NOC.interface-mikrotik', [
             'router_id' => $router->id,
             'users' => auth()->user(),
             'roles' => auth()->user()->roles,
         ]);
     }
     
     public function realtime($id)
     {
         $router = Router::findOrFail($id);
         $client = MikrotikServices::connect($router);
         
         // Ganti "ether1" sesuai nama interface aktif kamu
         $traffic = MikrotikServices::getInterfaceTraffic($client, 'sfp-sfpplus1');
         
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

        $paket = $customer->paket->nama_paket;
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

        return redirect()->back()->with('success', 'Antrian assigned successfully');
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

    public function profilePaket()
    {
        $router = Router::paginate(10);
        $paket = Paket::with('customer', 'router')
                     ->orderByRaw("CASE WHEN nama_paket = 'ISOLIREBILLING' THEN 1 ELSE 0 END")
                     ->orderBy('nama_paket', 'asc')
                     ->paginate(10);

        return view('NOC.profile-paket',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'paket' => $paket,
            'router' => $router
        ]);
    }

    public function tambahPaket(Request $request)
    {
        $paket = new Paket();
        $paket->router_id = $request->router_id;
        $paket->nama_paket = $request->nama_paket;
        $paket->harga = $request->hargaRaw;
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

}
