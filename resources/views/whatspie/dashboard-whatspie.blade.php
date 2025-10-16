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
    .table-responsive {
        max-height: 400px;
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
    
    /* Smooth transitions */
    .smooth-transition {
        transition: all 0.3s ease-in-out;
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
    
    /* Loading animation */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

@section('content')
<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- Modal Container -->
<div id="modalContainer"></div>

<div class="row">
    <div class=" col-sm-12">
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fab fa-whatsapp text-green-600 text-2xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">WhatsPie Services Dashboard</h1>
                                <p class="text-gray-600 mt-2">Manage your WhatsApp devices and send messages seamlessly</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-3 py-2 rounded-full bg-blue-100 text-blue-800 text-sm font-medium">
                            <i class="fas fa-server mr-2"></i>
                            {{ env('WHATSPIE_BASE_URL', 'Not configured') }}
                        </span>
                        <button onclick="loadDevices()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left Column - Actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Connection Status Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-plug text-blue-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Connection & Devices</h3>
                    </div>

                    <!-- API Status -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Status</label>
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
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Please configure WHATSPIE_BASE_URL and WHATSPIE_API_KEY in .env file
                            </p>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3 mb-6">
                        <button onclick="loadDevices()" class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-sync-alt mr-3"></i>
                            Load Devices
                        </button>
                        <button onclick="testConnection()" class="w-full flex items-center justify-center px-4 py-3 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 font-medium">
                            <i class="fas fa-wifi mr-3"></i>
                            Test Connection
                        </button>
                    </div>

                    <!-- Devices Summary -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Available Devices</label>
                        <div id="devicesSummary" class="text-sm">
                            @if(count($devices) > 0)
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                        {{ count($devices) }} Loaded
                                    </span>
                                    <span class="text-gray-500 text-xs">Click refresh to update status</span>
                                </div>
                            @else
                                <span class="text-gray-500">Click "Load Devices" to refresh</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Send Message Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-paper-plane text-green-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Send Message</h3>
                    </div>

                    <form id="sendMessageForm" onsubmit="sendMessage(event)" class="space-y-4">
                        @csrf
                        
                        <!-- Device Selection -->
                        <div>
                            <label for="deviceSelect" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-mobile-alt mr-2 text-gray-400"></i>
                                Select Device <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select id="deviceSelect" name="device_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
                                <option value="">Choose a device...</option>
                                @foreach($devices as $device)
                                    @php
                                        $deviceName = $device['raw']['device_name_mobile'] ?? $device['name'] ?? 'Unknown Device';
                                        $isPaired = ($device['raw']['paired_status'] ?? '') === 'PAIRED';
                                        $isActive = ($device['raw']['status'] ?? '') === 'ACTIVE';
                                        $statusClass = $isPaired ? 'text-green-600 font-semibold' : 'text-gray-500';
                                        $statusIcon = $isPaired ? '✅' : '❌';
                                    @endphp
                                    
                                    <option value="{{ $device['value'] }}" 
                                            data-status="{{ $device['status'] }}"
                                            data-device-name="{{ $deviceName }}"
                                            data-paired="{{ $isPaired ? 'true' : 'false' }}"
                                            class="{{ $statusClass }}">
                                        {{ $deviceName }} - {{ $device['phone'] ?? $device['value'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-gray-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Select connected WhatsApp device to send message
                            </p>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-phone mr-2 text-gray-400"></i>
                                Receiver Number <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input type="text" 
                                   id="phone"
                                   name="phone"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                   placeholder="081234567890 or 6281234567890"
                                   required
                                   value="{{ old('phone') }}">
                            <p class="text-gray-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Format: 08xxx or 62xxx (international)
                            </p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                Message <span class="text-red-500 ml-1">*</span>
                            </label>
                            <textarea id="message"
                                      name="message"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                      rows="4"
                                      placeholder="Type your message here..."
                                      required>{{ old('message') }}</textarea>
                            <p class="text-gray-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Maximum 1000 characters
                            </p>
                        </div>

                        <!-- Options -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <input type="checkbox" id="simulate" name="simulate" value="1" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="simulate" class="ml-2 text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-keyboard mr-2 text-gray-400"></i>
                                    Simulate Typing
                                </label>
                            </div>
                            <span class="text-xs text-gray-500">Optional</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-paper-plane mr-3"></i>
                            Send WhatsApp Message
                        </button>
                    </form>
                </div>

                <!-- Device Management Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-plus-circle text-purple-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Device Management</h3>
                    </div>

                    <!-- Add New Device -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-plus mr-2 text-gray-400"></i>
                            Add New Device
                        </h4>
                        <!-- Ganti deviceType menjadi devicePackage -->
                        <form onsubmit="addNewDevice(event)" class="space-y-3">
                            <div>
                                <input type="text" 
                                    id="newDeviceName"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                                    placeholder="Device name (optional)"
                                    maxlength="100">
                                <p class="text-gray-500 text-xs mt-1">Leave empty for auto-generated name</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Package</label>
                                <select id="devicePackage" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                    <option value="STARTUP240K">STARTUP240K</option>
                                    <option value="BETA">BETA</option>
                                    <!-- Tambahkan package lain sesuai yang tersedia di akun Anda -->
                                </select>
                            </div>

                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                                <i class="fas fa-plus-circle mr-3"></i>
                                Add New Device
                            </button>
                        </form>
                    </div>

                    <!-- Device Actions -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-cog mr-2 text-gray-400"></i>
                            Device Actions
                        </h4>
                        <div class="space-y-2">
                            <button onclick="showDeviceActions()" class="w-full flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 text-sm font-medium">
                                <i class="fas fa-tools mr-2"></i>
                                Manage Selected Device
                            </button>
                            <button onclick="refreshAllDevices()" class="w-full flex items-center justify-center px-4 py-2 border border-green-600 text-green-600 hover:bg-green-50 rounded-lg transition-all duration-200 text-sm font-medium">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Refresh All Devices
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Device Information Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Device Information</h3>
                    </div>

                    <form onsubmit="getDeviceInfo(event)" class="space-y-4">
                        <div>
                            <label for="deviceInfoId" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-search mr-2 text-gray-400"></i>
                                Check Device Status
                            </label>
                            <input type="text" 
                                   id="deviceInfoId"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                   placeholder="Enter device number..."
                                   required>
                        </div>
                        <button type="submit" class="w-full flex items-center justify-center px-4 py-3 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 font-medium">
                            <i class="fas fa-search mr-3"></i>
                            Get Device Info
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column - Results -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Results Card -->
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
                                <button onclick="testPayload()" class="inline-flex items-center px-3 py-2 border border-blue-300 text-blue-700 hover:bg-blue-50 rounded-lg transition-all duration-200 text-sm">
                                    <i class="fas fa-vial mr-2"></i>
                                    Test Payload
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Loading Indicator -->
                        <div id="loading" class="hidden">
                            <div class="flex items-center justify-center py-8">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mr-4"></div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">Processing Request</h4>
                                    <p class="text-gray-500">Please wait while we process your request...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Results Container -->
                        <div id="results">
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">No Data Available</h4>
                                <p class="text-gray-500">Load devices or send a message to see results</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration & Stats Card -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Configuration Info -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-cog text-yellow-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Configuration</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-key mr-2 text-gray-400"></i>
                                    Environment Variables
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">WHATSPIE_BASE_URL</span>
                                        <span class="text-sm font-medium {{ env('WHATSPIE_BASE_URL') ? 'text-green-600' : 'text-red-600' }}">
                                            {{ env('WHATSPIE_BASE_URL', 'Not set') }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">WHATSPIE_API_KEY</span>
                                        <span class="text-sm font-medium {{ env('WHATSPIE_API_KEY') ? 'text-green-600' : 'text-red-600' }}">
                                            {{ env('WHATSPIE_API_KEY') ? '***' . substr(env('WHATSPIE_API_KEY'), -4) : 'Not set' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm text-gray-600">Timeout</span>
                                        <span class="text-sm font-medium text-blue-600">30 seconds</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-code mr-2 text-gray-400"></i>
                                    API Endpoints
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex items-center py-2 border-b border-gray-100">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded mr-3">GET /devices</code>
                                        <span class="text-xs text-gray-500">List all devices</span>
                                    </div>
                                    <div class="flex items-center py-2 border-b border-gray-100">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded mr-3">GET /devices/&#123;number&#125;</code>
                                        <span class="text-xs text-gray-500">Device info</span>
                                    </div>
                                    <div class="flex items-center py-2">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded mr-3">POST /messages</code>
                                        <span class="text-xs text-gray-500">Send message</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-chart-bar text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Quick Stats</h3>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-blue-50 rounded-xl">
                                <div class="text-2xl font-bold text-blue-600 mb-1" id="statsConnected">0</div>
                                <div class="text-xs text-blue-800 font-medium">Connected</div>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-xl">
                                <div class="text-2xl font-bold text-purple-600 mb-1" id="statsTotal">0</div>
                                <div class="text-xs text-purple-800 font-medium">Total Devices</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-xl">
                                <div class="text-2xl font-bold text-green-600 mb-1" id="statsMessages">0</div>
                                <div class="text-xs text-green-800 font-medium">Messages Sent</div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Last Updated</span>
                                <span class="text-gray-900 font-medium" id="lastUpdated">Never</span>
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
        sendMessage: '{{ route("whatspie.send.message") }}',
        dashboard: '{{ route("whatspie.dashboard") }}',
        apiDevices: '{{ route("api.whatspie.devices") }}',
        apiAddDevice: '{{ route("api.whatspie.devices.add") }}',
        apiDeleteDevice: '{{ route("api.whatspie.devices.delete", ["device" => ":device"]) }}'.replace(':device', ''),
        apiRestartDevice: '{{ route("api.whatspie.devices.restart", ["device" => ":device"]) }}'.replace(':device', ''),
        apiGetDeviceQr: '{{ route("api.whatspie.devices.qr", ["device" => ":device"]) }}'.replace(':device', ''),
        apiTestConnection: '{{ route("api.whatspie.connection.test") }}'
    };

    // Stats counter
    let messagesSent = 0;

    // Load devices on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('WhatsPie Dashboard Loaded');
        updateDevicesSummary();
        updateStats();
        updateLastUpdated();
        
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
            showToast('WhatsPie API not configured. Check .env file.', 'error');
            return false;
        }
        return true;
    }

    // Update last updated time
    function updateLastUpdated() {
        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
    }

    // Load available devices
    async function loadDevices() {
        if (!checkApiConfiguration()) {
            return;
        }

        showLoading();
        
        try {
            const response = await fetch(`${ROUTES.dashboard}?action=devices`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Expected JSON but got: ${text.substring(0, 100)}...`);
            }

            const data = await response.json();
            
            if (data.success) {
                updateDeviceDropdown(data.data);
                displayResults('devices', data);
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
            hideLoading();
            updateLastUpdated();
        }
    }

    // Update device dropdown
    function updateDeviceDropdown(devices) {
        const deviceSelect = document.getElementById('deviceSelect');
        
        if (devices && devices.length > 0) {
            deviceSelect.innerHTML = '<option value="">Choose a device...</option>';
            
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.value;
                option.textContent = device.text;
                option.setAttribute('data-status', device.status);
                
                if (device.status === 'connected') {
                    option.textContent += ' ✅';
                    option.classList.add('text-green-600', 'font-semibold');
                } else {
                    option.textContent += ' ❌';
                    option.classList.add('text-gray-500');
                }
                
                deviceSelect.appendChild(option);
            });
        } else {
            deviceSelect.innerHTML = '<option value="">No devices found</option>';
        }
        
        updateDevicesSummary();
        updateStats();
    }

    // Update devices summary
    function updateDevicesSummary() {
        const deviceSelect = document.getElementById('deviceSelect');
        const devicesSummary = document.getElementById('devicesSummary');
        const options = deviceSelect.querySelectorAll('option');
        let connectedCount = 0;
        let totalCount = 0;

        options.forEach(option => {
            if (option.value && option.value !== '') {
                totalCount++;
                if (option.getAttribute('data-status') === 'connected') {
                    connectedCount++;
                }
            }
        });

        if (totalCount > 0) {
            devicesSummary.innerHTML = `
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                        ${connectedCount} Connected
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                        ${totalCount} Total
                    </span>
                </div>
            `;
        } else {
            devicesSummary.innerHTML = '<span class="text-gray-500">No devices loaded</span>';
        }

        // Update stats
        document.getElementById('statsConnected').textContent = connectedCount;
        document.getElementById('statsTotal').textContent = totalCount;
    }

    // Update stats
    function updateStats() {
        document.getElementById('statsMessages').textContent = messagesSent;
    }

    // Test connection
    async function testConnection() {
        if (!checkApiConfiguration()) {
            return;
        }

        showLoading();
        
        try {
            const response = await fetch(`${ROUTES.dashboard}?action=test`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Expected JSON but got: ${text.substring(0, 100)}...`);
            }

            const data = await response.json();
            displayResults('connection', data);
            
        } catch (error) {
            console.error('Test connection error:', error);
            displayError(error);
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    // Send message
    async function sendMessage(event) {
        event.preventDefault();
        
        if (!checkApiConfiguration()) {
            return;
        }

        showLoading();

        const formData = new FormData(event.target);
        
        const payload = {
            phone: formData.get('phone'),
            message: formData.get('message'),
            device_id: formData.get('device_id'),
            simulate: formData.get('simulate') ? true : false
        };

        // Validation
        if (!payload.phone || !payload.message || !payload.device_id) {
            alert('Please fill in all required fields');
            hideLoading();
            return;
        }

        // Phone number validation
        const phoneRegex = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
        if (!phoneRegex.test(payload.phone.replace(/\s/g, ''))) {
            alert('Please enter a valid Indonesian phone number');
            hideLoading();
            return;
        }

        try {
            console.log('Sending message to:', ROUTES.sendMessage);
            console.log('Payload:', payload);

            const response = await fetch(ROUTES.sendMessage, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            console.log('Response status:', response.status);

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Expected JSON but got: ${text.substring(0, 100)}...`);
            }

            const data = await response.json();
            displayResults('message', data);
            
            // Clear form if successful
            if (data.success) {
                document.getElementById('sendMessageForm').reset();
                messagesSent++;
                updateStats();
                showToast('Message sent successfully!', 'success');
            } else {
                showToast('Failed to send message: ' + (data.error || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Send message error:', error);
            displayError(error);
            showToast('Network error occurred', 'error');
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    // Get device info
    async function getDeviceInfo(event) {
        event.preventDefault();
        
        if (!checkApiConfiguration()) {
            return;
        }

        const deviceId = document.getElementById('deviceInfoId').value;
        if (!deviceId) {
            alert('Please enter a device number');
            return;
        }

        showLoading();
        
        try {
            const response = await fetch(`${ROUTES.dashboard}?action=device-info&device_phone=${encodeURIComponent(deviceId)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Expected JSON but got: ${text.substring(0, 100)}...`);
            }

            const data = await response.json();
            displayResults('device_info', data);
            
        } catch (error) {
            console.error('Get device info error:', error);
            displayError(error);
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    async function addNewDevice(event) {
        event.preventDefault();
        
        if (!checkApiConfiguration()) {
            return;
        }

        const deviceName = document.getElementById('newDeviceName').value.trim();
        const package = document.getElementById('devicePackage').value; // Ganti dari deviceType ke devicePackage

        // Validasi
        if (!package) {
            showToast('Please select package', 'error');
            return;
        }

        const finalDeviceName = deviceName || `Device-${Date.now()}`;

        // ✅ Payload yang BENAR sesuai dokumentasi
        const payload = {
            name: finalDeviceName,      // ✅ Field: name
            package: package           // ✅ Field: package
        };

        console.log('📦 Payload yang akan dikirim:', payload);

        showLoading();

        try {
            const response = await fetch(ROUTES.apiAddDevice, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            displayResults('add_device', data);

            if (data.success) {
                document.getElementById('newDeviceName').value = '';
                document.getElementById('devicePackage').value = 'Beta Tester'; // Reset ke default
                showToast('Device added successfully!', 'success');
                setTimeout(() => loadDevices(), 2000);
            } else {
                showToast('Failed to add device: ' + (data.error || 'Unknown error'), 'error');
            }

        } catch (error) {
            console.error('Add device error:', error);
            displayError(error);
            showToast('Network error occurred', 'error');
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    async function deleteDevice(deviceIdentifier) {
        if (!deviceIdentifier) {
            const selectedDevice = document.getElementById('deviceSelect').value;
            if (!selectedDevice) {
                alert('Please select a device first');
                return;
            }
            deviceIdentifier = selectedDevice;
        }

        if (!confirm(`Are you sure you want to delete device: ${deviceIdentifier}?`)) {
            return;
        }

        if (!checkApiConfiguration()) {
            return;
        }

        showLoading();

        try {
            const response = await fetch(ROUTES.apiDeleteDevice + encodeURIComponent(deviceIdentifier), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            displayResults('delete_device', data);

            if (data.success) {
                // Reload devices list
                setTimeout(() => loadDevices(), 1000);
                showToast('Device deleted successfully!', 'success');
            } else {
                showToast('Failed to delete device: ' + (data.error || 'Unknown error'), 'error');
            }

        } catch (error) {
            console.error('Delete device error:', error);
            displayError(error);
            showToast('Network error occurred', 'error');
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    async function restartDevice(deviceIdentifier) {
        if (!deviceIdentifier) {
            const selectedDevice = document.getElementById('deviceSelect').value;
            if (!selectedDevice) {
                alert('Please select a device first');
                return;
            }
            deviceIdentifier = selectedDevice;
        }

        if (!confirm(`Are you sure you want to restart device: ${deviceIdentifier}?`)) {
            return;
        }

        if (!checkApiConfiguration()) {
            return;
        }

        showLoading();

        try {
            const response = await fetch(ROUTES.apiRestartDevice + encodeURIComponent(deviceIdentifier), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            displayResults('restart_device', data);

            if (data.success) {
                showToast('Device restart command sent!', 'success');
            } else {
                showToast('Failed to restart device: ' + (data.error || 'Unknown error'), 'error');
            }

        } catch (error) {
            console.error('Restart device error:', error);
            displayError(error);
            showToast('Network error occurred', 'error');
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    async function getDeviceQR(deviceIdentifier) {
        if (!deviceIdentifier) {
            const selectedDevice = document.getElementById('deviceSelect').value;
            if (!selectedDevice) {
                alert('Please select a device first');
                return;
            }
            deviceIdentifier = selectedDevice;
        }

        if (!checkApiConfiguration()) {
            return;
        }

        showLoading();

        try {
            const response = await fetch(ROUTES.apiGetDeviceQr + encodeURIComponent(deviceIdentifier), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            displayResults('device_qr', data);

            if (data.success && data.qr_code) {
                showQRCode(deviceIdentifier, data.qr_code);
                showToast('QR code retrieved successfully!', 'success');
            } else {
                showToast('Failed to get QR code: ' + (data.error || 'Unknown error'), 'error');
            }

        } catch (error) {
            console.error('Get QR code error:', error);
            displayError(error);
            showToast('Network error occurred', 'error');
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    function showDeviceActions() {
        const selectedDevice = document.getElementById('deviceSelect').value;
        if (!selectedDevice) {
            alert('Please select a device first');
            return;
        }

        const deviceName = document.getElementById('deviceSelect').options[document.getElementById('deviceSelect').selectedIndex].text;
        
        const actionHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full mx-auto transform transition-all duration-300 scale-100 opacity-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-mobile-alt text-blue-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Device Actions</h3>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Selected Device:</p>
                        <p class="font-medium text-gray-900 bg-gray-50 p-2 rounded-lg">${deviceName}</p>
                    </div>
                    
                    <div class="space-y-3">
                        <button onclick="getDeviceQR('${selectedDevice}')" class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-qrcode mr-2"></i>
                            Get QR Code
                        </button>
                        
                        <button onclick="restartDevice('${selectedDevice}')" class="w-full flex items-center justify-center px-4 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-redo mr-2"></i>
                            Restart Device
                        </button>
                        
                        <button onclick="deleteDevice('${selectedDevice}')" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Device
                        </button>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <button onclick="closeModal()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all duration-200 font-medium">
                            <i class="fas fa-times mr-2"></i>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('modalContainer').innerHTML = actionHTML;
    }

    function showQRCode(deviceName, qrCodeUrl) {
        const qrHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full mx-auto text-center transform transition-all duration-300 scale-100 opacity-100">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-qrcode text-green-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Scan QR Code</h3>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Device: ${deviceName}</p>
                        <img src="${qrCodeUrl}" alt="QR Code" class="mx-auto border-2 border-gray-200 rounded-xl shadow-sm" style="max-width: 200px;">
                    </div>
                    
                    <p class="text-xs text-gray-500 mb-4">
                        Scan this QR code with WhatsApp to connect your device
                    </p>
                    
                    <button onclick="closeModal()" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 font-medium shadow-sm hover:shadow-md">
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

    function refreshAllDevices() {
        loadDevices();
        showToast('Refreshing all devices...', 'info');
    }

    // Test payload
    async function testPayload() {
        const phone = document.getElementById('phone').value;
        const message = document.getElementById('message').value;
        const device_id = document.getElementById('deviceSelect').value;

        if (!phone || !message || !device_id) {
            alert('Please fill in all message fields first');
            return;
        }

        showLoading();

        try {
            const payload = {
                device: device_id,
                receiver: phone,
                type: 'chat',
                params: {
                    text: message
                },
                simulate_typing: document.getElementById('simulate').checked ? 1 : 0
            };

            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-code text-blue-600 mr-2"></i>
                        <h4 class="text-lg font-semibold text-blue-900">Payload Test Result</h4>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-2">Generated Payload</label>
                            <pre class="bg-white border border-blue-200 rounded-lg p-3 text-sm overflow-x-auto custom-scrollbar">${JSON.stringify(payload, null, 2)}</pre>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-2">CURL Command</label>
                            <pre class="bg-gray-800 text-gray-100 rounded-lg p-3 text-sm overflow-x-auto custom-scrollbar">curl -X POST "{{ env('WHATSPIE_BASE_URL') }}/messages" \\
  -H "Authorization: Bearer {{ env('WHATSPIE_API_KEY') ? '***' . substr(env('WHATSPIE_API_KEY'), -4) : 'YOUR_API_KEY' }}" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '${JSON.stringify(payload)}'</pre>
                        </div>
                    </div>
                    <p class="text-blue-700 text-sm mt-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        This is the payload that will be sent to WhatsPie API
                    </p>
                </div>
            `;

            showToast('Payload generated successfully', 'info');
        } catch (error) {
            console.error('Test payload error:', error);
            displayError(error);
        } finally {
            hideLoading();
            updateLastUpdated();
        }
    }

    // Display Results
    function displayResults(type, data) {
        const resultsDiv = document.getElementById('results');
        
        if (type === 'devices') {
            let devicesTable = '';
            
            if (data.success && data.data) {
                let devicesArray = data.data;
                
                if (Array.isArray(devicesArray) && devicesArray.length > 0) {
                    devicesTable = `
                        <div class="mt-4">
                            <div class="flex items-center mb-4">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <h4 class="text-lg font-semibold text-green-700">Devices Found (${devicesArray.length})</h4>
                            </div>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device Name</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Text</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${devicesArray.map(device => {
                                            // ✅ Extract data dari struktur yang sesuai
                                            const deviceName = device.raw?.device_name_mobile || device.name || 'Unknown Device';
                                            const phoneNumber = device.phone || device.value || 'N/A';
                                            const deviceStatus = device.raw?.paired_status === 'PAIRED' ? 'connected' : 'disconnected';
                                            const isConnected = deviceStatus === 'connected';
                                            
                                            // ✅ Additional info dari raw data
                                            const packageName = device.raw?.package?.name || 'N/A';
                                            const serverName = device.raw?.server?.name || 'N/A';
                                            const quoteUsed = device.raw?.subscription?.quote_used || '0';
                                            const quoteAvailable = device.raw?.subscription?.quote_available || '0';

                                            return `
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-4 py-3 text-sm text-gray-900">
                                                        <div class="flex flex-col">
                                                            <span class="font-medium">${deviceName}</span>
                                                            <div class="mt-1 space-y-1">
                                                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-mobile-alt mr-1"></i>${device.name}
                                                                </span>
                                                                ${packageName !== 'N/A' ? `
                                                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                                        <i class="fas fa-cube mr-1"></i>${packageName}
                                                                    </span>
                                                                ` : ''}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm font-mono text-gray-600">
                                                        <div class="flex flex-col">
                                                            <span>${phoneNumber}</span>
                                                            <span class="text-xs text-gray-500 mt-1">
                                                                <i class="fas fa-server mr-1"></i>${serverName}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-col space-y-2">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${isConnected ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                                                ${device.raw?.paired_status || device.status} ${isConnected ? '✅' : '❌'}
                                                            </span>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${device.raw?.status === 'ACTIVE' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                                                                ${device.raw?.status || 'UNKNOWN'}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        <div class="flex flex-col">
                                                            <span>${device.text}</span>
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                <div class="flex items-center justify-between">
                                                                    <span>Quota:</span>
                                                                    <span class="font-mono">${quoteUsed}/${quoteAvailable}</span>
                                                                </div>
                                                                <div class="flex items-center justify-between mt-1">
                                                                    <span>Updated:</span>
                                                                    <span>${new Date(device.raw?.updated_at).toLocaleDateString()}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    devicesTable = '<p class="text-gray-500 text-center py-4">No devices found</p>';
                }
            }
            
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-lg font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'Devices Loaded Successfully' : 'Failed to Load Devices'}
                            </h4>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${data.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            Status: ${data.status}
                        </span>
                    </div>
                    
                    ${data.error ? `
                        <div class="mb-3">
                            <p class="text-sm ${data.success ? 'text-green-700' : 'text-red-700'}">${data.error}</p>
                        </div>
                    ` : ''}
                    
                    ${devicesTable}
                    
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm ${data.success ? 'text-green-700' : 'text-red-700'} font-medium flex items-center">
                            <i class="fas fa-code mr-2"></i>
                            Raw Response
                        </summary>
                        <pre class="mt-2 bg-white border ${data.success ? 'border-green-200' : 'border-red-200'} rounded-lg p-3 text-xs overflow-x-auto custom-scrollbar">${JSON.stringify(data, null, 2)}</pre>
                    </details>
                </div>
            `;
            
        } else if (type === 'message') {
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-lg font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'Message Sent Successfully' : 'Message Failed to Send'}
                            </h4>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${data.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            Status: ${data.status}
                        </span>
                    </div>
                    
                    ${data.error ? `
                        <div class="mb-3">
                            <p class="text-sm ${data.success ? 'text-green-700' : 'text-red-700'}">${data.error}</p>
                            ${data.body ? `
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-sm ${data.success ? 'text-green-700' : 'text-red-700'}">API Response</summary>
                                    <pre class="mt-2 bg-gray-800 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto custom-scrollbar">${data.body}</pre>
                                </details>
                            ` : ''}
                        </div>
                    ` : ''}
                    
                    ${data.success ? `
                        <div class="mt-3">
                            <h5 class="font-medium ${data.success ? 'text-green-700' : 'text-red-700'} mb-2">Message Details</h5>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="font-medium">To:</span> ${document.getElementById('phone').value}</div>
                                <div><span class="font-medium">Device:</span> ${document.getElementById('deviceSelect').value}</div>
                                <div><span class="font-medium">Status:</span> Sent to API</div>
                                <div><span class="font-medium">Message ID:</span> ${data.message_id || 'N/A'}</div>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${data.payload_sent ? `
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm ${data.success ? 'text-green-700' : 'text-red-700'} font-medium flex items-center">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Payload Sent
                            </summary>
                            <pre class="mt-2 bg-white border ${data.success ? 'border-green-200' : 'border-red-200'} rounded-lg p-3 text-xs overflow-x-auto custom-scrollbar">${JSON.stringify(data.payload_sent, null, 2)}</pre>
                        </details>
                    ` : ''}
                </div>
            `;
            
        } else if (type === 'device_info') {
            let deviceDetails = '';
            
            if (data.success && data.data) {
                const device = data.data;
                deviceDetails = `
                    <div class="mt-4">
                        <h4 class="text-lg font-semibold text-green-700 mb-4 flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Device Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="font-medium text-gray-700">Device ID</span>
                                    <span class="text-gray-900">${device.device_id || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="font-medium text-gray-700">Name</span>
                                    <span class="text-gray-900">${device.name || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="font-medium text-gray-700">Status</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${device.status === 'connected' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                        ${device.status || 'unknown'}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="font-medium text-gray-700">Phone</span>
                                    <span class="text-gray-900">${device.phone || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="font-medium text-gray-700">Platform</span>
                                    <span class="text-gray-900">${device.platform || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="font-medium text-gray-700">Manufacturer</span>
                                    <span class="text-gray-900">${device.manufacturer || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="font-medium text-gray-700">Model</span>
                                    <span class="text-gray-900">${device.model || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="font-medium text-gray-700">SDK Version</span>
                                    <span class="text-gray-900">${device.sdk_version || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-lg font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'Device Info Retrieved' : 'Failed to Get Device Info'}
                            </h4>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${data.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            Status: ${data.status}
                        </span>
                    </div>
                    
                    ${data.error ? `
                        <div class="mb-3">
                            <p class="text-sm ${data.success ? 'text-green-700' : 'text-red-700'}">${data.error}</p>
                        </div>
                    ` : ''}
                    
                    ${deviceDetails}
                    
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm ${data.success ? 'text-green-700' : 'text-red-700'} font-medium flex items-center">
                            <i class="fas fa-code mr-2"></i>
                            Raw Response
                        </summary>
                        <pre class="mt-2 bg-white border ${data.success ? 'border-green-200' : 'border-red-200'} rounded-lg p-3 text-xs overflow-x-auto custom-scrollbar">${JSON.stringify(data, null, 2)}</pre>
                    </details>
                </div>
            `;
            
        } else if (type === 'connection') {
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-lg font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'API Connection Successful' : 'API Connection Failed'}
                            </h4>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${data.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            Status: ${data.status}
                        </span>
                    </div>
                    
                    ${data.error ? `
                        <div class="mb-3">
                            <p class="text-sm ${data.success ? 'text-green-700' : 'text-red-700'}">${data.error}</p>
                        </div>
                    ` : ''}
                    
                    <div class="mt-3">
                        <h5 class="font-medium ${data.success ? 'text-green-700' : 'text-red-700'} mb-2">API Information</h5>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div><span class="font-medium">Base URL:</span> {{ env('WHATSPIE_BASE_URL') }}</div>
                            <div><span class="font-medium">API Key:</span> {{ env('WHATSPIE_API_KEY') ? 'Configured' : 'Not configured' }}</div>
                            <div><span class="font-medium">Timeout:</span> 30 seconds</div>
                            <div><span class="font-medium">Devices Count:</span> ${data.devices_count || 0}</div>
                        </div>
                    </div>
                    
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm ${data.success ? 'text-green-700' : 'text-red-700'} font-medium flex items-center">
                            <i class="fas fa-code mr-2"></i>
                            Raw Response
                        </summary>
                        <pre class="mt-2 bg-white border ${data.success ? 'border-green-200' : 'border-red-200'} rounded-lg p-3 text-xs overflow-x-auto custom-scrollbar">${JSON.stringify(data, null, 2)}</pre>
                    </details>
                </div>
            `;
        } else if (type === 'add_device') {
            resultsDiv.innerHTML = `
                <div class="border-l-4 ${data.success ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'} p-4 rounded-r-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <i class="fas ${data.success ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500'} mr-2"></i>
                            <h4 class="text-lg font-semibold ${data.success ? 'text-green-800' : 'text-red-800'}">
                                ${data.success ? 'Device Added Successfully' : 'Failed to Add Device'}
                            </h4>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${data.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            Status: ${data.status}
                        </span>
                    </div>
                    
                    ${data.success ? `
                        <div class="mt-3">
                            <h5 class="font-medium text-green-700 mb-2">Device Details</h5>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div><span class="font-medium">Name:</span> ${data.data.device_name}</div>
                                <div><span class="font-medium">Status:</span> ${data.data.status}</div>
                                <div><span class="font-medium">Device ID:</span> ${data.data.device_id || 'N/A'}</div>
                                <div><span class="font-medium">Phone:</span> ${data.data.phone_number || 'Not connected'}</div>
                            </div>
                        </div>
                        ${data.data.qr_code ? `
                            <div class="mt-4">
                                <p class="text-green-700 text-sm mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    QR code is available for scanning
                                </p>
                            </div>
                        ` : ''}
                    ` : `
                        <div class="mb-3">
                            <p class="text-sm text-red-700">${data.error}</p>
                        </div>
                    `}
                    
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm ${data.success ? 'text-green-700' : 'text-red-700'} font-medium flex items-center">
                            <i class="fas fa-code mr-2"></i>
                            Raw Response
                        </summary>
                        <pre class="mt-2 bg-white border ${data.success ? 'border-green-200' : 'border-red-200'} rounded-lg p-3 text-xs overflow-x-auto custom-scrollbar">${JSON.stringify(data, null, 2)}</pre>
                    </details>
                </div>
            `;
        }
    }

    // Display Error
    function displayError(error) {
        const resultsDiv = document.getElementById('results');
        resultsDiv.innerHTML = `
            <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded-r-lg">
                <div class="flex items-center mb-3">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    <h4 class="text-lg font-semibold text-red-800">Request Error</h4>
                </div>
                <div class="space-y-2">
                    <p class="text-red-700"><strong>Error Message:</strong> ${error.message}</p>
                    <div class="text-red-700">
                        <strong>Please check:</strong>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>Internet connection</li>
                            <li>API endpoint configuration</li>
                            <li>Server status</li>
                            <li>Check browser console for details</li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
    }

    // Clear results
    function clearResults() {
        document.getElementById('results').innerHTML = `
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-900 mb-2">No Data Available</h4>
                <p class="text-gray-500">Load devices or send a message to see results</p>
            </div>
        `;
        showToast('Results cleared', 'info');
    }

    // Loading Functions
    function showLoading() {
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('results').classList.add('hidden');
    }

    function hideLoading() {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('results').classList.remove('hidden');
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
        
        // Create toast element
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `flex items-center w-full max-w-xs p-4 ${bgColors[type]} text-white rounded-lg shadow-lg transform transition-all duration-300 fade-in`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
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
        
        // Add to body for top-most layer
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
</script>
@endsection