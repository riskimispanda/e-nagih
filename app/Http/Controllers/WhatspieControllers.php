<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ODC;
use App\Models\ODP;
use App\Models\Server;

use App\Models\Lokasi;

use App\Services\WhatspieServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsPieControllers extends Controller
{
    protected $whatspie;

    public function __construct(WhatspieServices $whatspie)
    {
        $this->whatspie = $whatspie;
    }

    /**
     * Menampilkan dashboard WhatsPie dengan data devices untuk dropdown
     */
    public function dashboard(Request $request)
    {
        try {
            $action = $request->get('action');
            
            if ($action === 'devices') {
                return $this->handleGetDevices();
            } elseif ($action === 'device-info') {
                return $this->handleGetDeviceInfo($request->get('device_phone'));
            } elseif ($action === 'test') {
                return $this->handleTestConnection();
            }

            // Default: tampilkan view dashboard dengan devices
            $devicesResponse = $this->whatspie->getDevices();
            $devices = $devicesResponse['success'] ? $devicesResponse['data'] : [];
            $servers = Server::all(); // Ambil semua data Server (BTS)

            return view('whatspie.dashboard-whatspie',[
                'users' => auth()->user(),
                'roles' => auth()->user()->roles,
                'devices' => $devices,
                'servers' => $servers, // Kirim data Server ke view
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsPie Dashboard Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    // ... method yang sudah ada ...

    /**
     * ========== DEVICE MANAGEMENT METHODS ==========
     */

    /**
     * Tambah device baru
     */
    public function addDevice(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',      // Device name (optional)
            'package' => 'required|string'             // Package name
        ]);

        $deviceName = $validated['name'] ?? null;
        $package = $validated['package'];

        $result = $this->whatspie->addNewDevice($deviceName, $package);

        return response()->json($result);
    }

    /**
     * Hapus device
     */
    public function deleteDevice(Request $request, $deviceIdentifier)
    {
        try {
            $result = $this->whatspie->deleteDevice($deviceIdentifier);

            if ($result['success']) {
                Log::info('Device berhasil dihapus: ' . $deviceIdentifier);
            } else {
                Log::error('Gagal menghapus device: ' . ($result['error'] ?? 'Unknown error'));
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Delete device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 500,
                'error' => 'Failed to delete device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart device
     */
    public function restartDevice(Request $request, $deviceIdentifier)
    {
        try {
            $result = $this->whatspie->restartDevice($deviceIdentifier);

            if ($result['success']) {
                Log::info('Device berhasil di-restart: ' . $deviceIdentifier);
            } else {
                Log::error('Gagal restart device: ' . ($result['error'] ?? 'Unknown error'));
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Restart device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 500,
                'error' => 'Failed to restart device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dapatkan QR code device
     */
    public function getDeviceQr(Request $request, $deviceIdentifier)
    {
        try {
            $result = $this->whatspie->getDeviceQr($deviceIdentifier);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::info('Get device QR error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 500,
                'error' => 'Failed to get device QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test connection
     */
    public function testConnection(Request $request)
    {
        try {
            $result = $this->whatspie->checkConnection();
            
            return response()->json([
                'success' => $result['success'],
                'status' => $result['status'],
                'message' => $result['success'] ? 'Connection successful' : 'Connection failed',
                'error' => $result['error'] ?? null,
                'devices_count' => $result['success'] ? count($result['data']) : 0,
                'data' => $result['success'] ? $result['data'] : null
            ]);

        } catch (\Exception $e) {
            Log::error('Test connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 500,
                'error' => 'Failed to test connection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ========== API METHODS (JSON RESPONSE) ==========
     */

    /**
     * API - Tambah device baru
     */
    public function apiAddDevice(Request $request)
    {
        return $this->addDevice($request);
    }

    /**
     * API - Hapus device
     */
    public function apiDeleteDevice(Request $request, $deviceIdentifier)
    {
        return $this->deleteDevice($request, $deviceIdentifier);
    }

    /**
     * API - Restart device
     */
    public function apiRestartDevice(Request $request, $deviceIdentifier)
    {
        return $this->restartDevice($request, $deviceIdentifier);
    }

    /**
     * API - Dapatkan QR code device
     */
    public function apiGetDeviceQr(Request $request, $deviceIdentifier)
    {
        return $this->getDeviceQr($request, $deviceIdentifier);
    }

    /**
     * API - Test connection
     */
    public function apiTestConnection(Request $request)
    {
        return $this->testConnection($request);
    }

    /**
     * API - Send message
     */
    public function apiSendMessage(Request $request)
    {
        return $this->sendMessage($request);
    }

    /**
     * Handle get devices request untuk AJAX
     */
    private function handleGetDevices()
    {
        $response = $this->whatspie->getDevices();
        return response()->json($response);
    }

    /**
     * Handle get device info request - sekarang berdasarkan nomor
     */
    private function handleGetDeviceInfo($devicePhone)
    {
        if (!$devicePhone) {
            return response()->json([
                'success' => false,
                'error' => 'Device phone number is required',
                'status' => 400
            ], 400);
        }

        $response = $this->whatspie->getDeviceInfo($devicePhone);
        return response()->json($response);
    }

    /**
     * Handle test connection request
     */
    private function handleTestConnection()
    {
        $response = $this->whatspie->checkConnection();
        
        return response()->json([
            'success' => $response['success'],
            'status' => $response['status'],
            'error' => $response['error'] ?? null,
            'devices_count' => $response['success'] ? count($response['data']) : 0,
            'data' => $response['success'] ? ['message' => 'API Connection successful'] : null
        ]);
    }

    /**
     * Mengirim pesan - device_id sekarang dari dropdown
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',           // Nomor penerima
                'message' => 'required|string|max:1000', // Pesan
                'device_id' => 'required|string',       // Device dari dropdown
                'simulate' => 'sometimes|boolean'       // Simulate typing
            ]);

            Log::info('Send message request:', $request->all());

            $response = $this->whatspie->sendMessage(
                $request->phone,        // receiver
                $request->message,      // message
                $request->device_id,    // device (dari dropdown)
                $request->get('simulate', false)
            );

            Log::info('Send message response:', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Send message error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 500,
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint untuk mendapatkan devices (JSON) untuk AJAX
     */
    public function apiGetDevices()
    {
        $response = $this->whatspie->getDevices();
        return response()->json($response);
    }

    /**
     * API - Kirim pesan maintenance ke customer berdasarkan OLT
     */
    public function sendMaintenanceMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'odp_id' => 'required|exists:odp,id',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $odpId = $request->odp_id;
            $message = $request->message . "\n\n💻 Pesan ini dikirim secara otomatis oleh sistem NBilling.";

            // Panggil service untuk mengirim pesan
            $result = $this->whatspie->sendBulkMaintenanceMessage($odpId, $message);

            if ($result['success']) {
                Log::info('Pesan maintenance berhasil dikirim untuk ODP ID: ' . $odpId, [
                    'sent' => $result['sent_count'],
                    'failed' => $result['failed_count']
                ]);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error sending maintenance message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 500,
                'error' => 'Failed to send maintenance messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API - Dapatkan jumlah customer berdasarkan ODP
     */
    public function getCustomerCountByOdp($odpId)
    {
        try {
            // Validasi sederhana, pastikan odpId adalah numerik
            if (!is_numeric($odpId)) {
                return response()->json(['success' => false, 'error' => 'Invalid ODP ID'], 400);
            }

            $customerCount = Customer::where('lokasi_id', $odpId) // Menggunakan kolom odp_id yang benar
                ->whereNotNull('no_hp')
                ->whereIn('status_id', [3, 4, 9]) // Hanya customer aktif atau terblokir
                ->count();

            return response()->json([
                'success' => true,
                'count' => $customerCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting customer count by OLT: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get customer count.'
            ], 500);
        }
    }

    /**
     * API - Dapatkan OLT berdasarkan Server (BTS)
     */
    public function getOltsByServer($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                return response()->json(['success' => false, 'error' => 'Invalid Server ID'], 400);
            }

            $olts = Lokasi::where('id_server', $serverId)->get(['id', 'nama_lokasi']);

            return response()->json([
                'success' => true,
                'data' => $olts
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting OLTs by server: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get OLTs.'
            ], 500);
        }
    }

    /**
     * API - Dapatkan ODC berdasarkan OLT
     */
    public function getOdcsByOlt($oltId)
    {
        try {
            if (!is_numeric($oltId)) {
                return response()->json(['success' => false, 'error' => 'Invalid OLT ID'], 400);
            }

            $odcs = ODC::where('lokasi_id', $oltId)->get(['id', 'nama_odc']);

            return response()->json([
                'success' => true,
                'data' => $odcs
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting ODCs by OLT: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to get ODCs.'], 500);
        }
    }

    /**
     * API - Dapatkan ODP berdasarkan ODC
     */
    public function getOdpsByOdc($odcId)
    {
        try {
            if (!is_numeric($odcId)) {
                return response()->json(['success' => false, 'error' => 'Invalid ODC ID'], 400);
            }

            $odps = ODP::where('odc_id', $odcId)->get(['id', 'nama_odp']);

            return response()->json([
                'success' => true,
                'data' => $odps
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting ODPs by ODC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get ODPs.'
            ], 500);
        }
    }
}