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
use App\Models\Server;
use App\Models\Lokasi;
use App\Models\ODC;
use App\Models\ODP;
use App\Services\ChatServices;



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

        // Teknisi
        $teknisi = User::where('roles_id', 5)->get();

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

        // Notif Ke Teknisi
        $chat = new ChatServices();
        foreach ($teknisi as $tek) {
            $nomor = preg_replace('/[^0-9]/', '', $tek->no_hp);
            if (str_starts_with($nomor, '0')) {
                $nomor = '62' . substr($nomor, 1);
            }
        
            $chat->kirimNotifikasiTeknisi($nomor, $tek);
        }
        

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

        $router = Router::paginate(10, ['*'], 'router_page');

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
            ->paginate(10, ['*'], 'paket_page')
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
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);

        $query = Paket::with('customer', 'router')
            ->when($search, function ($q) use ($search) {
                $q->where('nama_paket', 'like', '%' . $search . '%');
            })
            ->orderByRaw("CASE WHEN nama_paket = 'ISOLIREBILLING' THEN 1 ELSE 0 END")
            ->orderBy('nama_paket', 'asc');

        $paket = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($paket);
    }

    public function editServer($id)
    {
        $server = Server::findOrFail($id);
        return response()->json($server);
    }

    public function updateServer(Request $request, $id)
    {
        $server = Server::findOrFail($id);
        $server->lokasi_server = $request->lokasi_server;
        $server->ip_address = $request->ip_address;
        $server->gps = $request->gps;
        $server->save();

        return redirect()->back()->with('toast_success', 'Server berhasil diperbarui');
    }

    public function hapusServer($id)
    {
        $s = Server::findOrFail($id);
        $s->delete();
        return redirect()->back()->with('toast_success', 'Server berhasil dihapus');
    }


    public function editOlt($id)
    {
        $olt = Lokasi::findOrFail($id);
        return response()->json($olt);
    }

    public function updateOlt(Request $request, $id)
    {
        $olt = Lokasi::findOrFail($id);
        $olt->nama_lokasi = $request->nama_lokasi;
        $olt->id_server = $request->id_server;
        $olt->gps = $request->gps;
        $olt->save();
        return redirect()->back()->with('toast_success', 'OLT berhasil diperbarui');
    }

    public function hapusOlt($id)
    {
        $olt = Lokasi::findOrFail($id);
        $olt->delete();
        return redirect()->back()->with('toast_success', 'OLT berhasil dihapus');
    }

    public function editOdc($id)
    {
        $odc = ODC::findOrFail($id);
        return response()->json($odc);
    }

    public function updateOdc(Request $request, $id)
    {
        $odc = ODC::findOrFail($id);
        $odc->nama_odc = $request->nama_odc;
        $odc->lokasi_id = $request->olt;
        $odc->gps = $request->gps;
        $odc->save();
        return redirect()->back()->with('toast_success', 'ODC berhasil diperbarui');
    }

    public function hapusOdc($id)
    {
        $odc = ODC::findOrFail($id);
        $odc->delete();
        return redirect()->back()->with('toast_success', 'ODC berhasil dihapus');
    }

    public function editOdp($id)
    {
        $odp = ODP::findOrFail($id);
        return response()->json($odp);
    }

    public function updateOdp(Request $request, $id)
    {
        $odp = ODP::findOrFail($id);
        $odp->nama_odp = $request->nama_odp;
        $odp->odc_id = $request->odc;
        $odp->gps = $request->gps;
        $odp->save();
        return redirect()->back()->with('toast_success', 'ODP berhasil diperbarui');
    }

    public function hapusOdp($id)
    {
        $odp = ODP::findOrFail($id);
        $odp->delete();
        return redirect()->back()->with('toast_success', 'ODP berhasil dihapus');
    }
}
