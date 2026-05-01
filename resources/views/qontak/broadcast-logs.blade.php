@extends('layouts/contentNavbarLayout')

@section('title', 'Qontak Broadcast Logs')

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Enhanced Tailwind Config -->
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: {
            50: '#eff6ff',
            100: '#dbeafe',
            200: '#bfdbfe',
            300: '#93c5fd',
            400: '#60a5fa',
            500: '#3b82f6',
            600: '#2563eb',
            700: '#1d4ed8',
            800: '#1e40af',
            900: '#1e3a8a',
          },
          success: {
            50: '#f0fdf4',
            100: '#dcfce7',
            200: '#bbf7d0',
            300: '#86efac',
            400: '#4ade80',
            500: '#22c55e',
            600: '#16a34a',
            700: '#15803d',
            800: '#166534',
            900: '#14532d',
          },
          warning: {
            50: '#fffbeb',
            100: '#fef3c7',
            200: '#fde68a',
            300: '#fcd34d',
            400: '#fbbf24',
            500: '#f59e0b',
            600: '#d97706',
            700: '#b45309',
            800: '#92400e',
            900: '#78350f',
          },
          danger: {
            50: '#fef2f2',
            100: '#fee2e2',
            200: '#fecaca',
            300: '#fca5a5',
            400: '#f87171',
            500: '#ef4444',
            600: '#dc2626',
            700: '#b91c1c',
            800: '#991b1b',
            900: '#7f1d1d',
          }
        },
        fontFamily: {
          sans: ['Inter', 'ui-sans-serif', 'system-ui'],
        },
        boxShadow: {
          'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
          'medium': '0 4px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
          'large': '0 10px 40px -10px rgba(0, 0, 0, 0.1), 0 20px 25px -5px rgba(0, 0, 0, 0.04)',
        },
        animation: {
          'fade-in': 'fadeIn 0.5s ease-in-out',
          'slide-up': 'slideUp 0.3s ease-out',
          'pulse-soft': 'pulseSoft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        },
        keyframes: {
          fadeIn: {
            '0%': { opacity: '0' },
            '100%': { opacity: '1' },
          },
          slideUp: {
            '0%': { transform: 'translateY(10px)', opacity: '0' },
            '100%': { transform: 'translateY(0)', opacity: '1' },
          },
          pulseSoft: {
            '0%, 100%': { opacity: '1' },
            '50%': { opacity: '0.5' },
          },
        }
      }
    }
  }
</script>
<style>
  body {
    font-family: 'Inter', ui-sans-serif, system-ui;
  }

  /* Enhanced status badge styles */
  .status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-weight: 500;
    text-transform: capitalize;
    letter-spacing: 0.025em;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
  }

  .status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
  }

  .status-badge:hover::before {
    left: 100%;
  }

  .status-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .status-sent {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: 1px solid #2563eb;
  }

  .status-delivered {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    border: 1px solid #16a34a;
  }

  .status-read {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    border: 1px solid #0891b2;
  }

  .status-failed {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border: 1px solid #dc2626;
  }

  .status-pending {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border: 1px solid #d97706;
  }

  .status-done {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: 1px solid #059669;
  }

  /* Enhanced table styles */
  .table-row-hover {
    transition: all 0.2s ease;
  }

  .table-row-hover:hover {
    background-color: #f8fafc;
    transform: translateX(2px);
  }

  /* Custom scrollbar */
  ::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }

  ::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }

  ::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  /* Loading animation */
  .loading-dots span {
    animation: loading 1.4s infinite ease-in-out both;
  }

  .loading-dots span:nth-child(1) {
    animation-delay: -0.32s;
  }

  .loading-dots span:nth-child(2) {
    animation-delay: -0.16s;
  }

  @keyframes loading {

    0%,
    80%,
    100% {
      transform: scale(0);
    }

    40% {
      transform: scale(1);
    }
  }

  /* Card hover effects */
  .card-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }

  /* Button enhancements */
  .btn-enhanced {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .btn-enhanced::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }

  .btn-enhanced:hover::before {
    width: 300px;
    height: 300px;
  }

  /* Modal backdrop blur */
  .modal-backdrop-blur {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
  }
</style>

