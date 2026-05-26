<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Router;
use App\Models\Koneksi;
use App\Models\Customer;
use App\Models\User;
use App\Services\MikrotikServices;
use App\Services\ChatServices;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class MikrotikControllerApi extends Controller
{
    public function getNetwork()
    {
      $router = Router::all();
      $koneksi = Koneksi::all();

      $getRouter = $router->map(function($r){
        return [
          'id' => $r->id,
          'name' => $r->nama_router
        ];
      });

      $getKoneksi = $koneksi->map(function($k){
        return [
          'id' => $k->id,
          'name' => $k->nama_koneksi
        ];
      });

      return response()->json([
        'success' => true,
        'data' => [
          'router' => $getRouter,
          'koneksi' => $getKoneksi
        ],
      ]);

    }

    public function postAssign(Request $request, $id)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'router_id' => 'required|exists:router,id',
                'koneksi_id' => 'required|exists:koneksi,id',
                'usersecret' => 'required|string',
                'password' => 'required|string',
                'remote_address' => 'required|string',
                'local_address' => 'required|string',
                'remote' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cari customer
            $customer = Customer::findOrFail($id);

            // Cari router
            $router = Router::findOrFail($request->router_id);

            // Ambil nama paket dari customer
            $paket = $customer->paket->paket_name;

            // Cari koneksi
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
            $chat = new ChatServices();
            foreach ($teknisi as $tek) {
                $nomor = preg_replace('/[^0-9]/', '', $tek->no_hp);
                if (str_starts_with($nomor, '0')) {
                    $nomor = '62' . substr($nomor, 1);
                }

                $chat->kirimNotifikasiTeknisi($nomor, $tek);
            }

            // ? Catat Log
            activity('Dial Customer')
                ->causedBy(auth()->user())
                ->log(auth()->user()->name . ' Membuat dial untuk pelanggan ' . $customer->nama_customer);

            return response()->json([
                'success' => true,
                'message' => 'Antrian assigned successfully',
                'data' => [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->nama_customer,
                    'usersecret' => $customer->usersecret,
                    'router' => $router->nama_router,
                    'koneksi' => $koneksi->nama_koneksi,
                    'status_id' => $customer->status_id
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'error' => $e->getMessage()
            ], 404);

        } catch (Exception $e) {
            Log::error('Error in postAssign: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkVersion($id)
    {
        try {
            $router = Router::findOrFail($id);
            $client = MikrotikServices::connect($router);

            $query = new \RouterOS\Query('/system/resource/print');
            $response = $client->query($query)->read();

            $version = $response[0]['version'] ?? 'Unknown';

            return response()->json([
                'success' => true,
                'router_name' => $router->nama_router,
                'ip_address' => $router->ip_address,
                'version' => $version,
                'details' => [
                    'uptime' => $response[0]['uptime'] ?? null,
                    'cpu_load' => $response[0]['cpu-load'] ?? null,
                    'free_memory' => $response[0]['free-memory'] ?? null,
                    'total_memory' => $response[0]['total-memory'] ?? null,
                    'cpu' => $response[0]['cpu'] ?? null,
                    'board_name' => $response[0]['board-name'] ?? null,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in checkVersion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve router version',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getNeighbors($id)
    {
        try {
            $router = Router::findOrFail($id);
            $client = MikrotikServices::connect($router);

            $query = new \RouterOS\Query('/ip/neighbor/print');
            $response = $client->query($query)->read();

            // Map response to a clean structure and check for matching customer database info
            $neighbors = array_map(function ($item) {
                $localInterface = $item['interface'] ?? '';
                $usersecret = null;

                if (str_starts_with($localInterface, '<pppoe-') && str_ends_with($localInterface, '>')) {
                    $usersecret = substr($localInterface, 7, -1);
                } elseif (str_starts_with($localInterface, 'pppoe-')) {
                    $usersecret = substr($localInterface, 6);
                }

                $customerInfo = null;
                if ($usersecret) {
                    $customer = Customer::with(['paket', 'odp.odc.olt.server'])
                        ->whereRaw('LOWER(usersecret) = ?', [strtolower($usersecret)])
                        ->first();

                    if ($customer) {
                        $customerInfo = [
                            'id' => $customer->id,
                            'name' => $customer->nama_customer,
                            'phone' => $customer->no_hp,
                            'address' => $customer->alamat,
                            'package' => $customer->paket->paket_name ?? 'N/A',
                            'redaman' => $customer->redaman ?? 'N/A',
                            'olt' => $customer->odp?->odc?->olt?->nama_lokasi ?? 'N/A',
                            'odc' => $customer->odp?->odc?->nama_odc ?? 'N/A',
                            'odp' => $customer->odp?->nama_odp ?? 'N/A',
                        ];
                    }
                }

                return [
                    'local_interface' => $localInterface,
                    'neighbor_identity' => $item['identity'] ?? 'Unknown',
                    'ip_address' => $item['address'] ?? 'N/A',
                    'mac_address' => $item['mac-address'] ?? 'N/A',
                    'platform' => $item['platform'] ?? 'Unknown',
                    'board' => $item['board'] ?? null,
                    'version' => $item['version'] ?? null,
                    'uptime' => $item['uptime'] ?? null,
                    'is_customer' => $customerInfo !== null,
                    'customer_info' => $customerInfo
                ];
            }, $response);

            return response()->json([
                'success' => true,
                'router_name' => $router->nama_router,
                'ip_address' => $router->ip_address,
                'total_neighbors' => count($neighbors),
                'data' => $neighbors
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in getNeighbors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve neighbors (LLDP/MNDP)',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
