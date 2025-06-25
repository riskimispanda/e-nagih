<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\Koneksi;
use App\Models\Router;
use App\Services\MikrotikServices;
use App\Models\Perusahaan;


class NocController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
        return view('noc.data-antrian-noc',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => Customer::where('status_id', 1)->where('teknisi_id', null)->get(),
            'perusahaan' => Perusahaan::where('status_id', 5)->get(),
        ]);
    }

    public function prosesAntrian($id)
    {
        return view('noc.proses-antrian',[
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
        // $koneksi = Koneksi::where('customer_id', $id)->first();
        // dd($koneksi);

        // $mikrotik = new MikrotikServices();
        // $routerDetails = $mikrotik->getRouterDetailsByName($router->nama_router);
        
        $updated = $customer->update([
            'koneksi_id' => $request->koneksi_id,
            'usersecret' => $request->usersecret,
            'remote_address' => $request->remote_address,
            'router_id' => $request->router_id,
            'local_address' => $request->local_address,
            'pass_secret' => $request->password,
            'status_id' => 5,
        ]);

        $d = $request->koneksi_id;
        $koneksi = Koneksi::findOrFail($d);
        $konek = strtolower($koneksi->nama_koneksi);

        $mikrotik = new MikrotikServices();
        $mikrotik->addPPPSecret([
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
        return view('/noc/perusahaan',[
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
}
