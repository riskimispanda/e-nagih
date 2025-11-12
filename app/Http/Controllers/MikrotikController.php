<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikServices;
use App\Models\Router;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class MikrotikController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $router = Router::findOrFail(2); // atau sesuaikan dengan ID dinamis dari route
        $client = MikrotikServices::connect($router);

        $inter = MikrotikServices::trafficPelanggan($router, 'SAHID-Office@niscala.net.id');
        $user = MikrotikServices::getPPPSecret($client);
        $tes = MikrotikServices::testKoneksi($router->ip_address, $router->port, $router->username, $router->password);
        $firewall = MikrotikServices::getFirewallRules($router);
        // 1. Ganti semua profile dari ISOLIR ke profile paket
        $result = MikrotikServices::changeAllProfilesFromIsolirToPackage($client);

        // Aktifkan dengan opsi advanced
        // $result = MikrotikServices::activateAllCustomersAdvanced($client, [
        //     'only_disabled' => true,
        //     'profile_filter' => 'profile-UpTo-5', // Hanya profile tertentu
        //     'limit' => 50, // Maksimal 50 pelanggan
        //     'delay_between_activation' => 1, // Delay 1 detik antara aktivasi
        // ]);

        dd($result);
    }

    public function testKoneksi($id)
    {
        $router = Router::findOrFail($id);

        try {
            $client = MikrotikServices::connect($router);
            $info = MikrotikServices::testConnection($client);

            return response()->json([
                'status' => 'success',
                'info' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function traffic($id)
    {
        $router = Router::findOrFail($id);
        $client = MikrotikServices::connect($router);
        $traffic = MikrotikServices::getInterfaceTraffic($client, 'ether1');
        return response()->json($traffic);
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

    public function trafficPelanggan($id)
    {
        $pelanggan = Customer::findOrFail($id);
        $router    = Router::findOrFail($pelanggan->router_id);
        $result = MikrotikServices::trafficPelanggan($router, $pelanggan->usersecret);
        // dd($result);
        return view('pelanggan.traffic-pelanggan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pelanggan' => $pelanggan
        ]);
    }

    // �� Endpoint untuk AJAX polling
    public function trafficData($id)
    {
        $pelanggan = Customer::findOrFail($id);
        $router    = Router::findOrFail($pelanggan->router_id);

        $result = MikrotikServices::trafficPelanggan($router, $pelanggan->usersecret);

        return response()->json([
            'rx'     => $result['rx'] ?? 0,
            'tx'     => $result['tx'] ?? 0,
            'uptime' => $result['uptime'] ?? null,
            'status' => $result['status'] ?? 'offline',
            'msg'    => $result['message'] ?? '-',
            'mac_address' => $result['mac_address'] ?? null,
            'ip_local' => $result['ip_local'] ?? null,
            'ip_remote' => $result['ip_remote'] ?? null,
            'profile' => $result['profile'] ?? null,
            'total_rx' => $result['total_rx'] ?? null,
            'total_tx' => $result['total_tx'] ?? null,
        ]);
    }
}