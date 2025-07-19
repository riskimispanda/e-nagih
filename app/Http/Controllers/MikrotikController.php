<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikServices;
use App\Models\Router;
use RouterOS\Query;

class MikrotikController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
public function index()
{
    $router = Router::findOrFail(2); // atau sesuaikan dengan ID dinamis dari route
    $client = MikrotikServices::connect($router);

    $profiles = MikrotikServices::trafficPelanggan($router, 'Rizky-I107@niscala.net.id');
    $user = MikrotikServices::getPPPSecret($client);
    $logs = MikrotikServices::activeConnections($router);
    $tes = MikrotikServices::testKoneksi($router->ip_address, $router->port, $router->username, $router->password);
    dd($logs, $user);
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
}
