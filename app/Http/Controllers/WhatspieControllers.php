<?php

namespace App\Http\Controllers;

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

            return view('whatspie.dashboard-whatspie',[
                'users' => auth()->user(),
                'roles' => auth()->user()->roles,
                'devices' => $devices
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
}