@section('content')
  <!-- Enhanced Content with Tailwind CSS -->
  <div class="col-sm-12 min-h-screen">
    <!-- Enhanced Header -->
    <header
      class="bg-white shadow-soft rounded-lg border-b border-gray-100/50 backdrop-blur-sm bg-opacity-90 sticky top-0 z-40 mb-5">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          <div class="flex items-center space-x-4">
            <div class="relative">
              <div class="absolute inset-0 bg-primary-500 rounded-full blur-xl opacity-20 animate-pulse-soft"></div>
              <div class="relative bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl p-3 shadow-medium">
                <i class="fas fa-broadcast-tower text-white text-xl"></i>
              </div>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Qontak Broadcast Logs</h1>
              <p class="text-sm text-gray-500">Monitor and manage your WhatsApp broadcasts</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <button onclick="testConnection()"
              class="btn-enhanced inline-flex items-center px-4 py-2.5 border border-gray-200 shadow-soft text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
              <i class="fas fa-plug mr-2"></i>
              Test Connection
            </button>
            <button onclick="testEndpoints()"
              class="btn-enhanced inline-flex items-center px-4 py-2.5 border border-gray-200 shadow-soft text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
              <i class="fas fa-vial mr-2"></i>
              Test Endpoints
            </button>
            <button onclick="refreshLogs()"
              class="btn-enhanced inline-flex items-center px-4 py-2.5 border border-transparent shadow-soft text-sm font-medium rounded-lg text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
              <i class="fas fa-sync-alt mr-2"></i>
              Refresh
            </button>
            <!-- New: Send Maintenance Button -->
            <button onclick="openMaintenanceModal()"
              class="btn-enhanced inline-flex items-center px-4 py-2.5 border border-transparent shadow-soft text-sm font-medium rounded-lg text-white bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200"
              title="Send Maintenance">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Send Maintenance
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Enhanced Main Content -->
    <main class="max-w-7xl mx-auto">
      <!-- Enhanced Filters Section -->
      <div class="bg-white rounded-2xl shadow-soft p-6 mb-8 card-hover">
        <div class="flex items-center mb-6">
          <div class="bg-primary-100 rounded-lg p-2 mr-3">
            <i class="fas fa-filter text-primary-600"></i>
          </div>
          <h2 class="text-lg font-semibold text-gray-900">Filter Broadcast Logs</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="space-y-2">
            <label for="statusFilter" class="block text-sm font-medium text-gray-700 flex items-center">
              <i class="fas fa-chart-line text-gray-400 mr-2 text-xs"></i>
              Status
            </label>
            <select id="statusFilter"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
              <option value="">All Status</option>
              <option value="sent">Sent</option>
              <option value="delivered">Delivered</option>
              <option value="read">Read</option>
              <option value="failed">Failed</option>
            </select>
          </div>
          <div class="space-y-2">
            <label for="channelFilter" class="block text-sm font-medium text-gray-700 flex items-center">
              <i class="fas fa-broadcast-tower text-gray-400 mr-2 text-xs"></i>
              Channel
            </label>
            <select id="channelFilter"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
              <option value="">All Channels</option>
              <option value="whatsapp">WhatsApp</option>
            </select>
          </div>
          <div class="space-y-2">
            <label for="limitFilter" class="block text-sm font-medium text-gray-700 flex items-center">
              <i class="fas fa-list-ol text-gray-400 mr-2 text-xs"></i>
              Limit
            </label>
            <select id="limitFilter"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
              <option value="10">10</option>
              <option value="25" selected>25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <div class="flex items-end space-x-3">
            <button onclick="applyFilters()"
              class="btn-enhanced flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 shadow-soft hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
              <i class="fas fa-check mr-2"></i>
              Apply
            </button>
            <button onclick="clearFilters()"
              class="btn-enhanced inline-flex justify-center items-center px-4 py-2.5 border border-gray-200 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 shadow-soft hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
              <i class="fas fa-times mr-2"></i>
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Enhanced Loading Indicator -->
      <div id="loadingIndicator" class="hidden">
        <div class="bg-white rounded-2xl shadow-soft p-12">
          <div class="flex flex-col items-center space-y-4">
            <div class="relative">
              <div class="animate-spin rounded-full h-16 w-16 border-4 border-primary-200 border-t-primary-600"></div>
              <div
                class="absolute inset-0 rounded-full h-16 w-16 border-4 border-primary-200 border-t-transparent animate-ping">
              </div>
            </div>
            <div class="text-center">
              <p class="text-lg font-medium text-gray-900">Loading broadcast logs...</p>
              <div class="loading-dots flex justify-center space-x-1 mt-2">
                <span class="w-2 h-2 bg-primary-600 rounded-full"></span>
                <span class="w-2 h-2 bg-primary-600 rounded-full"></span>
                <span class="w-2 h-2 bg-primary-600 rounded-full"></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Error Message -->
      <div id="errorMessage" class="hidden bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-lg animate-fade-in">
        <div class="flex">
          <div class="flex-shrink-0">
            <div class="bg-red-100 rounded-full p-2">
              <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
          </div>
          <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">
              <p id="errorText"></p>
              <div id="errorActions"></div>
            </div>
          </div>
          <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
              <button onclick="hideError()"
                class="inline-flex bg-red-100 rounded-md p-1.5 text-red-600 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Debug Panel (can be removed in production) -->
      <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl shadow-soft p-6 mb-8 border border-gray-200/50">
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center">
            <div class="bg-yellow-100 rounded-lg p-2 mr-3">
              <i class="fas fa-bug text-yellow-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Debug Information</h3>
              <p class="text-sm text-gray-500">API Response & Data Structure Analysis</p>
            </div>
          </div>
          <button onclick="toggleDebug()"
            class="btn-enhanced inline-flex items-center px-4 py-2 border border-gray-200 shadow-soft text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 mr-2">
            <i class="fas fa-eye mr-2"></i>
            Toggle Debug
          </button>
          <button onclick="debugPagination()"
            class="btn-enhanced inline-flex items-center px-4 py-2 border border-gray-200 shadow-soft text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
            <i class="fas fa-bug mr-2"></i>
            Debug Pagination
          </button>
        </div>
        <div id="debugPanel" class="hidden animate-slide-up">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl p-4 shadow-inner">
              <div class="flex items-center mb-3">
                <i class="fas fa-code text-blue-500 mr-2"></i>
                <h4 class="text-sm font-semibold text-gray-900">API Response</h4>
              </div>
              <pre id="debugResponse"
                class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs overflow-x-auto max-h-64 font-mono"></pre>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-inner">
              <div class="flex items-center mb-3">
                <i class="fas fa-sitemap text-purple-500 mr-2"></i>
                <h4 class="text-sm font-semibold text-gray-900">Data Structure</h4>
              </div>
              <pre id="debugStructure"
                class="bg-gray-900 text-blue-400 p-4 rounded-lg text-xs overflow-x-auto max-h-64 font-mono"></pre>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Broadcast Logs Table -->
      <div class="bg-white rounded-2xl shadow-soft overflow-hidden card-hover">
        <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="bg-primary-100 rounded-lg p-2 mr-3">
                <i class="fas fa-history text-primary-600"></i>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Broadcast History</h3>
                <p class="text-sm text-gray-500">View and analyze your broadcast logs</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse"></span>
                Live
              </span>
            </div>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Broadcast ID
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Template</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Channel</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Messages</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sent At</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody id="broadcastLogsBody" class="bg-white divide-y divide-gray-100">
              <!-- Data will be loaded here -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Enhanced Pagination -->
      <div id="paginationContainer"
        class="mt-8 flex flex-col sm:flex-row justify-between items-center bg-white rounded-2xl shadow-soft p-6">
        <div id="paginationInfo" class="text-sm text-gray-600 mb-4 sm:mb-0">
          <div class="flex items-center">
            <i class="fas fa-info-circle text-gray-400 mr-2"></i>
            <span id="paginationInfoText">Loading...</span>
          </div>
        </div>
        <nav aria-label="Broadcast logs pagination" class="flex items-center space-x-1">
          <ul class="inline-flex -space-x-px rounded-lg shadow-sm" id="pagination">
            <!-- Pagination will be loaded here -->
          </ul>
        </nav>
      </div>
    </main>
  </div>

  <!-- Enhanced Broadcast Detail Modal -->
  <div id="broadcastDetailModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-gray-900 bg-opacity-75 modal-backdrop-blur transition-opacity" aria-hidden="true">
      </div>

      <!-- Modal Panel -->
      <div
        class="relative inline-flex items-center justify-center w-full max-w-4xl transform transition-all sm:my-8 sm:w-full sm:max-w-4xl lg:max-w-5xl">
        <div class="bg-white rounded-2xl shadow-large text-left overflow-hidden animate-slide-up">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="bg-white/20 rounded-lg p-2 mr-3">
                  <i class="fas fa-info-circle text-white"></i>
                </div>
                <h3 class="text-xl font-semibold text-white">Broadcast Detail</h3>
              </div>
              <div class="ml-auto flex items-center">
                <button onclick="closeModal()"
                  class="bg-white/20 rounded-lg p-2 text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors">
                  <i class="fas fa-times text-xl"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
            <div id="broadcastDetailContent">
              <!-- Detail will be loaded here -->
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Broadcast ID: <span class="font-mono text-xs" id="modalBroadcastId">N/A</span>
              </div>
              <div class="flex space-x-3">
                <button onclick="exportModalDetail()"
                  class="btn-enhanced inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                  <i class="fas fa-download mr-2"></i>
                  Export
                </button>
                <button onclick="closeModal()"
                  class="btn-enhanced inline-flex justify-center items-center px-6 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-500 hover:bg-primary-600 hover:shadow-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                  <i class="fas fa-times mr-2"></i>
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentPage = 1;
    let currentFilters = {};

    document.addEventListener('DOMContentLoaded', function () {
      loadBroadcastLogs();
    });

    function loadBroadcastLogs(page = 1) {
      showLoading();

      const params = {
        limit: document.getElementById('limitFilter').value,
        offset: (page - 1) * parseInt(document.getElementById('limitFilter').value),
        ...currentFilters
      };

      console.log('Loading broadcast logs with params:', params);

      fetch(`/qontak/broadcast-history?${new URLSearchParams(params)}`)
        .then(response => {
          console.log('Response status:', response.status);
          return response.json();
        })
        .then(data => {
          console.log('Full API response:', data);
          hideLoading();
          hideError(); // Hide any previous errors

          // Update debug information
          updateDebugInfo(data);

          if (data.success) {
            displayBroadcastLogs(data.data);
            // Pass both the data and current page to pagination
            updatePagination(data, page);
            // Update global current page
            currentPage = page;
          } else {
            showError(data.message || 'Unknown error occurred');
          }
        })
        .catch(error => {
          console.error('Error loading broadcast logs:', error);
          hideLoading();
          hideError();

          // Check if it's an authentication error
          if (error.message && error.message.includes('invalid_token')) {
            showError('Authentication Error: Access token is invalid or expired. Please check your Qontak API credentials.');
          } else if (error.status === 401) {
            showError('API Error: ' + (error.message || 'Unknown error occurred'));
          } else {
            showError('Failed to load broadcast logs: ' + error.message);
          }
        });
    }

    function displayBroadcastLogs(data) {
      const tbody = document.getElementById('broadcastLogsBody');
      tbody.innerHTML = '';

      // Debug: Log the actual data structure
      console.log('API Response Data:', data);

      // Handle the specific response structure from Qontak WhatsApp broadcast API
      let broadcasts = [];
      let pagination = {};

      // Case 1: Response with data array and meta.pagination (Qontak WhatsApp broadcast API)
      if (data.data && Array.isArray(data.data)) {
        broadcasts = data.data;
        if (data.meta && data.meta.pagination) {
          pagination = {
            total: data.meta.pagination.total || broadcasts.length,
            limit: data.meta.pagination.limit || 25,
            offset: data.meta.pagination.offset || 0
          };
        } else {
          pagination = {
            total: data.total || broadcasts.length,
            limit: data.limit || 25,
            offset: data.offset || 0
          };
        }
      }
      // Case 2: Direct array response (from your example)
      else if (Array.isArray(data)) {
        broadcasts = data;
        pagination = {
          total: data.total || data.length,
          limit: data.limit || 25,
          offset: data.offset || 0
        };
      }
      // Case 3: Look for any array property
      else {
        for (let key in data) {
          if (Array.isArray(data[key])) {
            broadcasts = data[key];
            pagination = {
              total: data.total || data[key].length,
              limit: data.limit || 25,
              offset: data.offset || 0
            };
            break;
          }
        }
      }

      // Store pagination data for global use
      window.currentPagination = pagination;

      if (broadcasts.length === 0) {
        tbody.innerHTML = `
                  <tr>
                      <td colspan="7" class="px-6 py-12 text-center">
                          <div class="flex flex-col items-center">
                              <div class="bg-gray-100 rounded-full p-3 mb-3">
                                  <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                              </div>
                              <p class="text-gray-500 font-medium">No broadcast logs found</p>
                              <p class="text-gray-400 text-sm mt-1">Try adjusting your filters or check back later</p>
                          </div>
                      </td>
                  </tr>
              `;
        console.log('No broadcasts found in data structure');

        // Show the table if it's hidden
        const broadcastLogsTable = document.querySelector('.bg-white.rounded-2xl.shadow-soft.overflow-hidden');
        if (broadcastLogsTable) {
          broadcastLogsTable.style.display = 'block';
        }

        return;
      }

      console.log('Found broadcasts:', broadcasts.length);
      console.log('Pagination data:', pagination);

      broadcasts.forEach((broadcast, index) => {
        console.log(`Broadcast ${index}:`, broadcast);

        // Extract fields based on the actual Qontak WhatsApp broadcast API response structure
        const broadcastId = broadcast.messages_broadcast_id || broadcast.id || `ID-${index}`;

        // Extract template name from messages.body.template
        let templateName = 'N/A';
        if (broadcast.messages && broadcast.messages.body && broadcast.messages.body.template) {
          // Extract template content and truncate if too long
          templateName = broadcast.messages.body.template;
          if (templateName.length > 50) {
            templateName = templateName.substring(0, 50) + '...';
          }
        }

        const channel = broadcast.channel_integration_id || 'whatsapp';
        const status = broadcast.status || 'pending';

        // Extract contact name from contact_extra first, then fallback to contact_full_name
        let contactName = 'Unknown';
        if (broadcast.contact_extra) {
          if (typeof broadcast.contact_extra === 'string') {
            try {
              const contactExtraParsed = JSON.parse(broadcast.contact_extra);
              contactName = contactExtraParsed.name || contactExtraParsed.full_name || contactExtraParsed.contact_name || broadcast.contact_full_name || 'Unknown';
            } catch (e) {
              // If parsing fails, try to use the string directly
              contactName = broadcast.contact_extra || broadcast.contact_full_name || 'Unknown';
            }
          } else if (typeof broadcast.contact_extra === 'object') {
            contactName = broadcast.contact_extra.nama || broadcast.contact_extra.full_name || broadcast.contact_extra.contact_name || broadcast.contact_full_name || 'Unknown';
          }
        } else {
          contactName = broadcast.contact_full_name || 'Unknown';
        }

        // Extract phone number
        const phoneNumber = broadcast.contact_extra.nama || 'N/A';

        const createdAt = broadcast.created_at;

        // Calculate total messages from messages_response
        let totalMessages = 0;
        if (broadcast.messages_response) {
          Object.keys(broadcast.messages_response).forEach(key => {
            if (broadcast.messages_response[key] && broadcast.messages_response[key].statuses) {
              totalMessages += broadcast.messages_response[key].statuses.length;
            }
          });
        }

        const row = document.createElement('tr');
        row.className = 'table-row-hover';
        row.innerHTML = `
                  <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                          <div class="bg-gray-100 rounded-lg p-1.5 mr-2">
                              <i class="fas fa-hashtag text-gray-500 text-xs"></i>
                          </div>
                          <code class="text-xs font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">${broadcastId.substring(0, 8)}...</code>
                      </div>
                  </td>
                  <td class="px-6 py-4">
                      <div class="flex flex-col">
                          <div class="flex items-center mb-1">
                              <div class="bg-purple-100 rounded-lg p-1.5 mr-2">
                                  <i class="fas fa-file-alt text-purple-500 text-xs"></i>
                              </div>
                              <span class="text-sm font-medium text-gray-900">${broadcast.message_template.name}</span>
                          </div>
                          <div class="text-xs text-gray-500 ml-8">
                              <i class="fas fa-user mr-1"></i>${contactName}
                          </div>
                      </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-col">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-1">
                              <i class="fas fa-whatsapp mr-1"></i>
                              WhatsApp
                          </span>
                          <code class="text-xs text-gray-500">${channel.substring(0, 8)}...</code>
                      </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <span class="status-badge status-${status.toLowerCase()}">${broadcast.execute_status}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                          <div class="bg-blue-100 rounded-lg p-1.5 mr-2">
                              <i class="fas fa-envelope text-blue-500 text-xs"></i>
                          </div>
                          <div class="flex flex-col">
                              <span class="text-sm font-medium text-gray-900">${totalMessages || 1}</span>
                              <span class="text-xs text-gray-500">messages</span>
                          </div>
                      </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-col text-sm text-gray-900">
                          <div class="flex items-center mb-1">
                              <div class="bg-gray-100 rounded-lg p-1.5 mr-2">
                                  <i class="fas fa-clock text-gray-500 text-xs"></i>
                              </div>
                              <span>${createdAt ? new Date(createdAt).toLocaleDateString() : 'N/A'}</span>
                          </div>
                          <span class="text-xs text-gray-500 ml-8">${createdAt ? new Date(createdAt).toLocaleTimeString() : 'N/A'}</span>
                      </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex flex-col space-y-1">
                          <button onclick="viewBroadcastDetail('${broadcastId}')" class="btn-enhanced inline-flex justify-center items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-primary-600 bg-primary-50 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                              <i class="fas fa-eye mr-1"></i>
                              View Detail
                          </button>
                          <div class="text-xs text-gray-500 text-center">
                              ${phoneNumber}
                          </div>
                      </div>
                  </td>
              `;
        tbody.appendChild(row);
      });
    }

    function updatePagination(data, currentPage) {
      const paginationInfo = document.getElementById('paginationInfo');
      const pagination = document.getElementById('pagination');

      console.log('updatePagination called with:', { data, currentPage });

      // Extract pagination data from multiple possible sources
      let total = 0;
      let limit = parseInt(document.getElementById('limitFilter').value) || 25;
      let offset = 0;

      // Try different data structures
      if (data && typeof data === 'object') {
        // Check for meta.pagination structure (common in APIs)
        if (data.meta && data.meta.pagination) {
          total = data.meta.pagination.total || 0;
          limit = data.meta.pagination.limit || limit;
          offset = data.meta.pagination.offset || 0;
        }
        // Check for direct pagination properties
        else if (data.total !== undefined) {
          total = data.total;
          limit = data.limit || limit;
          offset = data.offset || offset;
        }
        // Check for total_count
        else if (data.total_count !== undefined) {
          total = data.total_count;
          limit = data.limit || limit;
          offset = data.offset || offset;
        }
        // Check if data itself is an array
        else if (Array.isArray(data)) {
          total = data.length;
          offset = 0;
        }
        // Check for data array within response
        else if (data.data && Array.isArray(data.data)) {
          total = data.total || data.data.length;
          offset = data.offset || 0;
        }
      }

      const totalPages = Math.ceil(total / limit);
      const start = total > 0 ? offset + 1 : 0;
      const end = total > 0 ? Math.min(offset + limit, total) : 0;

      console.log('Pagination calculated:', { total, limit, offset, currentPage, totalPages, start, end });

      // Update pagination info
      if (paginationInfo) {
        const paginationInfoText = document.getElementById('paginationInfoText');
        if (paginationInfoText) {
          if (total === 0) {
            paginationInfoText.innerHTML = '<span class="text-gray-500">No entries found</span>';
          } else {
            paginationInfoText.innerHTML = `
                          <span class="font-medium">Showing ${start} to ${end}</span>
                          <span class="text-gray-500">of ${total} entries</span>
                      `;
          }
        }
      }

      // Clear pagination
      if (pagination) {
        pagination.innerHTML = '';
      }

      // Don't show pagination if no data or only one page
      if (total === 0 || totalPages <= 1) {
        console.log('Pagination not needed:', { total, totalPages });
        return;
      }

      // Ensure currentPage is valid
      currentPage = Math.max(1, Math.min(currentPage, totalPages));

      // Previous button
      const prevLi = document.createElement('li');
      const prevDisabled = currentPage === 1;
      prevLi.innerHTML = `<button onclick="loadBroadcastLogs(${currentPage - 1})" ${prevDisabled ? 'disabled' : ''} class="relative inline-flex items-center px-3 py-2 rounded-l-lg text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 ${prevDisabled ? 'opacity-50 cursor-not-allowed' : ''}">
              <i class="fas fa-chevron-left"></i>
          </button>`;
      pagination.appendChild(prevLi);

      // Page numbers
      const maxPages = 5;
      let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
      let endPage = Math.min(totalPages, startPage + maxPages - 1);

      if (endPage - startPage < maxPages - 1) {
        startPage = Math.max(1, endPage - maxPages + 1);
      }

      for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        const isActive = i === currentPage;
        li.innerHTML = `<button onclick="loadBroadcastLogs(${i})" class="relative inline-flex items-center px-4 py-2 text-sm font-medium transition-all duration-200 ${isActive ? 'z-10 bg-primary-500 border-primary-500 text-white shadow-sm' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50 hover:shadow-sm'}">
                  ${i}
              </button>`;
        pagination.appendChild(li);
      }

      // Next button
      const nextLi = document.createElement('li');
      const nextDisabled = currentPage === totalPages;
      nextLi.innerHTML = `<button onclick="loadBroadcastLogs(${currentPage + 1})" ${nextDisabled ? 'disabled' : ''} class="relative inline-flex items-center px-3 py-2 rounded-r-lg text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 ${nextDisabled ? 'opacity-50 cursor-not-allowed' : ''}">
              <i class="fas fa-chevron-right"></i>
          </button>`;
      pagination.appendChild(nextLi);

      console.log('Pagination rendered successfully');
    }

    function viewBroadcastDetail(broadcastId) {
      // Since the API returns array of message details, we need to fetch the detail
      showLoading();

      fetch(`/qontak/broadcast-detail/${broadcastId}`)
        .then(response => response.json())
        .then(data => {
          hideLoading();
          if (data.success) {
            // Update modal broadcast ID
            updateModalBroadcastId(broadcastId);

            // Show modal with details
            const modal = document.getElementById('broadcastDetailModal');
            modal.classList.remove('hidden');
            displayBroadcastDetail(data.data);
          } else {
            showError(data.message || 'Failed to load broadcast details');
          }
        })
        .catch(error => {
          hideLoading();
          showError('Failed to load broadcast details: ' + error.message);
        });
    }

    function displayBroadcastDetail(data) {
      const content = document.getElementById('broadcastDetailContent');

      console.log('Broadcast detail data:', data);

      // Handle the actual Qontak WhatsApp broadcast API response structure
      // The API returns an array of message objects or single object
      const messages = Array.isArray(data) ? data : [data];

      if (messages.length === 0) {
        content.innerHTML = '<div class="text-center py-8 text-gray-500">No broadcast details available</div>';
        return;
      }

      // Get the first message as reference
      const broadcast = messages[0];

      // Extract contact name from contact_extra
      let contactName = 'Unknown';
      if (broadcast.contact_extra) {
        if (typeof broadcast.contact_extra === 'string') {
          try {
            const contactExtraParsed = JSON.parse(broadcast.contact_extra);
            contactName = contactExtraParsed.name || contactExtraParsed.full_name || contactExtraParsed.contact_name || broadcast.contact_full_name || 'Unknown';
          } catch (e) {
            contactName = broadcast.contact_extra || broadcast.contact_full_name || 'Unknown';
          }
        } else if (typeof broadcast.contact_extra === 'object') {
          contactName = broadcast.contact_extra.name || broadcast.contact_extra.full_name || broadcast.contact_extra.contact_name || broadcast.contact_full_name || 'Unknown';
        }
      } else {
        contactName = broadcast.contact_full_name || 'Unknown';
      }

      // Extract message status counts from messages_response
      const statusCount = broadcast.messages_response || {};

      // Calculate status counts dynamically
      const sentCount = statusCount.sent?.statuses?.length || 0;
      const deliveredCount = statusCount.delivered?.statuses?.length || 0;
      const readCount = statusCount.read?.statuses?.length || 0;

      content.innerHTML = `
              <div class="space-y-6">
                  <!-- Message Information -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="bg-gray-50 rounded-lg p-4">
                          <h4 class="text-sm font-medium text-gray-900 mb-3">Message Information</h4>
                          <dl class="space-y-2">
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Message ID:</dt>
                                  <dd class="text-sm text-gray-900 font-mono text-xs">${broadcast.id || 'N/A'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Broadcast ID:</dt>
                                  <dd class="text-sm text-gray-900 font-mono text-xs">${broadcast.messages_broadcast_id || 'N/A'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Contact Name:</dt>
                                  <dd class="text-sm text-gray-900">${contactName}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Phone Number:</dt>
                                  <dd class="text-sm text-gray-900">${broadcast.contact_phone_number || 'N/A'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Status:</dt>
                                  <dd><span class="status-badge status-${(broadcast.status || 'pending').toLowerCase()}">${broadcast.status || 'pending'}</span></dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Channel Integration ID:</dt>
                                  <dd class="text-sm text-gray-900 font-mono text-xs">${broadcast.channel_integration_id || 'N/A'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Is Pacing:</dt>
                                  <dd class="text-sm text-gray-900">${broadcast.is_pacing ? 'Yes' : 'No'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Created At:</dt>
                                  <dd class="text-sm text-gray-900">${broadcast.created_at ? new Date(broadcast.created_at).toLocaleString() : 'N/A'}</dd>
                              </div>
                          </dl>
                      </div>

                      <!-- Message Status -->
                      <div class="bg-gray-50 rounded-lg p-4">
                          <h4 class="text-sm font-medium text-gray-900 mb-3">Message Status</h4>
                          <dl class="space-y-2">
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Sent:</dt>
                                  <dd class="text-sm text-gray-900">${sentCount}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Delivered:</dt>
                                  <dd class="text-sm text-gray-900">${deliveredCount}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Read:</dt>
                                  <dd class="text-sm text-gray-900">${readCount}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Total Statuses:</dt>
                                  <dd class="text-sm text-gray-900">${sentCount + deliveredCount + readCount}</dd>
                              </div>
                          </dl>
                      </div>
                  </div>

                  <!-- Message Details -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="bg-gray-50 rounded-lg p-4">
                          <h4 class="text-sm font-medium text-gray-900 mb-3">Message Details</h4>
                          <dl class="space-y-2">
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">WhatsApp Message ID:</dt>
                                  <dd class="text-sm text-gray-900 font-mono text-xs break-all">${broadcast.whatsapp_message_id || 'N/A'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Organization ID:</dt>
                                  <dd class="text-sm text-gray-900 font-mono text-xs">${broadcast.organization_id || 'N/A'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Error Message:</dt>
                                  <dd class="text-sm text-gray-900">${broadcast.whatsapp_error_message || 'N/A'}</dd>
                              </div>
                              <div class="flex justify-between">
                                  <dt class="text-sm font-medium text-gray-500">Webhook ID:</dt>
                                  <dd class="text-sm text-gray-900 font-mono text-xs">${statusCount.sent?.webhook || statusCount.delivered?.webhook || statusCount.read?.webhook || 'N/A'}</dd>
                              </div>
                          </dl>
                      </div>

                      <div class="bg-gray-50 rounded-lg p-4">
                          <h4 class="text-sm font-medium text-gray-900 mb-3">Message Content</h4>
                          <div class="space-y-3">
                              ${broadcast.messages ? `
                                  ${broadcast.messages.header && (broadcast.messages.header.text || broadcast.messages.header.template?.length > 0) ? `
                                      <div>
                                          <dt class="text-sm font-medium text-gray-500 mb-1">Header:</dt>
                                          <dd class="text-sm text-gray-900 bg-white p-2 rounded border">${broadcast.messages.header?.text || (broadcast.messages.header.template ? broadcast.messages.header.template.join(', ') : 'N/A')}</dd>
                                      </div>
                                  ` : ''}
                                  <div>
                                      <dt class="text-sm font-medium text-gray-500 mb-1">Body:</dt>
                                      <dd class="text-sm text-gray-900 bg-white p-2 rounded border max-h-32 overflow-y-auto">${broadcast.messages.body?.text || JSON.stringify(broadcast.messages.body) || 'N/A'}</dd>
                                  </div>
                                  ${broadcast.messages.buttons && broadcast.messages.buttons.template ? `
                                      <div>
                                          <dt class="text-sm font-medium text-gray-500 mb-1">Buttons:</dt>
                                          <dd class="text-sm text-gray-900 bg-white p-2 rounded border">${broadcast.messages.buttons.template.join(', ') || 'N/A'}</dd>
                                      </div>
                                  ` : ''}
                              ` : '<div class="text-sm text-gray-500">No message content available</div>'}
                          </div>
                      </div>
                  </div>

                  <!-- Response Details -->
                  <div class="bg-gray-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Response Details</h4>
                      <div class="overflow-x-auto">
                          <table class="min-w-full divide-y divide-gray-200">
                              <thead class="bg-gray-100">
                                  <tr>
                                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Response Type</th>
                                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statuses Count</th>
                                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Webhook ID</th>
                                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                                  </tr>
                              </thead>
                              <tbody class="bg-white divide-y divide-gray-200">
                                  ${broadcast.messages_response ? Object.entries(broadcast.messages_response).map(([type, response]) => {
        const statusesCount = response.statuses?.length || 0;
        const webhookId = response.webhook || 'N/A';
        const contactsCount = response.contacts?.length || 0;
        const details = contactsCount > 0 ? `${contactsCount} contacts` : 'No contacts';
        return `
                                          <tr>
                                              <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">${type}</td>
                                              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${statusesCount}</td>
                                              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 font-mono text-xs">${webhookId}</td>
                                              <td class="px-4 py-2 text-sm text-gray-900">${details}</td>
                                          </tr>
                                      `;
      }).join('') : '<tr><td colspan="4" class="px-4 py-2 text-sm text-center text-gray-500">No response details available</td></tr>'}
                              </tbody>
                          </table>
                      </div>
                  </div>

                  <!-- Contact Information -->
                  ${statusCount.contacts && statusCount.contacts.length > 0 ? `
                      <div class="bg-gray-50 rounded-lg p-4">
                          <h4 class="text-sm font-medium text-gray-900 mb-3">Contact Information</h4>
                          <div class="overflow-x-auto">
                              <table class="min-w-full divide-y divide-gray-200">
                                  <thead class="bg-gray-100">
                                      <tr>
                                          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Input Number</th>
                                          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">WhatsApp ID</th>
                                      </tr>
                                  </thead>
                                  <tbody class="bg-white divide-y divide-gray-200">
                                      ${statusCount.contacts.map((contact, index) => `
                                          <tr>
                                              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${contact.input || 'N/A'}</td>
                                              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${contact.wa_id || 'N/A'}</td>
                                          </tr>
                                      `).join('')}
                                  </tbody>
                              </table>
                          </div>
                      </div>
                  ` : ''}

                  <!-- Raw Response -->
                  <div class="bg-gray-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Raw Response Data</h4>
                      <pre class="text-xs bg-gray-100 p-3 rounded overflow-x-auto max-h-64">${JSON.stringify(broadcast, null, 2)}</pre>
                  </div>
              </div>
          `;
    }

    function applyFilters() {
      const status = document.getElementById('statusFilter').value;
      const channel = document.getElementById('channelFilter').value;

      currentFilters = {};
      if (status) currentFilters.status = status;
      if (channel) currentFilters.channel = channel;

      loadBroadcastLogs(1);
    }

    // Helper functions for the enhanced detail view
    function getStatusColor(status) {
      const colors = {
        'sent': 'blue',
        'delivered': 'green',
        'read': 'purple',
        'failed': 'red',
        'pending': 'yellow'
      };
      return colors[status] || 'gray';
    }

    function getStatusIcon(status) {
      const icons = {
        'sent': 'fa-paper-plane',
        'delivered': 'fa-check-circle',
        'read': 'fa-eye',
        'failed': 'fa-exclamation-circle',
        'pending': 'fa-clock'
      };
      return icons[status] || 'fa-info-circle';
    }

    function exportLog(broadcastId) {
      // Fetch the broadcast detail data first
      showLoading();

      fetch(`/qontak/broadcast-detail/${broadcastId}`)
        .then(response => response.json())
        .then(data => {
          hideLoading();
          if (data.success) {
            const messages = Array.isArray(data.data) ? data.data : [data.data];
            const messageLog = messages[0]; // Get first message for export

            // Extract contact name from contact_extra
            let contactName = 'Unknown';
            if (messageLog.contact_extra) {
              if (typeof messageLog.contact_extra === 'string') {
                try {
                  const contactExtraParsed = JSON.parse(messageLog.contact_extra);
                  contactName = contactExtraParsed.name || contactExtraParsed.full_name || contactExtraParsed.contact_name || messageLog.contact_full_name || 'Unknown';
                } catch (e) {
                  contactName = messageLog.contact_extra || messageLog.name || 'Unknown';
                }
              } else if (typeof messageLog.contact_extra === 'object') {
                contactName = messageLog.contact_extra.name || messageLog.contact_extra.name || messageLog.contact_extra.contact_name || messageLog.contact_full_name || 'Unknown';
              }
            } else {
              contactName = messageLog.contact_full_name || 'Unknown';
            }

            // Create a comprehensive JSON string of the log data
            const logData = {
              export_info: {
                exported_at: new Date().toISOString(),
                broadcast_id: broadcastId,
                total_messages: messages.length
              },
              message: {
                id: messageLog.id,
                broadcast_id: messageLog.messages_broadcast_id,
                status: messageLog.status,
                contact: {
                  name: contactName,
                  phone: messageLog.contact_phone_number,
                  whatsapp_id: messageLog.messages_response?.contacts?.[0]?.wa_id || 'N/A'
                },
                content: {
                  header: messageLog.messages?.header,
                  body: messageLog.messages?.body,
                  buttons: messageLog.messages?.buttons
                },
                response: messageLog.messages_response,
                metadata: {
                  channel_integration_id: messageLog.channel_integration_id,
                  organization_id: messageLog.organization_id,
                  whatsapp_message_id: messageLog.whatsapp_message_id,
                  is_pacing: messageLog.is_pacing,
                  created_at: messageLog.created_at,
                  error_message: messageLog.whatsapp_error_message
                }
              },
              all_messages: messages.map((msg, index) => {
                // Extract contact name from contact_extra for each message
                let msgContactName = 'Unknown';
                if (msg.contact_extra) {
                  if (typeof msg.contact_extra === 'string') {
                    try {
                      const contactExtraParsed = JSON.parse(msg.contact_extra);
                      msgContactName = contactExtraParsed.name || contactExtraParsed.full_name || contactExtraParsed.contact_name || msg.contact_full_name || 'Unknown';
                    } catch (e) {
                      msgContactName = msg.contact_extra || msg.contact_full_name || 'Unknown';
                    }
                  } else if (typeof msg.contact_extra === 'object') {
                    msgContactName = msg.contact_extra.name || msg.contact_extra.full_name || msg.contact_extra.contact_name || msg.contact_full_name || 'Unknown';
                  }
                } else {
                  msgContactName = msg.contact_full_name || 'Unknown';
                }

                return {
                  index: index + 1,
                  id: msg.id,
                  status: msg.status,
                  contact_name: msgContactName,
                  phone: msg.contact_phone_number,
                  created_at: msg.created_at
                };
              })
            };

            // Create and download JSON file
            const dataStr = JSON.stringify(logData, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);

            const exportFileDefaultName = `broadcast-log-${broadcastId}-${new Date().toISOString().split('T')[0]}.json`;

            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();

            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-up';
            successDiv.innerHTML = `
                          <div class="flex items-center">
                              <i class="fas fa-check-circle mr-2"></i>
                              <span>Log exported successfully!</span>
                          </div>
                      `;
            document.body.appendChild(successDiv);

            // Remove after 3 seconds
            setTimeout(() => {
              successDiv.remove();
            }, 3000);
          } else {
            showError('Failed to fetch data for export: ' + data.message);
          }
        })
        .catch(error => {
          hideLoading();
          showError('Failed to export log: ' + error.message);
        });
    }

    function refreshLog(broadcastId) {
      // Close modal first
      closeModal();

      // Reload the detail
      viewBroadcastDetail(broadcastId);
    }

    function refreshLog(broadcastId) {
      // Close modal first
      closeModal();

      // Reload the detail
      viewBroadcastDetail(broadcastId);
    }

    function clearFilters() {
      document.getElementById('statusFilter').value = '';
      document.getElementById('channelFilter').value = '';
      currentFilters = {};
      loadBroadcastLogs(1);
    }

    function refreshLogs() {
      loadBroadcastLogs(currentPage);
    }

    function closeModal() {
      const modal = document.getElementById('broadcastDetailModal');
      modal.classList.add('hidden');
    }

    function exportModalDetail() {
      const broadcastId = document.getElementById('modalBroadcastId').textContent;
      if (broadcastId && broadcastId !== 'N/A') {
        exportLog(broadcastId);
      } else {
        showError('No broadcast ID available for export');
      }
    }

    // Helper function to update modal broadcast ID
    function updateModalBroadcastId(broadcastId) {
      const modalBroadcastIdElement = document.getElementById('modalBroadcastId');
      if (modalBroadcastIdElement) {
        modalBroadcastIdElement.textContent = broadcastId;
      }
    }

    function showLoading() {
      const loadingIndicator = document.getElementById('loadingIndicator');
      const errorMessage = document.getElementById('errorMessage');
      const broadcastLogsTable = document.querySelector('.bg-white.rounded-2xl.shadow-soft.overflow-hidden');

      if (loadingIndicator) {
        loadingIndicator.classList.remove('hidden');
      }
      if (errorMessage) {
        errorMessage.classList.add('hidden');
      }
      if (broadcastLogsTable) {
        broadcastLogsTable.style.display = 'none';
      }
    }

    function hideLoading() {
      const loadingIndicator = document.getElementById('loadingIndicator');
      const broadcastLogsTable = document.querySelector('.bg-white.rounded-2xl.shadow-soft.overflow-hidden');

      if (loadingIndicator) {
        loadingIndicator.classList.add('hidden');
      }
      if (broadcastLogsTable) {
        broadcastLogsTable.style.display = 'block';
      }
    }

    function showError(message, showRetry = false) {
      const errorText = document.getElementById('errorText');
      const errorMessage = document.getElementById('errorMessage');
      const errorActions = document.getElementById('errorActions');

      if (errorText) {
        errorText.textContent = message;
      }

      // Update error actions based on error type
      if (errorActions) {
        if (message.includes('Authentication failed') || message.includes('401')) {
          errorActions.innerHTML = `
                      <div class="mt-4 flex gap-2">
                          <button onclick="testEndpoints()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                              <i class="fas fa-plug mr-2"></i>Test Connection
                          </button>
                          <button onclick="location.reload()" class="page-button bg-secondary text-secondary-foreground hover:bg-secondary/80">
                              <i class="fas fa-sync-alt mr-2"></i>Refresh Page
                          </button>
                      </div>
                  `;
        } else if (showRetry) {
          errorActions.innerHTML = `
                      <div class="mt-4 flex gap-2">
                          <button onclick="location.reload()" class="page-button bg-secondary text-secondary-foreground hover:bg-secondary/80">
                              <i class="fas fa-redo mr-2"></i>Retry
                          </button>
                          <button onclick="hideError()" class="page-button bg-destructive text-destructive-foreground hover:bg-destructive/90">
                              <i class="fas fa-times mr-2"></i>Dismiss
                          </button>
                      </div>
                  `;
        } else {
          errorActions.innerHTML = '';
        }
      }

      if (errorMessage) {
        errorMessage.classList.remove('hidden');
      }

      const loadingIndicator = document.getElementById('loadingIndicator');
      const broadcastLogsTable = document.querySelector('.bg-white.rounded-2xl.shadow-soft.overflow-hidden');

      if (loadingIndicator) {
        loadingIndicator.classList.add('hidden');
      }
      if (broadcastLogsTable) {
        broadcastLogsTable.style.display = 'none';
      }

      // Add error animation
      if (errorMessage) {
        errorMessage.classList.add('animate-pulse');
        setTimeout(() => {
          errorMessage.classList.remove('animate-pulse');
        }, 3000);
      }
    }

    function hideError() {
      const errorMessage = document.getElementById('errorMessage');
      if (errorMessage) {
        errorMessage.classList.add('hidden');
      }
    }

    function testConnection() {
      showLoading();
      hideError(); // Clear any previous errors

      fetch('/qontak/test-connection')
        .then(response => response.json())
        .then(data => {
          hideLoading();
          console.log('Connection test results:', data);

          const messageDiv = document.createElement('div');

          if (data.success) {
            messageDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-slide-up max-w-md';
            messageDiv.innerHTML = `
                          <div class="flex items-start">
                              <div class="flex-shrink-0">
                                  <i class="fas fa-check-circle text-2xl"></i>
                              </div>
                              <div class="ml-3">
                                  <h4 class="font-semibold">Connection Successful</h4>
                                  <p class="mt-1 text-sm">${data.message}</p>
                              </div>
                              <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-200 hover:text-white">
                                  <i class="fas fa-times"></i>
                              </button>
                          </div>
                      `;
          } else {
            messageDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-slide-up max-w-md';
            messageDiv.innerHTML = `
                          <div class="flex items-start">
                              <div class="flex-shrink-0">
                                  <i class="fas fa-exclamation-circle text-2xl"></i>
                              </div>
                              <div class="ml-3">
                                  <h4 class="font-semibold">Connection Failed</h4>
                                  <p class="mt-1 text-sm">${data.message}</p>
                              </div>
                              <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-200 hover:text-white">
                                  <i class="fas fa-times"></i>
                              </button>
                          </div>
                      `;
          }

          document.body.appendChild(messageDiv);

          // Auto-remove after 5 seconds
          setTimeout(() => {
            if (messageDiv.parentNode) {
              messageDiv.remove();
            }
          }, 5000);
        })
        .catch(error => {
          hideLoading();
          showError('Failed to test connection: ' + error.message, true);
        });
    }

    function testEndpoints() {
      showLoading();
      hideError(); // Clear any previous errors

      fetch('/qontak/test-endpoints')
        .then(response => response.json())
        .then(data => {
          hideLoading();
          console.log('Endpoint test results:', data);

          if (data.success) {
            // Display results in debug panel
            const debugResponse = document.getElementById('debugResponse');
            const debugStructure = document.getElementById('debugStructure');

            debugResponse.textContent = JSON.stringify(data.data, null, 2);

            // Create summary
            const summary = {
              working_endpoints: [],
              failed_endpoints: []
            };

            for (let endpoint in data.data.list) {
              if (data.data.list[endpoint].success) {
                summary.working_endpoints.push(endpoint);
              } else {
                summary.failed_endpoints.push({
                  endpoint: endpoint,
                  error: data.data.list[endpoint].message
                });
              }
            }

            debugStructure.textContent = JSON.stringify(summary, null, 2);

            // Show debug panel
            document.getElementById('debugPanel').style.display = 'block';

            // Show success message with better UX
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-slide-up max-w-md';
            successMessage.innerHTML = `
                          <div class="flex items-start">
                              <div class="flex-shrink-0">
                                  <i class="fas fa-check-circle text-2xl"></i>
                              </div>
                              <div class="ml-3">
                                  <h4 class="font-semibold">Endpoint Testing Completed</h4>
                                  <div class="mt-1 text-sm">
                                      <p>✅ Working endpoints: ${summary.working_endpoints.length}</p>
                                      <p>❌ Failed endpoints: ${summary.failed_endpoints.length}</p>
                                      <p class="mt-2 text-xs opacity-90">Check debug panel for detailed results</p>
                                  </div>
                              </div>
                              <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-200 hover:text-white">
                                  <i class="fas fa-times"></i>
                              </button>
                          </div>
                      `;
            document.body.appendChild(successMessage);

            // Auto-remove after 5 seconds
            setTimeout(() => {
              if (successMessage.parentNode) {
                successMessage.remove();
              }
            }, 5000);
          } else {
            showError('Endpoint testing failed: ' + data.message);
          }
        })
        .catch(error => {
          hideLoading();
          showError('Failed to test endpoints: ' + error.message);
        });
    }

    function toggleDebug() {
      const debugPanel = document.getElementById('debugPanel');
      debugPanel.style.display = debugPanel.style.display === 'none' ? 'block' : 'none';
    }

    function updateDebugInfo(data) {
      // Show raw API response
      const debugResponse = document.getElementById('debugResponse');
      debugResponse.textContent = JSON.stringify(data, null, 2);

      // Show data structure analysis
      const debugStructure = document.getElementById('debugStructure');
      const structure = analyzeDataStructure(data);
      debugStructure.textContent = JSON.stringify(structure, null, 2);
    }

    function analyzeDataStructure(obj, path = '') {
      let result = {};

      for (let key in obj) {
        const currentPath = path ? `${path}.${key}` : key;
        const value = obj[key];

        if (Array.isArray(value)) {
          result[currentPath] = {
            type: 'array',
            length: value.length,
            sample: value.length > 0 ? typeof value[0] : 'empty'
          };
        } else if (typeof value === 'object' && value !== null) {
          result[currentPath] = {
            type: 'object',
            keys: Object.keys(value)
          };
        } else {
          result[currentPath] = {
            type: typeof value,
            value: value
          };
        }
      }

      return result;
    }

    // Debug function to check pagination state
    function debugPagination() {
      console.log('=== Pagination Debug Info ===');
      console.log('Current Page:', currentPage);
      console.log('Current Filters:', currentFilters);
      console.log('Current Pagination:', window.currentPagination);
      console.log('Limit Filter Value:', document.getElementById('limitFilter').value);
      console.log('Pagination Container:', document.getElementById('paginationContainer'));
      console.log('Pagination Element:', document.getElementById('pagination'));
      console.log('Pagination Info Text:', document.getElementById('paginationInfoText')?.innerHTML);

      // Try to get current API response for debugging
      fetch(`/qontak/broadcast-history?limit=5&offset=0`)
        .then(response => response.json())
        .then(data => {
          console.log('Sample API Response:', data);
          analyzeDataStructure(data);
        })
        .catch(error => {
          console.error('Debug API Error:', error);
        });
    }
  </script>

  <!-- Maintenance modal (Enhanced) -->
  <div id="sendMaintenanceModal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 p-6 overflow-hidden flex flex-col max-h-[90vh]">
      <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <h3 class="text-lg font-semibold text-gray-900">Send Maintenance Broadcast</h3>
        <button onclick="closeMaintenanceModal()" class="text-gray-500 hover:text-gray-700" aria-label="Close">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Filters & Actions -->
      <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4 flex-shrink-0">
        <div class="relative flex-1">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </span>
          <input type="text" id="maintenanceSearch" onkeyup="filterMaintenanceCustomers()"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Search customers by name or phone...">
        </div>
        <div class="flex items-center space-x-3">
          <span id="maintenanceSelectedCount" class="text-sm text-gray-600 font-medium">0 selected</span>
          <button id="btnBroadcastMaintenance" onclick="sendMaintenanceBroadcast()"
            class="btn-enhanced inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-soft transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-paper-plane mr-2"></i> Send Broadcast
          </button>
        </div>
      </div>

      <!-- Recipients Table -->
      <div class="overflow-y-auto flex-grow border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 sticky top-0 z-10">
            <tr>
              <th class="px-4 py-2 text-left">
                <input type="checkbox" id="selectAllMaintenance" onclick="toggleAllMaintenance(this)"
                  class="rounded text-primary-600 focus:ring-primary-500">
              </th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody id="maintenanceRecipientsTableBody" class="divide-y divide-gray-200">
            <!-- Rows will be injected here -->
          </tbody>
        </table>
      </div>

      <!-- Loading Overlay for Modal -->
      <div id="maintenanceLoadingOverlay"
        class="hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
        <div class="flex flex-col items-center">
          <div class="w-10 h-10 border-4 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
          <p class="mt-2 text-gray-700 font-medium" id="maintenanceLoadingText">Loading customers...</p>
        </div>
      </div>

      <div class="flex justify-between items-center mt-4 flex-shrink-0">
        <div class="flex items-center space-x-4">
          <span id="maintenanceTotal" class="text-sm text-gray-500">Total: 0</span>
          <div id="maintenancePagination"
            class="flex items-center space-x-2 bg-gray-50 px-2 py-1 rounded-md border border-gray-200">
            <button onclick="changeMaintenancePage(-1)" id="btnPrevPage"
              class="p-1 px-2 text-gray-600 hover:bg-gray-200 rounded disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
              <i class="fas fa-chevron-left text-xs"></i>
            </button>
            <span id="currentPageDisplay"
              class="text-xs font-bold text-gray-700 min-w-[80px] text-center uppercase tracking-wider">Page 1 / 1</span>
            <button onclick="changeMaintenancePage(1)" id="btnNextPage"
              class="p-1 px-2 text-gray-600 hover:bg-gray-200 rounded disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
              <i class="fas fa-chevron-right text-xs"></i>
            </button>
          </div>
        </div>
        <div class="flex space-x-2">
          <button onclick="closeMaintenanceModal()"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let allMaintenanceCustomers = [];
    let filteredMaintenanceCustomers = [];
    let selectedCustomerData = new Map(); // Store as Map { id: {name, number} }
    let maintenancePageSize = 50;
    let maintenanceCurrentPage = 1;

    function openMaintenanceModal() {
      const m = document.getElementById('sendMaintenanceModal');
      if (m) m.classList.remove('hidden');
      // Clear search and selection
      document.getElementById('maintenanceSearch').value = '';
      document.getElementById('selectAllMaintenance').checked = false;
      selectedCustomerData.clear();
      maintenanceCurrentPage = 1;
      renderMaintenanceRecipientsV2();
    }

    function closeMaintenanceModal() {
      const m = document.getElementById('sendMaintenanceModal');
      if (m) m.classList.add('hidden');
    }

    function renderMaintenanceRecipientsV2() {
      const loadingOverlay = document.getElementById('maintenanceLoadingOverlay');
      const loadingText = document.getElementById('maintenanceLoadingText');

      if (loadingOverlay) {
        loadingText.textContent = 'Loading customers...';
        loadingOverlay.classList.remove('hidden');
      }

      fetch('/maintenance-customers')
        .then(res => res.json())
        .then(data => {
          if (loadingOverlay) loadingOverlay.classList.add('hidden');

          allMaintenanceCustomers = (data && data.success && Array.isArray(data.data)) ? data.data : [];
          filteredMaintenanceCustomers = [...allMaintenanceCustomers];
          maintenanceCurrentPage = 1;
          displayMaintenanceCustomers();
        })
        .catch(err => {
          if (loadingOverlay) loadingOverlay.classList.add('hidden');
          showToast('Failed to load customers: ' + err.message, 'error');
        });
    }

    function displayMaintenanceCustomers() {
      const listEl = document.getElementById('maintenanceRecipientsTableBody');
      const totalEl = document.getElementById('maintenanceTotal');
      const pageDisplay = document.getElementById('currentPageDisplay');
      const btnPrev = document.getElementById('btnPrevPage');
      const btnNext = document.getElementById('btnNextPage');

      if (!listEl) return;

      const totalItems = filteredMaintenanceCustomers.length;
      const totalPages = Math.ceil(totalItems / maintenancePageSize) || 1;

      if (maintenanceCurrentPage > totalPages) maintenanceCurrentPage = totalPages;
      if (maintenanceCurrentPage < 1) maintenanceCurrentPage = 1;

      const start = (maintenanceCurrentPage - 1) * maintenancePageSize;
      const end = start + maintenancePageSize;
      const pageItems = filteredMaintenanceCustomers.slice(start, end);

      if (totalEl) totalEl.textContent = `Total: ${totalItems}`;
      if (pageDisplay) pageDisplay.textContent = `PAGE ${maintenanceCurrentPage} / ${totalPages}`;
      if (btnPrev) btnPrev.disabled = maintenanceCurrentPage === 1;
      if (btnNext) btnNext.disabled = maintenanceCurrentPage === totalPages;

      const escapeHtml = (s) => String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

      if (pageItems.length === 0) {
        listEl.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No customers found</td></tr>';
        updateSelectedUI();
        return;
      }

      listEl.innerHTML = pageItems.map(it => {
        const status = (it.status || 'never_sent').toLowerCase();
        const statusText = status.replace('_', ' ');
        const isSelected = selectedCustomerData.has(String(it.id));
        let badgeClass = 'status-pending';

        if (status === 'sent' || status === 'delivered' || status === 'read' || status === 'done') {
          badgeClass = 'status-delivered';
        } else if (status === 'failed' || status === 'error') {
          badgeClass = 'status-failed';
        } else if (status === 'never_sent') {
          badgeClass = 'bg-gray-100 text-gray-600 border border-gray-200';
        } else if (status === 'already_sent') {
          badgeClass = 'bg-blue-100 text-blue-600 border border-blue-200';
        }

        return `
            <tr class="hover:bg-gray-50 transition-colors ${isSelected ? 'bg-primary-50' : ''}">
              <td class="px-4 py-2 whitespace-nowrap">
                <input type="checkbox" class="customer-checkbox rounded text-primary-600 focus:ring-primary-500"
                  data-id="${it.id}" data-name="${escapeHtml(it.name)}" data-number="${escapeHtml(it.number)}"
                  ${isSelected ? 'checked' : ''} onchange="toggleCustomerSelection(this)">
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">${it.id ?? ''}</td>
              <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(it.name)}</td>
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">${escapeHtml(it.number)}</td>
              <td class="px-4 py-2 whitespace-nowrap text-sm">
                <span class="status-badge ${badgeClass} inline-flex items-center">
                  ${status === 'never_sent' ? '<i class="fas fa-clock mr-1 text-gray-400"></i>' : ''}
                  ${status === 'already_sent' ? '<i class="fas fa-check-double mr-1"></i>' : ''}
                  ${statusText.charAt(0).toUpperCase() + statusText.slice(1)}
                </span>
              </td>
            </tr>`;
      }).join('');

      updateSelectedUI();
    }

    function changeMaintenancePage(delta) {
      maintenanceCurrentPage += delta;
      displayMaintenanceCustomers();
      // Scroll table to top
      const tableContainer = document.querySelector('#sendMaintenanceModal .overflow-y-auto');
      if (tableContainer) tableContainer.scrollTop = 0;
    }

    function toggleCustomerSelection(checkbox) {
      const id = String(checkbox.getAttribute('data-id'));
      const name = checkbox.getAttribute('data-name');
      const number = checkbox.getAttribute('data-number');

      if (checkbox.checked) {
        selectedCustomerData.set(id, { name, number });
        checkbox.closest('tr').classList.add('bg-primary-50');
      } else {
        selectedCustomerData.delete(id);
        checkbox.closest('tr').classList.remove('bg-primary-50');
      }
      updateSelectedUI();
    }

    function filterMaintenanceCustomers() {
      const query = document.getElementById('maintenanceSearch').value.toLowerCase();
      filteredMaintenanceCustomers = allMaintenanceCustomers.filter(c =>
        (c.name && c.name.toLowerCase().includes(query)) ||
        (c.number && String(c.number).includes(query)) ||
        (c.id && String(c.id).includes(query))
      );
      maintenanceCurrentPage = 1;
      displayMaintenanceCustomers();
    }

    function toggleAllMaintenance(source) {
      if (source.checked) {
        // Select ALL customers from the currently filtered list (across all pages)
        filteredMaintenanceCustomers.forEach(it => {
          selectedCustomerData.set(String(it.id), {
            name: it.name,
            number: it.number
          });
        });
      } else {
        // Deselect everything
        selectedCustomerData.clear();
      }

      // Re-render current page to show the checked states
      displayMaintenanceCustomers();
    }

    function updateSelectedUI() {
      const selectedCount = selectedCustomerData.size;
      const countEl = document.getElementById('maintenanceSelectedCount');
      const btn = document.getElementById('btnBroadcastMaintenance');

      if (countEl) countEl.textContent = `${selectedCount} selected`;
      if (btn) btn.disabled = selectedCount === 0;

      // Update Select All checkbox state for the current page
      const pageCheckboxes = document.querySelectorAll('.customer-checkbox');
      const allCheckedOnPage = pageCheckboxes.length > 0 && Array.from(pageCheckboxes).every(cb => cb.checked);
      const selectAll = document.getElementById('selectAllMaintenance');
      if (selectAll) selectAll.checked = allCheckedOnPage;
    }

    function sendMaintenanceBroadcast() {
      const recipients = Array.from(selectedCustomerData.values());

      if (recipients.length === 0) {
        showToast('Please select at least one customer', 'warning');
        return;
      }

      if (!confirm(`Are you sure you want to send maintenance broadcast to ${recipients.length} customers?`)) {
        return;
      }

      const btn = document.getElementById('btnBroadcastMaintenance');
      const loadingOverlay = document.getElementById('maintenanceLoadingOverlay');
      const loadingText = document.getElementById('maintenanceLoadingText');

      if (btn) btn.disabled = true;
      if (loadingOverlay) {
        loadingOverlay.classList.remove('hidden');
      }

      // Chunking logic to prevent timeouts
      const chunkSize = 20;
      const chunks = [];
      for (let i = 0; i < recipients.length; i += chunkSize) {
        chunks.push(recipients.slice(i, i + chunkSize));
      }

      let totalSent = 0;
      let totalSkipped = 0;
      let totalFailed = 0;

      async function processChunks() {
        for (let i = 0; i < chunks.length; i++) {
          const chunk = chunks[i];
          const progress = Math.round(((i) / chunks.length) * 100);

          if (loadingText) {
            loadingText.innerHTML = `
                <div class="w-64">
                  <div class="text-center mb-2 font-bold text-primary-600">Sending Broadcast...</div>
                  <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                    <div class="bg-primary-600 h-2.5 rounded-full transition-all duration-300" style="width: ${progress}%"></div>
                  </div>
                  <div class="flex justify-between text-xs text-gray-500">
                    <span>Progress: ${progress}%</span>
                    <span>Chunk ${i + 1}/${chunks.length}</span>
                  </div>
                  <div class="mt-2 text-xs text-center text-gray-400">Processing ${totalSent + totalSkipped + totalFailed}/${recipients.length} recipients</div>
                </div>
              `;
          }

          try {
            const response = await fetch('/maintenance/broadcast', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({ recipients: chunk })
            });

            const data = await response.json();

            if (data.success) {
              const results = data.data || [];
              totalSent += results.filter(r => r.sent).length;
              totalSkipped += results.filter(r => r.status === 'already_sent').length;
              totalFailed += results.filter(r => !r.sent && r.status !== 'already_sent').length;
            } else {
              totalFailed += chunk.length;
            }
          } catch (err) {
            console.error('Chunk error:', err);
            totalFailed += chunk.length;
          }
        }

        // Final finish
        if (loadingOverlay) loadingOverlay.classList.add('hidden');
        if (btn) btn.disabled = false;

        let msg = `Broadcast complete. Sent: ${totalSent}.`;
        if (totalSkipped > 0) msg += ` Skipped: ${totalSkipped}.`;
        if (totalFailed > 0) msg += ` Failed: ${totalFailed}.`;

        showToast(msg, totalFailed > 0 ? 'warning' : 'success');

        selectedCustomerData.clear();
        renderMaintenanceRecipientsV2();
      }

      processChunks();
    }

    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      const bgClass = type === 'success' ? 'bg-green-500' : (type === 'warning' ? 'bg-yellow-500' : 'bg-red-500');
      const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle');

      toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-2xl z-[60] text-white animate-slide-up flex items-center ${bgClass}`;
      toast.innerHTML = `
          <i class="fas ${iconClass} mr-3 text-xl"></i>
          <span class="font-medium">${message}</span>
        `;
      document.body.appendChild(toast);
      setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        setTimeout(() => toast.remove(), 500);
      }, 4000);
    }
  </script>

@endsection
