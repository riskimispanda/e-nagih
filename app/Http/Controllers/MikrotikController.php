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
        $router = Router::findOrFail(3); // atau sesuaikan dengan ID dinamis dari route
        $client = MikrotikServices::connect($router);

        $inter = MikrotikServices::trafficPelanggan($router, 'SAHID-Office@niscala.net.id');
        $user = MikrotikServices::getPPPSecret($client);
        $tes = MikrotikServices::testKoneksi($router->ip_address, $router->port, $router->username, $router->password);
        $firewall = MikrotikServices::getFirewallRules($router);
        dd($firewall);
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
        
        // Get WiFi clients data
        $wifiData = MikrotikServices::getCustomerWifiClients($router, $pelanggan->usersecret);

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
            // WiFi scanning data
            'wifi_clients' => $wifiData['wifi_clients'] ?? 0,
            'wifi_status' => $wifiData['status'] ?? 'unknown',
            'wifi_method' => $wifiData['method'] ?? 'unknown',
            'wifi_message' => $wifiData['message'] ?? '',
            'customer_ip' => $wifiData['customer_ip'] ?? null,
            'wifi_devices' => $wifiData['devices'] ?? []
        ]);
    }

    /**
     * Get WiFi clients count for specific customer
     */
    public function getWifiClients($id)
    {
        try {
            $pelanggan = Customer::findOrFail($id);
            $router = Router::findOrFail($pelanggan->router_id);

            $result = MikrotikServices::getCustomerWifiClients($router, $pelanggan->usersecret);

            return response()->json([
                'success' => true,
                'customer_name' => $pelanggan->nama_customer,
                'customer_ip' => $result['customer_ip'] ?? null,
                'wifi_clients' => $result['wifi_clients'] ?? 0,
                'status' => $result['status'] ?? 'unknown',
                'method' => $result['method'] ?? 'unknown',
                'message' => $result['message'] ?? '',
                'devices' => $result['devices'] ?? []
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'wifi_clients' => 0
            ]);
        }
    }

    /**
     * Bulk scan WiFi clients for multiple customers
     */
    public function bulkWifiScan(Request $request)
    {
        try {
            $customerIds = $request->input('customer_ids', []);
            
            if (empty($customerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No customers selected'
                ]);
            }

            $results = [];
            
            foreach ($customerIds as $customerId) {
                $pelanggan = Customer::find($customerId);
                
                if (!$pelanggan || !$pelanggan->router_id) {
                    $results[] = [
                        'customer_id' => $customerId,
                        'customer_name' => $pelanggan->nama_customer ?? 'Unknown',
                        'wifi_clients' => 0,
                        'status' => 'error',
                        'message' => 'Customer or router not found'
                    ];
                    continue;
                }

                $router = Router::find($pelanggan->router_id);
                if (!$router) {
                    $results[] = [
                        'customer_id' => $customerId,
                        'customer_name' => $pelanggan->nama_customer,
                        'wifi_clients' => 0,
                        'status' => 'error',
                        'message' => 'Router not found'
                    ];
                    continue;
                }

                $wifiData = MikrotikServices::getCustomerWifiClients($router, $pelanggan->usersecret);
                
                $results[] = [
                    'customer_id' => $customerId,
                    'customer_name' => $pelanggan->nama_customer,
                    'customer_ip' => $wifiData['customer_ip'] ?? null,
                    'wifi_clients' => $wifiData['wifi_clients'] ?? 0,
                    'status' => $wifiData['status'] ?? 'unknown',
                    'method' => $wifiData['method'] ?? 'unknown',
                    'message' => $wifiData['message'] ?? ''
                ];
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'total_scanned' => count($results)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk scan error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get comprehensive network information for customer
     */
    public function getNetworkInfo($id)
    {
        try {
            $pelanggan = Customer::findOrFail($id);
            $router = Router::findOrFail($pelanggan->router_id);

            $networkInfo = MikrotikServices::getCustomerNetworkInfo($router, $pelanggan->usersecret);

            return response()->json([
                'success' => true,
                'customer_name' => $pelanggan->nama_customer,
                'network_info' => $networkInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting network info: ' . $e->getMessage()
            ]);
        }
    }
}