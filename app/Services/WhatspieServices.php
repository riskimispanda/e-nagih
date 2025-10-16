<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatspieServices
{
    protected $baseUrl;
    protected $apiKey;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('WHATSPIE_BASE_URL', 'https://api.whatspie.com'), '/');
        $this->apiKey = env('WHATSPIE_API_KEY');
        $this->timeout = 30;
    }

    /**
     * GET /devices - dengan format data yang lebih baik untuk dropdown
     */
    public function getDevices()
    {
        try {
            $url = $this->baseUrl . '/devices';

            Log::info("WhatsPie GET devices: {$url}");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout($this->timeout)
              ->get($url);

            Log::info("WhatsPie GET devices status: " . $response->status());

            $body = $response->json();

            if ($response->successful()) {
                $devices = $body['data'] ?? [];
                
                // Format data untuk dropdown
                $formattedDevices = [];
                if (is_array($devices)) {
                    foreach ($devices as $device) {
                        $deviceId = $device['device_id'] ?? $device['id'] ?? null;
                        $deviceName = $device['name'] ?? $device['device_name'] ?? 'Unknown Device';
                        $devicePhone = $device['phone'] ?? $device['number'] ?? $deviceId;
                        $deviceStatus = $device['status'] ?? $device['connection_status'] ?? 'unknown';

                        if ($devicePhone) {
                            $formattedDevices[] = [
                                'value' => $deviceId, // Nomor telepon sebagai value
                                'text' => $deviceName . ' - ' . $this->formatPhoneForDisplay($devicePhone),
                                'status' => $deviceStatus,
                                'name' => $deviceName,
                                'phone' => $devicePhone,
                                'raw' => $device
                            ];
                        }
                    }
                }

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $formattedDevices,
                    'raw' => $body,
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error',
                'body' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsPieService getDevices Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getDevicesPhone()
    {
        try {
            $url = $this->baseUrl . '/devices';

            Log::info("WhatsPie GET devices: {$url}");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout($this->timeout)
                ->get($url);

            Log::info("WhatsPie GET devices status: " . $response->status());

            $body = $response->json();

            if ($response->successful()) {
                $devices = $body['data'] ?? [];

                // Format data untuk dropdown
                $formattedDevices = [];
                if (is_array($devices)) {
                    foreach ($devices as $device) {
                        $deviceId = $device['device_id'] ?? $device['id'] ?? null;
                        $deviceName = $device['name'] ?? $device['device_name'] ?? 'Unknown Device';
                        $devicePhone = $device['phone'] ?? $device['number'] ?? $deviceId;
                        $deviceStatus = $device['status'] ?? $device['connection_status'] ?? 'unknown';

                        if ($devicePhone) {
                            $formattedDevices[] = [
                                'value' => $devicePhone, // Nomor telepon sebagai value
                                'text' => $deviceName . ' - ' . $this->formatPhoneForDisplay($devicePhone),
                                'status' => $deviceStatus,
                                'name' => $deviceName,
                                'phone' => $devicePhone,
                                'raw' => $device
                            ];
                        }
                    }
                }

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $formattedDevices,
                    'raw' => $body,
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error',
                'body' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsPieService getDevices Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getPhoneNumber()
    {
        try {
            $devicesResult = $this->getDevices();

            if ($devicesResult['success'] && !empty($devicesResult['data'])) {
                $phones = [];

                foreach ($devicesResult['data'] as $device) {
                    if (isset($device['phone']) && !empty($device['phone'])) {
                        $phones[] = $device['phone']; // Langsung push string phone
                    }
                }

                return [
                    'success' => true,
                    'phones' => $phones, // Sekarang berisi array of strings
                    'total' => count($phones)
                ];
            }

            return [
                'success' => false,
                'error' => $devicesResult['error'] ?? 'No devices found',
                'phones' => []
            ];
        } catch (Exception $e) {
            Log::error('WhatsPieService getPhoneNumber Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'phones' => []
            ];
        }
    }

    /**
     * GET /devices/{phone} - Device info berdasarkan nomor
     */
    public function getDeviceInfo($phoneNumber)
    {
        try {
            $url = $this->baseUrl . '/devices/' . $this->formatPhone($phoneNumber);

            Log::info("WhatsPie GET device info: {$url}");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout($this->timeout)
              ->get($url);

            Log::info("WhatsPie GET device info status: " . $response->status());

            $body = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $body['data'] ?? $body,
                    'raw' => $body,
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error',
                'body' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsPieService getDeviceInfo Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * POST /messages - Format yang benar dengan device sebagai nomor
     */
    public function sendMessage($receiver, $message, $devicePhone, $simulate = false)
    {
        try {
            $url = $this->baseUrl . '/messages';

            // Format payload yang benar - device adalah nomor telepon
            $payload = [
                'device' => $this->formatPhone($devicePhone),    // Device sebagai nomor telepon
                'receiver' => $this->formatPhone($receiver),     // Nomor penerima
                'type' => 'chat',                               // Tipe pesan
                'params' => [                                   // Parameter pesan
                    'text' => $message                          // Isi pesan
                ],
                'simulate_typing' => $simulate ? 1 : 0          // Simulate typing
            ];

            Log::info("WhatsPie sendMessage URL: " . $url);
            Log::info("WhatsPie sendMessage payload: ", $payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout)
              ->post($url, $payload);

            Log::info("WhatsPie sendMessage status: " . $response->status());
            Log::info("WhatsPie sendMessage body: " . $response->body());

            $body = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $body,
                    'message_id' => $body['data']['id'] ?? $body['id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error',
                'body' => $response->body(),
                'payload_sent' => $payload,
            ];
        } catch (Exception $e) {
            Log::error('WhatsPieService sendMessage Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format nomor telepon ke standar internasional (62) untuk API
     */
    private function formatPhone($phone)
    {
        // hapus non-digit
        $digits = preg_replace('/\D/', '', $phone);
        
        // jika diawali 0
        if (strlen($digits) > 0 && $digits[0] === '0') {
            $digits = '62' . substr($digits, 1);
        }
        
        // jika diawali 62, pastikan tidak ada +
        if (strlen($digits) > 0 && strpos($digits, '62') === 0) {
            return $digits;
        }
        
        return $digits;
    }

    /**
     * Format nomor telepon untuk display (62xxx -> 08xxx)
     */
    private function formatPhoneForDisplay($phone)
    {
        $digits = preg_replace('/\D/', '', $phone);
        
        // jika diawali 62, ubah ke 0
        if (strlen($digits) > 0 && strpos($digits, '62') === 0) {
            return '0' . substr($digits, 2);
        }
        
        return $phone;
    }

    /**
     * Check connection dan dapatkan devices yang tersedia
     */
    public function checkConnection()
    {
        return $this->getDevices();
    }

    /**
     * POST /devices - Tambah device baru di Whatspie
     */
    public function addNewDevice($customDeviceName = null, $package = 'STARTUP60K')
    {
        try {
            $url = $this->baseUrl . '/devices';

            // Generate device name jika tidak ada custom name
            $deviceName = $customDeviceName ?: 'device_' . uniqid() . '_' . time();
            
            // ✅ PERBAIKI: Gunakan format yang sesuai dokumentasi
            $payload = [
                'package' => $package,      // ✅ Package name
                'name' => $deviceName       // ✅ Device name
            ];

            Log::info("WhatsPie addNewDevice URL: " . $url);
            Log::info("WhatsPie addNewDevice payload: ", $payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout)
            ->post($url, $payload);

            Log::info("WhatsPie addNewDevice status: " . $response->status());
            Log::info("WhatsPie addNewDevice body: " . $response->body());

            $body = $response->json();

            if ($response->successful()) {
                $responseData = $body['data'] ?? $body;
                
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => 'Device berhasil ditambahkan',
                    'data' => [
                        'device_name' => $deviceName,
                        'device_id' => $responseData['id'] ?? null,
                        'qr_code' => $responseData['qr'] ?? null,
                        'status' => $responseData['status'] ?? 'pending',
                        'phone_number' => $responseData['phone'] ?? null,
                        'package' => $package,
                        'raw_response' => $responseData
                    ],
                    'raw' => $body
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error',
                'body' => $response->body(),
                'payload_sent' => $payload,
            ];

        } catch (Exception $e) {
            Log::error('WhatsPieService addNewDevice Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * DELETE /devices/{device} - Hapus device
     */
    public function deleteDevice($deviceIdentifier)
    {
        try {
            $url = $this->baseUrl . '/devices/' . $deviceIdentifier;

            Log::info("WhatsPie deleteDevice URL: " . $url);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout($this->timeout)
            ->delete($url);

            Log::info("WhatsPie deleteDevice status: " . $response->status());
            Log::info("WhatsPie deleteDevice body: " . $response->body());

            $body = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => 'Device berhasil dihapus',
                    'data' => $body['data'] ?? $body,
                    'raw' => $body
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error',
                'body' => $response->body(),
            ];

        } catch (Exception $e) {
            Log::error('WhatsPieService deleteDevice Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * POST /devices/{device}/restart - Restart device
     */
    public function restartDevice($deviceIdentifier)
    {
        try {
            $url = $this->baseUrl . '/devices/' . $deviceIdentifier . '/restart';

            Log::info("WhatsPie restartDevice URL: " . $url);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout($this->timeout)
            ->post($url);

            Log::info("WhatsPie restartDevice status: " . $response->status());
            Log::info("WhatsPie restartDevice body: " . $response->body());

            $body = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => 'Device berhasil di-restart',
                    'data' => $body['data'] ?? $body,
                    'raw' => $body
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error',
                'body' => $response->body(),
            ];

        } catch (Exception $e) {
            Log::error('WhatsPieService restartDevice Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * GET /devices/{device}/qr - Dapatkan QR code device
     */
    public function getDeviceQr($deviceIdentifier)
    {
        try {
            // Pastikan device identifier adalah angka/numeric
            if (!is_numeric($deviceIdentifier)) {
                return [
                    'success' => false,
                    'status' => 400,
                    'error' => 'Device identifier must be numeric',
                ];
            }

            // Tambahkan parameter response_type seperti di dokumentasi
            $url = $this->baseUrl . '/devices/' . $deviceIdentifier . '/qr?response_type=url';

            Log::info("WhatsPie getDeviceQr URL: " . $url);
            Log::info("WhatsPie API Key: " . substr($this->apiKey, 0, 10) . '...'); // Log partial API key untuk debug

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json; charset=utf-8', // Sesuai dokumentasi
                'Content-Type' => 'application/json', // Tambahkan ini
            ])->timeout($this->timeout)
                ->get($url);

            Log::info("WhatsPie getDeviceQr status: " . $response->status());
            Log::info("WhatsPie getDeviceQr headers: " . json_encode($response->headers()));
            Log::info("WhatsPie getDeviceQr body: " . $response->body());

            $body = $response->json();

            // Handle 400 error specifically
            if ($response->status() === 400) {
                return [
                    'success' => false,
                    'status' => 400,
                    'error' => $body['message'] ?? 'Invalid device ID',
                    'device_id' => $deviceIdentifier,
                    'suggestion' => 'Device mungkin belum terdaftar atau sudah expired'
                ];
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $body['data'] ?? $body,
                    'qr_code' => $body['data']['qr'] ?? $body['qr'] ?? $body['data']['qrcode'] ?? null,
                    'qr_url' => $body['data']['url'] ?? $body['url'] ?? null,
                    'raw' => $body
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $body['message'] ?? 'Unknown error: ' . $response->body(),
                'body' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsPieService getDeviceQr Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

}