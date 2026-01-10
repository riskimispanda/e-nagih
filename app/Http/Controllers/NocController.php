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
use Illuminate\Support\Facades\Log;



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
            'customer' => Customer::where('status_id', 1)->where('teknisi_id', null)->paginate(10),
            'perusahaan' => Perusahaan::where('status_id', 5)->paginate(10),
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
        $customer->refresh();
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

        // Notif Ke Teknisi
        // $chat = new ChatServices();
        // foreach ($teknisi as $tek) {
        //     $nomor = preg_replace('/[^0-9]/', '', $tek->no_hp);
        //     if (str_starts_with($nomor, '0')) {
        //         $nomor = '62' . substr($nomor, 1);
        //     }

        //     $chat->kirimNotifikasiTeknisi($nomor, $tek);
        // }

        // ? Catat Log
        activity('Dial Customer')
            ->causedBy(auth()->user())
            ->log(auth()->user()->name . ' Membuat dial untuk pelanggan ' . $customer->nama_customer);

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

        try {
        $paket = Paket::with('customer', 'router')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_paket', 'like', '%' . $search . '%');
            })
            ->orderByRaw("CASE WHEN nama_paket = 'ISOLIREBILLING' THEN 1 ELSE 0 END")
            ->orderBy('nama_paket', 'asc')
            ->paginate(10, ['*'], 'paket_page')
            ->withQueryString();
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data paket', [
                'search' => $search,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $paket = collect([]);
        }

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

    public function ajaxPaket(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('🔍 ajaxPaket method called', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'params' => $request->all(),
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            $search = $request->input('search');
            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            // Validate per_page to prevent memory issues
            if ($perPage > 100) {
                $perPage = 100;
            }

            Log::info('📊 Query parameters', [
                'search' => $search,
                'perPage' => $perPage,
                'page' => $page
            ]);

            // Build query with proper error handling for relationships
            $query = Paket::query();

            // Check if relationships exist before eager loading
            try {
                $query = $query->with(['customer', 'router']);
            } catch (\Exception $e) {
                Log::warning('⚠️ Relationship loading issue', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue without relationships if they cause issues
            }

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_paket', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%');
                });
            }

            // Apply ordering
            $query->orderByRaw("CASE WHEN nama_paket = 'ISOLIREBILLING' THEN 1 ELSE 0 END")
                ->orderBy('nama_paket', 'asc');

            Log::info('🔍 Executing query...');

            // Execute pagination
            $paket = $query->paginate($perPage, ['*'], 'page', $page);

            Log::info('✅ Query executed successfully', [
                'total' => $paket->total(),
                'current_page' => $paket->currentPage(),
                'last_page' => $paket->lastPage(),
                'items_count' => $paket->count()
            ]);

            // Prepare response data
            $responseData = [
                'success' => true,
                'message' => 'Data paket berhasil dimuat',
                'data' => $paket->items(),
                'current_page' => $paket->currentPage(),
                'last_page' => $paket->lastPage(),
                'per_page' => $paket->perPage(),
                'total' => $paket->total(),
                'from' => $paket->firstItem(),
                'to' => $paket->lastItem(),
                'has_more_pages' => $paket->hasMorePages(),
                'timestamp' => now()->toISOString()
            ];

            Log::info('📤 Sending JSON response', [
                'response_size' => strlen(json_encode($responseData)),
                'items_count' => count($responseData['data'])
            ]);

            // Ensure we're returning JSON with proper headers
            return response()->json($responseData, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('❌ Database query error in ajaxPaket', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data paket - Database error',
                'error' => config('app.debug') ? $e->getMessage() : 'Database connection issue',
                'timestamp' => now()->toISOString()
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ General error in ajaxPaket', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data paket - Server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'timestamp' => now()->toISOString()
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Fatal error in ajaxPaket', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data paket - Fatal error',
                'error' => config('app.debug') ? $e->getMessage() : 'System error',
                'timestamp' => now()->toISOString()
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    }

    // Add this method to test if the controller is being called correctly
    public function testAjaxPaket(Request $request)
    {
        Log::info('🧪 testAjaxPaket called', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test endpoint working correctly',
            'method' => 'testAjaxPaket',
            'timestamp' => now()->toISOString(),
            'request_info' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]
        ], 200, [
            'Content-Type' => 'application/json'
        ]);
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

    public function editAntrian($id)
    {
        $customer = Customer::findOrFail($id);
        $router = Router::all();
        $paket = Paket::all();
        return view('/NOC/editAntrian',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'antrian' => $customer,
            'paket' => $paket,
            'router' => $router
        ]);
    }

    public function simpanEdit(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $router = Router::findOrFail($customer->router_id);

        // Pastikan paket yang baru diambil berdasarkan paket_id yang dipilih
        $paket = Paket::find($request->paket_id);
        if (!$paket) {
            return redirect()->back()->with('error', 'Paket tidak ditemukan');
        }

        $koneksi = Koneksi::findOrFail($customer->koneksi_id);
        $konek = strtolower($koneksi->nama_koneksi);

        // Update database Laravel
        $customer->update([
            'usersecret' => $request->usersecret,
            'pass_secret' => $request->pass,
            'remote' => $request->remote,
            'remote_address' => $request->remote,
            'local_address' => $request->local_address,
            'paket_id' => $request->paket_id,
            'router_id' => $request->router_id
        ]);

        $client = MikrotikServices::connect($router);

        // Data untuk PPP Secret
        $pppData = [
            'name'          => $request->usersecret,
            'password'      => $request->pass,
            'remote-address' => $request->remote,
            'local-address'  => $request->local_address,
            'profile'       => $paket->paket_name, // Gunakan nama paket dari database
            'service'       => $konek
        ];

        Log::info("PPP Data to be sent: " . json_encode($pppData));

        // Cek dan update atau buat baru
        $existingSecret = MikrotikServices::checkPPPSecret($client, $request->usersecret);

        if ($existingSecret) {
            Log::info("Existing secret found: " . json_encode($existingSecret));
            MikrotikServices::updatePPPSecret($client, $existingSecret['.id'], $pppData);
            $disconnectResult = MikrotikServices::removeActiveConnections($client, $request->usersecret);
            $message = 'Berhasil Update PPP Secret di Mikrotik';
        } else {
            MikrotikServices::addPPPSecret($client, $pppData);
            $message = 'Berhasil Membuat PPP Secret Baru di Mikrotik';
        }

        return redirect('/teknisi/antrian')->with('toast_success', $message);
    }

    public function getPaketByRouter($routerId)
    {
        $paket = Paket::where('router_id', $routerId)->get();
        return response()->json($paket);
    }
}
