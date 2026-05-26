<?php 

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Lokasi;
use App\Models\ODC;
use App\Models\ODP;
use App\Models\Customer;
use App\Models\Router;
use App\Services\MikrotikServices;

class MapController extends Controller
{
    public function index()
    {
        return view('map.map', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles
        ]);
    }

    private function parseGps($gps)
    {
        if (!$gps) return ['lat' => null, 'lng' => null];

        // Format: "-8.044889109411237, 110.4827779828878"
        if (preg_match('/^-?\d+\.\d+,\s*-?\d+\.\d+$/', $gps)) {
            [$lat, $lng] = explode(',', $gps);
            return ['lat' => trim($lat), 'lng' => trim($lng)];
        }

        // Format: "...?q=-8.04488,110.48277"
        if (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $gps, $matches)) {
            return ['lat' => $matches[1], 'lng' => $matches[2]];
        }

        // Format DMS: 7°58'42.7"S 110°24'31.8"E
        if (preg_match('/(\d+)°(\d+)\'([\d.]+)"([NS]),\s*(\d+)°(\d+)\'([\d.]+)"([EW])/', $gps, $m)) {
            $lat = $m[1] + $m[2] / 60 + $m[3] / 3600;
            if ($m[4] === 'S') $lat *= -1;

            $lng = $m[5] + $m[6] / 60 + $m[7] / 3600;
            if ($m[8] === 'W') $lng *= -1;

            return ['lat' => $lat, 'lng' => $lng];
        }

        // Format tidak dikenali
        return ['lat' => null, 'lng' => null];
    }


    public function data()
    {
        $type = request('type');

        // Fetch all active customers to determine online/offline status
        $customers = Customer::whereNull('deleted_at')->with('paket')->get();

        // Get unique router IDs associated with these customers to fetch active sessions
        $routerIds = $customers->pluck('router_id')->filter()->unique();
        $activeSecretsByRouter = [];

        foreach ($routerIds as $routerId) {
            try {
                $router = Router::find($routerId);
                if ($router) {
                    $client = MikrotikServices::connect($router);
                    $query = new \RouterOS\Query('/ppp/active/print');
                    $activeUsers = $client->query($query)->read();
                    $activeSecretsByRouter[$routerId] = array_map('strtolower', array_column($activeUsers, 'name'));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("MapController: Gagal konek ke router ID {$routerId}: " . $e->getMessage());
                $activeSecretsByRouter[$routerId] = [];
            }
        }

        // 1. Process Customers and determine their online status
        $customerStatuses = []; // customer_id => status
        $customerData = $customers->map(function ($item) use ($activeSecretsByRouter, &$customerStatuses) {
            $coord = $this->parseGps($item->gps);
            
            $isOnline = false;
            if ($item->router_id && isset($activeSecretsByRouter[$item->router_id])) {
                $isOnline = in_array(strtolower($item->usersecret), $activeSecretsByRouter[$item->router_id]);
            }
            
            $status = $isOnline ? 'online' : 'offline';
            $customerStatuses[$item->id] = $status;
            
            return [
                'id' => $item->id,
                'nama' => $item->nama_customer,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'customer',
                'odp_id' => $item->lokasi_id,
                'status' => $status,
                'details' => [
                    'package' => $item->paket->paket_name ?? 'N/A',
                    'phone' => $item->no_hp ?? 'N/A',
                    'address' => $item->alamat ?? 'N/A',
                    'redaman' => $item->redaman ?? 'N/A',
                    'usersecret' => $item->usersecret ?? 'N/A',
                ]
            ];
        });

        // 2. Process ODPs
        $odpRaw = ODP::all();
        $odpData = $odpRaw->map(function ($item) use ($customerData) {
            $coord = $this->parseGps($item->gps);
            
            // Find customers connected to this ODP
            $myCustomers = $customerData->where('odp_id', $item->id);
            
            if ($myCustomers->isEmpty()) {
                $status = 'empty';
            } else {
                $totalOnline = $myCustomers->where('status', 'online')->count();
                $totalOffline = $myCustomers->where('status', 'offline')->count();
                
                if ($totalOnline > 0 && $totalOffline > 0) {
                    $status = 'warning'; // Some online, some offline
                } elseif ($totalOnline > 0) {
                    $status = 'online';
                } else {
                    $status = 'offline';
                }
            }
            
            return [
                'id' => $item->id,
                'nama' => $item->nama_odp,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'odp',
                'odc_id' => $item->odc_id,
                'status' => $status,
                'details' => [
                    'total_customers' => $myCustomers->count(),
                    'online_customers' => $myCustomers->where('status', 'online')->count()
                ]
            ];
        });

        // 3. Process ODCs
        $odcRaw = ODC::all();
        $odcData = $odcRaw->map(function ($item) use ($odpData) {
            $coord = $this->parseGps($item->gps);
            
            // Find ODPs connected to this ODC
            $myOdps = $odpData->where('odc_id', $item->id);
            $totalOnline = 0;
            
            if ($myOdps->isEmpty()) {
                $status = 'empty';
            } else {
                $totalOnline = $myOdps->whereIn('status', ['online', 'warning'])->count();
                $totalOffline = $myOdps->where('status', 'offline')->count();
                
                if ($totalOnline > 0 && $totalOffline > 0) {
                    $status = 'warning';
                } elseif ($totalOnline > 0) {
                    $status = 'online';
                } else {
                    $status = 'offline';
                }
            }
            
            return [
                'id' => $item->id,
                'nama' => $item->nama_odc,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'odc',
                'olt_id' => $item->lokasi_id,
                'status' => $status,
                'details' => [
                    'total_odps' => $myOdps->count(),
                    'online_odps' => $totalOnline
                ]
            ];
        });

        // 4. Process OLTs (Lokasi)
        $oltRaw = Lokasi::all();
        $oltData = $oltRaw->map(function ($item) use ($odcData) {
            $coord = $this->parseGps($item->gps);
            
            // Find ODCs connected to this OLT
            $myOdcs = $odcData->where('olt_id', $item->id);
            $totalOnline = 0;
            
            if ($myOdcs->isEmpty()) {
                $status = 'empty';
            } else {
                $totalOnline = $myOdcs->whereIn('status', ['online', 'warning'])->count();
                $totalOffline = $myOdcs->where('status', 'offline')->count();
                
                if ($totalOnline > 0 && $totalOffline > 0) {
                    $status = 'warning';
                } elseif ($totalOnline > 0) {
                    $status = 'online';
                } else {
                    $status = 'offline';
                }
            }
            
            return [
                'id' => $item->id,
                'nama' => $item->nama_lokasi,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'olt',
                'server_id' => $item->id_server,
                'status' => $status,
                'details' => [
                    'total_odcs' => $myOdcs->count(),
                    'online_odcs' => $totalOnline
                ]
            ];
        });

        // 5. Process Servers (BTS)
        $serverRaw = Server::all();
        $serverData = $serverRaw->map(function ($item) use ($oltData) {
            $coord = $this->parseGps($item->gps);
            
            // Find OLTs connected to this Server
            $myOlts = $oltData->where('server_id', $item->id);
            $totalOnline = 0;
            
            if ($myOlts->isEmpty()) {
                $status = 'empty';
            } else {
                $totalOnline = $myOlts->whereIn('status', ['online', 'warning'])->count();
                $totalOffline = $myOlts->where('status', 'offline')->count();
                
                if ($totalOnline > 0 && $totalOffline > 0) {
                    $status = 'warning';
                } elseif ($totalOnline > 0) {
                    $status = 'online';
                } else {
                    $status = 'offline';
                }
            }
            
            return [
                'id' => $item->id,
                'nama' => $item->lokasi_server,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'server',
                'status' => $status,
                'details' => [
                    'ip_address' => $item->ip_address,
                    'total_olts' => $myOlts->count(),
                    'online_olts' => $totalOnline
                ]
            ];
        });

        // Combine all data
        $allData = collect();
        
        if (!$type || $type === 'server') {
            $allData = $allData->merge($serverData);
        }
        if (!$type || $type === 'olt') {
            $allData = $allData->merge($oltData);
        }
        if (!$type || $type === 'odc') {
            $allData = $allData->merge($odcData);
        }
        if (!$type || $type === 'odp') {
            $allData = $allData->merge($odpData);
        }
        if (!$type || $type === 'customer') {
            $allData = $allData->merge($customerData);
        }

        return response()->json(
            $allData->filter(fn($item) => $item['lat'] && $item['lng'])
                ->values()
        );
    }

    public function debugGps()
    {
        $servers = Server::all()->map(fn($item) => ['jenis' => 'server', 'id' => $item->id, 'nama' => $item->lokasi_server, 'gps_raw' => $item->gps, 'parsed' => $this->parseGps($item->gps)]);
        $olts = Lokasi::all()->map(fn($item) => ['jenis' => 'olt', 'id' => $item->id, 'nama' => $item->nama_lokasi, 'gps_raw' => $item->gps, 'parsed' => $this->parseGps($item->gps)]);
        $odcs = ODC::all()->map(fn($item) => ['jenis' => 'odc', 'id' => $item->id, 'nama' => $item->nama_odc, 'gps_raw' => $item->gps, 'parsed' => $this->parseGps($item->gps)]);
        $odps = ODP::all()->map(fn($item) => ['jenis' => 'odp', 'id' => $item->id, 'nama' => $item->nama_odp, 'gps_raw' => $item->gps, 'parsed' => $this->parseGps($item->gps)]);
        
        return response()->json([
            'total_servers' => $servers->count(),
            'total_olts' => $olts->count(),
            'total_odcs' => $odcs->count(),
            'total_odps' => $odps->count(),
            'servers' => $servers,
            'olts' => $olts,
            'odcs' => $odcs,
            'odps' => $odps
        ]);
    }
}
