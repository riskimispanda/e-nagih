@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard Whatspie')

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .cursor-pointer {
        cursor: pointer;
    }
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-size: 0.8em;
    }
    
    /* Custom animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Progress bar styles */
    .progress-bar {
        height: 6px;
        background-color: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .progress-low {
        background-color: #10b981; /* green */
    }
    
    .progress-medium {
        background-color: #f59e0b; /* yellow */
    }
    
    .progress-high {
        background-color: #ef4444; /* red */
    }
</style>

@section('content')
<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- Modal Container -->
<div id="modalContainer"></div>

<div class="row">
    <div class="col-sm-12">
        <!-- Header -->
        <div class="mb-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-dark rounded-xl flex items-center justify-center mr-4">
                                <i class="fab fa-whatsapp text-green-400 text-3xl"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Dashboard Customer Service</h1>
                                <p class="text-gray-600 mt-1">Manage WhatsApp Devices and Qr Code</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="loadDevices()" class="btn-sm inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh Devices
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Connection Status -->
            <div class="lg:col-span-1">
                <!-- Connection Status Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-plug text-blue-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Connection Status</h3>
                    </div>

                    <!-- API Status -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Configuration</label>
                        <div class="flex items-center">
                            @if(env('WHATSPIE_API_KEY') && env('WHATSPIE_BASE_URL'))
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Configured
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 text-sm font-medium">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    Not Configured
                                </span>
                            @endif
                        </div>
                        @if(!env('WHATSPIE_API_KEY') || !env('WHATSPIE_BASE_URL'))
                            <p class="text-red-600 text-sm mt-2">
                                Please configure WHATSPIE_BASE_URL and WHATSPIE_API_KEY in .env file
                            </p>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Devices Summary</label>
                        <div id="devicesSummary" class="text-sm">
                            @if(count($devices) > 0)
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                        {{ count($devices) }} Loaded
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-500">Devices will load automatically</span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button onclick="loadDevices()" class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-sync-alt mr-3"></i>
                            Reload Devices
                        </button>
                        <button onclick="testConnection()" class="w-full flex items-center justify-center px-4 py-3 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 font-medium">
                            <i class="fas fa-wifi mr-3"></i>
                            Test Connection
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Devices Table & Results -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Devices Table Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-mobile-alt text-green-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">WhatsApp Devices</h3>
                            </div>
                            <div class="text-sm text-gray-500">
                                <span id="devicesCount">0 devices</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Loading Indicator -->
                        <div id="loadingDevices" class="hidden">
                            <div class="flex items-center justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Loading Devices</h4>
                                    <p class="text-gray-500 text-sm">Please wait...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Devices Table -->
                        <div id="devicesTableContainer">
                            @if(count($devices) > 0)
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Device</th>
                                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Quota Used</th>
                                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Package</th>
                                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($devices as $device)
                                                @php
                                                    $deviceData = $device['raw'] ?? $device;
                                                    $deviceId = $deviceData['id'] ?? $device['value'] ?? 'N/A';
                                                    $deviceName = $deviceData['device_name_mobile'] ?? 'Unknown Device';
                                                    $status = $deviceData['paired_status'] ?? 'UNPAIRED';
                                                    $isActive = ($deviceData['status'] ?? '') === 'ACTIVE';
                                                    $package = $deviceData['package']['name'] ?? 'N/A';
                                                    $isConnected = $status === 'PAIRED';
                                                    
                                                    // Quota information
                                                    $quoteUsed = $deviceData['subscription']['quote_used'] ?? 0;
                                                    $quoteAvailable = $deviceData['subscription']['quote_available'] ?? 0;
                                                    $quotaPercentage = $quoteAvailable > 0 ? ($quoteUsed / $quoteAvailable) * 100 : 0;
                                                    $quotaColor = $quotaPercentage < 50 ? 'progress-low' : ($quotaPercentage < 80 ? 'progress-medium' : 'progress-high');
                                                @endphp
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                                <i class="fas fa-mobile-alt text-blue-600 text-sm"></i>
                                                            </div>
                                                            <div>
                                                                <div class="text-sm font-medium text-gray-900">{{ $deviceName }}</div>
                                                                <div class="text-xs text-gray-500">ID: {{ $deviceId }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-col space-y-1">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $isConnected ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                {{ $isConnected ? '✅ Connected' : '❌ Not Connected' }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-col space-y-2">
                                                            <div class="text-xs text-gray-600 text-center">
                                                                {{ $quoteUsed }} / {{ $quoteAvailable }}
                                                            </div>
                                                            <div class="progress-bar">
                                                                <div class="progress-fill {{ $quotaColor }}" style="width: {{ $quotaPercentage }}%"></div>
                                                            </div>
                                                            <div class="text-xs text-gray-500 text-center">
                                                                {{ number_format($quotaPercentage, 1) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            {{ $package }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex space-x-2">
                                                            <button onclick="getDeviceQR('{{ $deviceId }}')" class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition-all duration-200">
                                                                <i class="fas fa-qrcode mr-1"></i>
                                                                QR Code
                                                            </button>
                                                            <button onclick="showDeviceDetails('{{ $deviceId }}')" class="inline-flex items-center px-3 py-1 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-xs font-medium transition-all duration-200">
                                                                <i class="fas fa-info-circle mr-1"></i>
                                                                Info
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-mobile-alt text-gray-400 text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Devices Found</h4>
                                    <p class="text-gray-500 mb-4">Click "Reload Devices" to load your WhatsApp devices</p>
                                    <button onclick="loadDevices()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200">
                                        <i class="fas fa-sync-alt mr-2"></i>
                                        Load Devices
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- API Results Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-list-alt text-purple-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">API Results & Logs</h3>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="clearResults()" class="inline-flex items-center px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all duration-200 text-sm">
                                    <i class="fas fa-broom mr-2"></i>
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Loading Indicator -->
                        <div id="loadingResults" class="hidden">
                            <div class="flex items-center justify-center py-4">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                                <span class="text-sm text-gray-600">Processing...</span>
                            </div>
                        </div>

                        <!-- Results Container -->
                        <div id="results">
                            <div class="text-center py-8">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-inbox text-gray-400 text-xl"></i>
                                </div>
                                <h4 class="text-md font-medium text-gray-900 mb-1">No Data Available</h4>
                                <p class="text-gray-500 text-sm">Perform actions to see results</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // CSRF Token for Laravel
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Base URLs
    const ROUTES = {
        dashboard: '{{ route("whatspie.dashboard") }}',
        apiGetDeviceQr: '{{ route("api.whatspie.devices.qr", ["device" => ":device"]) }}'.replace(':device', ''),
    };

    // Load devices automatically on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('WhatsPie Dashboard Loaded');
        updateDevicesSummary();
        
        // Auto load devices if not already loaded
        if (document.querySelectorAll('#devicesTableContainer tbody tr').length === 0) {
            loadDevices();
        }
        
        // Check if API is configured
        if (!checkApiConfiguration()) {
            showToast('WhatsPie API is not configured. Please check environment variables.', 'warning');
        }
    });

    // Check API configuration
    function checkApiConfiguration() {
        const baseUrl = '{{ env('WHATSPIE_BASE_URL') }}';
        const apiKey = '{{ env('WHATSPIE_API_KEY') }}';
        
        if (!baseUrl || !apiKey) {
            return false;
        }
        return true;
    }

    // Load available devices
    async function loadDevices() {
        if (!checkApiConfiguration()) {
            showToast('WhatsPie API not configured. Check .env file.', 'error');
            return;
        }

        showLoadingDevices();
        
        try {
            const response = await fetch(`${ROUTES.dashboard}?action=devices`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            
            if (data.success) {
                updateDevicesTable(data.data);
                showToast('Devices loaded successfully!', 'success');
            } else {
                displayResults('devices', data);
                showToast('Failed to load devices: ' + (data.error || 'Unknown error'), 'error');
            }
            
        } catch (error) {
            console.error('Load devices error:', error);
            displayError(error);
            showToast('Error loading devices', 'error');
        } finally {
            hideLoadingDevices();
        }
    }

    // Update devices table
    function updateDevicesTable(devices) {
        const container = document.getElementById('devicesTableContainer');
        const countElement = document.getElementById('devicesCount');
        
        if (devices && devices.length > 0) {
            let tableHTML = `
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Device</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Quota Used</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Package</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
            `;
            
            devices.forEach(device => {
                const deviceData = device.raw || device;
                const deviceId = deviceData.id || device.value;
                const deviceName = deviceData.device_name_mobile || 'Unknown Device';
                const status = deviceData.paired_status || 'UNPAIRED';
                const isActive = deviceData.status === 'ACTIVE';
                const package = deviceData.package?.name || 'N/A';
                const isConnected = status === 'PAIRED';
                
                // Quota information
                const quoteUsed = deviceData.subscription?.quote_used || 0;
                const quoteAvailable = deviceData.subscription?.quote_available || 0;
                const quotaPercentage = quoteAvailable > 0 ? (quoteUsed / quoteAvailable) * 100 : 0;
                const quotaColor = quotaPercentage < 50 ? 'progress-low' : (quotaPercentage < 80 ? 'progress-medium' : 'progress-high');
                
                tableHTML += `
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-mobile-alt text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">${deviceName}</div>
                                    <div class="text-xs text-gray-500">ID: ${deviceId}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col space-y-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${isConnected ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                    ${isConnected ? '✅ Connected' : '❌ Not Connected'}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col space-y-2">
                                <div class="text-xs text-gray-600 text-center">
                                    ${quoteUsed} / ${quoteAvailable}
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill ${quotaColor}" style="width: ${quotaPercentage}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 text-center">
                                    ${quotaPercentage.toFixed(1)}%
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                ${package}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex space-x-2">
                                <button onclick="getDeviceQR('${deviceId}')" class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition-all duration-200">
                                    <i class="fas fa-qrcode mr-1"></i>
                                    QR Code
                                </button>
                                <button onclick="showDeviceDetails('${deviceId}')" class="inline-flex items-center px-3 py-1 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-xs font-medium transition-all duration-200">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Info
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            
            container.innerHTML = tableHTML;
            countElement.textContent = `${devices.length} device${devices.length !== 1 ? 's' : ''}`;
        } else {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-mobile-alt text-gray-400 text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Devices Found</h4>
                    <p class="text-gray-500 mb-4">No WhatsApp devices available</p>
                </div>
            `;
            countElement.textContent = '0 devices';
        }
        
        updateDevicesSummary();
    }

    // Update devices summary
    function updateDevicesSummary() {
        const devicesSummary = document.getElementById('devicesSummary');
        const deviceCount = document.querySelectorAll('#devicesTableContainer tbody tr').length;
        
        if (deviceCount > 0) {
            devicesSummary.innerHTML = `
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                        ${deviceCount} Devices Loaded
                    </span>
                </div>
            `;
        } else {
            devicesSummary.innerHTML = '<span class="text-gray-500">No devices loaded</span>';
        }
    }

    // Test connection
    async function testConnection() {
        if (!checkApiConfiguration()) {
            return;
        }

        showLoadingResults();
        
        try {
            const response = await fetch(`${ROUTES.dashboard}?action=test`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            displayResults('connection', data);
            
        } catch (error) {
            console.error('Test connection error:', error);
            displayError(error);
        } finally {
            hideLoadingResults();
        }
    }

    // Get device QR code
    async function getDeviceQR(deviceIdentifier) {
        console.log('🔍 Getting QR for Device ID:', deviceIdentifier);
        
        // Validasi device ID
        if (!deviceIdentifier || !/^\d+$/.test(deviceIdentifier)) {
            alert('Invalid Device ID.');
            return;
        }

        if (!checkApiConfiguration()) {
            return;
        }

        showLoadingResults();

        try {
            const baseRoute = '{{ route("api.whatspie.devices.qr", ["device" => ":device"]) }}';
            const qrUrl = baseRoute.replace(':device', deviceIdentifier);
            
            console.log('📤 Fetching QR from:', qrUrl);
            
            const response = await fetch(qrUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            displayResults('device_qr', data);

            if (data.success) {
                if (data.qr_code) {
                    showQRCode(deviceIdentifier, data.qr_code, data.qr_url);
                    showToast('QR code retrieved successfully!', 'success');
                } else if (data.data && data.data.qr) {
                    showQRCode(deviceIdentifier, data.data.qr, data.data.url);
                    showToast('QR code retrieved successfully!', 'success');
                } else {
                    showToast('QR code not available for this device', 'warning');
                }
            } else {
                showToast('Failed to get QR code: ' + (data.error || 'Unknown error'), 'error');
            }

        } catch (error) {
            console.error('❌ Get QR code error:', error);
            displayError(error);
            showToast('Error: ' + error.message, 'error');
        } finally {
            hideLoadingResults();
        }
    }

    // Show device details (placeholder function)
    function showDeviceDetails(deviceId) {
        showToast(`Device details for ID: ${deviceId}`, 'info');
        // You can implement detailed device info modal here
    }

    // Show QR code modal
    function showQRCode(deviceId, qrCodeUrl, qrCodeImage = null) {
        const qrImageSrc = qrCodeImage || qrCodeUrl;

        const qrHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full mx-auto text-center">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-qrcode text-green-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Scan QR Code</h3>
                    </div>
                    
                    <div class="mb-4">
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <p class="text-sm text-gray-600">Device ID: <span class="font-mono font-medium">${deviceId}</span></p>
                        </div>
                        
                        ${qrImageSrc ? `
                            <img src="${qrImageSrc}" alt="QR Code" 
                                class="mx-auto border-2 border-gray-200 rounded-xl shadow-sm w-48 h-48 object-contain bg-white">
                        ` : `
                            <div class="w-48 h-48 mx-auto border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-center bg-gray-50">
                                <p class="text-gray-500 text-sm">QR Code not available</p>
                            </div>
                        `}
                    </div>
                    
                    <p class="text-xs text-gray-500 mb-4">
                        Scan this QR code with WhatsApp to connect your device
                    </p>
                    
                    <button onclick="closeModal()" class="w-full flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 text-sm font-medium">
                        <i class="fas fa-check mr-2"></i>
                        Done
                    </button>
                </div>
            </div>
        `;

        document.getElementById('modalContainer').innerHTML = qrHTML;
    }

    function closeModal() {
        document.getElementById('modalContainer').innerHTML = '';
    }

    // Display Results
    function displayResults(type, data) {
        const resultsDiv = document.getElementById('results');
        
        if (type === 'devices') {
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-md font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'Devices Loaded' : 'Failed to Load Devices'}
                            </h4>
                        </div>
                        <span class="text-xs ${data.success ? 'text-green-700' : 'text-red-700'}">
                            ${data.data ? data.data.length + ' devices' : ''}
                        </span>
                    </div>
                    ${data.error ? `<p class="text-sm ${data.success ? 'text-green-700' : 'text-red-700'} mt-2">${data.error}</p>` : ''}
                </div>
            `;
        } else if (type === 'device_qr') {
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-md font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'QR Code Retrieved' : 'Failed to Get QR Code'}
                            </h4>
                        </div>
                    </div>
                    ${data.error ? `<p class="text-sm ${data.success ? 'text-green-700' : 'text-red-700'} mt-2">${data.error}</p>` : ''}
                </div>
            `;
        } else if (type === 'connection') {
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-md font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'Connection Successful' : 'Connection Failed'}
                            </h4>
                        </div>
                    </div>
                    ${data.error ? `<p class="text-sm ${data.success ? 'text-green-700' : 'text-red-700'} mt-2">${data.error}</p>` : ''}
                </div>
            `;
        }
    }

    // Display Error
    function displayError(error) {
        const resultsDiv = document.getElementById('results');
        resultsDiv.innerHTML = `
            <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded-r-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    <h4 class="text-md font-semibold text-red-800">Request Error</h4>
                </div>
                <p class="text-sm text-red-700">${error.message}</p>
            </div>
        `;
    }

    // Clear results
    function clearResults() {
        document.getElementById('results').innerHTML = `
            <div class="text-center py-8">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-inbox text-gray-400 text-xl"></i>
                </div>
                <h4 class="text-md font-medium text-gray-900 mb-1">No Data Available</h4>
                <p class="text-gray-500 text-sm">Perform actions to see results</p>
            </div>
        `;
    }

    // Loading Functions
    function showLoadingDevices() {
        document.getElementById('loadingDevices').classList.remove('hidden');
    }

    function hideLoadingDevices() {
        document.getElementById('loadingDevices').classList.add('hidden');
    }

    function showLoadingResults() {
        document.getElementById('loadingResults').classList.remove('hidden');
    }

    function hideLoadingResults() {
        document.getElementById('loadingResults').classList.add('hidden');
    }

    // Toast Notification
    function showToast(message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        
        const bgColors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `flex items-center w-full max-w-xs p-4 ${bgColors[type]} text-white rounded-lg shadow-lg transform transition-all duration-300 fade-in`;
        toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        toast.setAttribute('role', 'alert');
        
        toast.innerHTML = `
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8">
                <i class="fas ${icons[type]}"></i>
            </div>
            <div class="ml-3 text-sm font-normal">${message}</div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-white hover:text-gray-100 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8" 
                    onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }
</script>
@endsection