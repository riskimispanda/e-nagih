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

}
