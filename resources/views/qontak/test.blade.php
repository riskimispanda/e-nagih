@extends('layouts/contentNavbarLayout')

@section('title', 'Qontak API Dashboard')

@section('content')
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<div class="min-h-screen bg-gray-50">
  <!-- Header -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <h1 class="text-xl font-semibold text-gray-900">Qontak API Dashboard</h1>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <span class="w-2 h-2 mr-1 bg-green-400 rounded-full animate-pulse"></span>
            Connected
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
      <!-- Test Connection Card -->
      <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Test Connection</h3>
            <p class="text-sm text-gray-500">Verify API connectivity</p>
          </div>
        </div>
        <button onclick="testConnection()" class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Test Now
        </button>
        <div id="connectionResult" class="mt-3"></div>
      </div>

      <!-- Templates Card -->
      <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Templates</h3>
            <p class="text-sm text-gray-500">Get message templates</p>
          </div>
        </div>
        <button onclick="getTemplates()" class="mt-4 w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          Fetch Templates
        </button>
        <div id="templatesResult" class="mt-3"></div>
      </div>

      <!-- Account Info Card -->
      <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Account Info</h3>
            <p class="text-sm text-gray-500">View account details</p>
          </div>
        </div>
        <button onclick="getAccountInfo()" class="mt-4 w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Get Info
        </button>
        <div id="accountResult" class="mt-3"></div>
      </div>

      <!-- Debug Auth Card -->
      <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Debug Auth</h3>
            <p class="text-sm text-gray-500">Debug authentication</p>
          </div>
        </div>
        <button onclick="debugAuth()" class="mt-4 w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
          </svg>
          Debug
        </button>
        <div id="debugResult" class="mt-3"></div>
      </div>

      <!-- Broadcast Card -->
      <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Broadcast</h3>
            <p class="text-sm text-gray-500">Send bulk messages</p>
          </div>
        </div>
        <button onclick="document.getElementById('broadcastForm').scrollIntoView({behavior: 'smooth'})" class="mt-4 w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
          </svg>
          Start Broadcast
        </button>
      </div>
    </div>

    <!-- Message Sending Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Send to Customer -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Send to Customer</h2>
          <p class="text-sm text-gray-500 mt-1">Send message to existing customer</p>
        </div>
        <div class="p-6">
          <form id="sendMessageForm" class="space-y-4">
            <div>
              <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
              <select id="customer_id" name="customer_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Customer</option>
              </select>
            </div>
            <div>
              <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">Template (Optional)</label>
              <div class="relative">
                <select id="template_id" name="template_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Select Template</option>
                </select>
                <button type="button" onclick="loadTemplates()" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600" title="Refresh Templates">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                </button>
              </div>
              <!-- Selected Template Display -->
              <div id="selectedTemplateDisplay" class="mt-2 hidden">
                <div class="flex items-center space-x-2 text-sm mb-2">
                  <span class="text-gray-600">Selected:</span>
                  <div class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span id="selectedTemplateName">Template Name</span>
                    <span id="selectedTemplateLanguage" class="ml-1 text-xs opacity-75">(lang)</span>
                  </div>
                  <button type="button" onclick="clearTemplateSelection('template_id')" class="text-gray-400 hover:text-red-500" title="Clear Selection">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>

                <!-- Template Body Display -->
                <div id="templateBodyDisplay" class="hidden">
                  <div class="text-xs text-gray-600 mb-1">Template Body:</div>
                  <div class="bg-gray-900 text-green-400 p-3 rounded-lg font-mono text-xs overflow-x-auto">
                    <pre id="templateBodyContent" class="whitespace-pre-wrap"></pre>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
              <textarea id="message" name="message" rows="3" required placeholder="Enter your message here..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div>
              <label for="channel_id" class="block text-sm font-medium text-gray-700 mb-1">Channel ID (Optional)</label>
              <input type="text" id="channel_id" name="channel_id" placeholder="Channel ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
              </svg>
              Send Message
            </button>
          </form>
          <div id="messageResult" class="mt-4"></div>
        </div>
      </div>

      <!-- Send to Custom Number -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Send to Custom Number</h2>
          <p class="text-sm text-gray-500 mt-1">Send message to any phone number</p>
        </div>
        <div class="p-6">
          <form id="sendToNumberForm" class="space-y-4">
            <div>
              <label for="to_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <input type="text" id="to_number" name="to_number" required placeholder="628123456789" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
              <label for="custom_template_id" class="block text-sm font-medium text-gray-700 mb-1">Template (Optional)</label>
              <div class="relative">
                <select id="custom_template_id" name="template_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Select Template</option>
                </select>
                <button type="button" onclick="loadTemplates()" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600" title="Refresh Templates">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                </button>
              </div>
              <!-- Selected Template Display -->
              <div id="selectedCustomTemplateDisplay" class="mt-2 hidden">
                <div class="flex items-center space-x-2 text-sm mb-2">
                  <span class="text-gray-600">Selected:</span>
                  <div class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span id="selectedCustomTemplateName">Template Name</span>
                    <span id="selectedCustomTemplateLanguage" class="ml-1 text-xs opacity-75">(lang)</span>
                  </div>
                  <button type="button" onclick="clearTemplateSelection('custom_template_id')" class="text-gray-400 hover:text-red-500" title="Clear Selection">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>

                <!-- Template Body Display -->
                <div id="templateBodyDisplayCustom" class="hidden">
                  <div class="text-xs text-gray-600 mb-1">Template Body:</div>
                  <div class="bg-gray-900 text-green-400 p-3 rounded-lg font-mono text-xs overflow-x-auto">
                    <pre id="templateBodyContentCustom" class="whitespace-pre-wrap"></pre>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <label for="customMessage" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
              <textarea id="customMessage" name="message" rows="3" required placeholder="Enter your message here..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div>
              <label for="customChannelId" class="block text-sm font-medium text-gray-700 mb-1">Channel ID (Optional)</label>
              <input type="text" id="customChannelId" name="channel_id" placeholder="Channel ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </svg>
              Send to Number
            </button>
          </form>
          <div id="customNumberResult" class="mt-4"></div>
        </div>
      </div>
    </div>

    <!-- Broadcast Section -->
    <div class="mt-8 bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Broadcast Message</h2>
        <p class="text-sm text-gray-500 mt-1">Send broadcast to multiple recipients using template</p>
      </div>
      <div class="p-6">
        <form id="broadcastForm" class="space-y-6">
          <!-- Template Selection -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="broadcast_template_id" class="block text-sm font-medium text-gray-700 mb-1">Template *</label>
              <select id="broadcast_template_id" name="template_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Template</option>
              </select>
            </div>
            <div>
              <label for="broadcast_channel_id" class="block text-sm font-medium text-gray-700 mb-1">Channel ID *</label>
              <input type="text" id="broadcast_channel_id" name="channel_integration_id" required placeholder="601d491c-f5fa-4488-b48b-f0ad2284f0e8" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="broadcast_language" class="block text-sm font-medium text-gray-700 mb-1">Language</label>
              <select id="broadcast_language" name="language_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="id">Indonesian (id)</option>
                <option value="en">English (en)</option>
              </select>
            </div>
            <div>
              <label for="recipient_type" class="block text-sm font-medium text-gray-700 mb-1">Recipient Type</label>
              <select id="recipient_type" name="recipient_type" onchange="toggleRecipientType()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="selected">Selected Customers</option>
                <option value="custom">Custom Numbers</option>
              </select>
            </div>
          </div>

          <!-- Template Parameters -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Template Parameters</label>
            <div id="templateParams" class="space-y-2">
              <div class="flex gap-2 items-center">
                <input type="text" placeholder="Key (1)" class="param-key flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="1">
                <input type="text" placeholder="Parameter Name (nama)" class="param-name flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <input type="text" placeholder="Value (Paijo)" class="param-value flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="button" onclick="removeParam(this)" class="px-3 py-2 text-red-600 hover:text-red-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
              <div class="flex gap-2 items-center">
                <input type="text" placeholder="Key (2)" class="param-key flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="2">
                <input type="text" placeholder="Parameter Name (no_invoice)" class="param-name flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <input type="text" placeholder="Value (INV-123456)" class="param-value flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="button" onclick="removeParam(this)" class="px-3 py-2 text-red-600 hover:text-red-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
              <div class="flex gap-2 items-center">
                <input type="text" placeholder="Key (3)" class="param-key flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="3">
                <input type="text" placeholder="Parameter Name (total_budget)" class="param-name flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <input type="text" placeholder="Value (100.000.000)" class="param-value flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="button" onclick="removeParam(this)" class="px-3 py-2 text-red-600 hover:text-red-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
            <button type="button" onclick="addParamRow()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Parameter
            </button>
          </div>

          <!-- Selected Customers -->
          <div id="selectedCustomersSection">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Customers</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
              <div class="col-span-full">
                <input type="text" id="customerSearch" placeholder="Search customers..." onkeyup="filterCustomers()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-2">
              </div>
              <div id="customerCheckboxes" class="contents">
              </div>
            </div>
          </div>

          <!-- Custom Numbers -->
          <div id="customNumbersSection" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Numbers (One per line: Name, Number)</label>
            <textarea id="customNumbers" name="custom_numbers" rows="6" placeholder="Paijo,6282243613621&#10;Budi,6281234567890&#10;Siti,6289876543210" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            <p class="text-xs text-gray-500 mt-1">Format: Name,Phone Number (one per line)</p>
          </div>

          <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
            </svg>
            Send Broadcast
          </button>
        </form>
        <div id="broadcastResult" class="mt-4"></div>
      </div>
    </div>

    <!-- Response Display Modal -->
    <div id="responseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3">
          <h3 class="text-lg font-semibold text-gray-900">API Response</h3>
          <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div id="modalContent" class="mt-4">
          <!-- Content will be inserted here -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Memindahkan seluruh kode JavaScript ke file terpisah untuk menghindari masalah parsing Blade
// Atau menggunakan @verbatim directive untuk melindungi kode JavaScript
</script>

<script>
// Versi sederhana dari kode JavaScript untuk menghindari error parsing
document.addEventListener('DOMContentLoaded', function() {
  // Load customers for dropdown
  loadCustomers();
  loadCustomersForCheckboxes();
  loadTemplates();
  setupFormListeners();
});

function loadCustomers() {
  fetch('/data/customer')
    .then(response => response.json())
    .then(data => {
      const customerSelect = document.getElementById('customer_id');
      if (data.data && Array.isArray(data.data.data)) {
        data.data.data.forEach(customer => {
          const option = document.createElement('option');
          option.value = customer.id;
          option.textContent = customer.nama_customer || customer.name || 'Customer ' + customer.id;
          customerSelect.appendChild(option);
        });
      }
    })
    .catch(error => console.error('Error loading customers:', error));
}

function loadTemplates() {
  const templateSelects = ['template_id', 'custom_template_id', 'broadcast_template_id'];
  templateSelects.forEach(selectId => {
    const select = document.getElementById(selectId);
    if (select) {
      select.innerHTML = '<option value="">Loading templates...</option>';
      select.disabled = true;
    }
  });

  fetch('/qontak/templates')
    .then(response => response.json())
    .then(data => {
      templateSelects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
          select.innerHTML = '<option value="">Select Template</option>';
          select.disabled = false;

          if (data.success && data.data && data.data.data) {
            const templates = data.data.data.sort((a, b) => {
              const nameA = a.name || a.template_name || '';
              const nameB = b.name || b.template_name || '';
              return nameA.localeCompare(nameB);
            });

            templates.forEach(template => {
              const option = document.createElement('option');
              option.value = template.id || template.template_id;

              let label = template.name || template.template_name || 'Unknown Template';
              const language = (template.language && template.language.code) ? template.language.code : template.language || '';
              const status = template.status || 'active';

              if (language) label += ' (' + language + ')';
              if (status !== 'active') label += ' [' + status + ']';

              option.textContent = label;
              option.dataset.language = language;
              option.dataset.body = template.body || template.content || '';

              select.appendChild(option);
              console.log('Template: ', template.id);
              console.log('Template: ', template.name);
            });
            window.availableTemplates = templates;
          }
        }
      });
    })
    .catch(error => {
      console.error('Error loading templates:', error);
      templateSelects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
          select.innerHTML = '<option value="">Failed to load templates</option>';
          select.disabled = false;
        }
      });
    });
}

function setupFormListeners() {
  // Template change listeners
  document.getElementById('template_id').addEventListener('change', function() {
    updateTemplateDisplay(this, 'template_id');
  });

  document.getElementById('custom_template_id').addEventListener('change', function() {
    updateTemplateDisplay(this, 'custom_template_id');
  });

  // Send Message to Customer form
  document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendFormData(this, '/qontak/send-message', 'messageResult');
  });

  // Send to Custom Number form
  document.getElementById('sendToNumberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendFormData(this, '/qontak/send-to-number', 'customNumberResult');
  });

  // Broadcast form
  document.getElementById('broadcastForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendBroadcastData(this);
  });
}

function updateTemplateDisplay(selectElement, type) {
  const displayId = type === 'template_id' ? 'selectedTemplateDisplay' : 'selectedCustomTemplateDisplay';
  const bodyDisplayId = type === 'template_id' ? 'templateBodyDisplay' : 'templateBodyDisplayCustom';
  const bodyContentId = type === 'template_id' ? 'templateBodyContent' : 'templateBodyContentCustom';

  const displayDiv = document.getElementById(displayId);
  const bodyDisplayDiv = document.getElementById(bodyDisplayId);
  const bodyContentPre = document.getElementById(bodyContentId);

  if (selectElement.value) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const templateBody = selectedOption.dataset.body || '';

    if (templateBody) {
      bodyContentPre.textContent = templateBody;
      bodyDisplayDiv.classList.remove('hidden');
    } else {
      bodyDisplayDiv.classList.add('hidden');
    }

    displayDiv.classList.remove('hidden');
  } else {
    displayDiv.classList.add('hidden');
    bodyDisplayDiv.classList.add('hidden');
  }
}

function clearTemplateSelection(formType) {
  const selectElement = document.getElementById(formType);
  const displayId = formType === 'template_id' ? 'selectedTemplateDisplay' : 'selectedCustomTemplateDisplay';
  const bodyDisplayId = formType === 'template_id' ? 'templateBodyDisplay' : 'templateBodyDisplayCustom';

  const displayDiv = document.getElementById(displayId);
  const bodyDisplayDiv = document.getElementById(bodyDisplayId);

  if (selectElement) selectElement.value = '';
  if (displayDiv) displayDiv.classList.add('hidden');
  if (bodyDisplayDiv) bodyDisplayDiv.classList.add('hidden');
}

function sendFormData(form, url, resultDivId) {
  const resultDiv = document.getElementById(resultDivId);
  showLoading(resultDiv);

  const formData = new FormData(form);
  const data = Object.fromEntries(formData);

  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showSuccess(resultDiv, data.message || 'Success!');
    } else {
      showError(resultDiv, data.message || 'Error occurred');
    }
  })
  .catch(error => {
    showError(resultDiv, 'Network error: ' + error.message);
  });
}

// API Functions
function testConnection() {
  showResult('connectionResult', '/qontak/test-connection', 'Testing connection...');
}

function getTemplates() {
  showResult('templatesResult', '/qontak/templates', 'Fetching templates...');
}

function getAccountInfo() {
  showResult('accountResult', '/qontak/account-info', 'Fetching account info...');
}

function debugAuth() {
  showResult('debugResult', '/qontak/debug-auth', 'Debugging auth...');
}

// Broadcast Functions
function loadCustomersForCheckboxes() {
  const container = document.getElementById('customerCheckboxes');
  container.innerHTML = '<div class="col-span-full text-center text-gray-500">Loading customers...</div>';

  fetch('/data/customer')
    .then(response => response.json())
    .then(data => {
      if (data.data && Array.isArray(data.data.data)) {
        container.innerHTML = '';
        data.data.data.forEach(customer => {
          const label = document.createElement('label');
          label.className = 'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer';
          label.innerHTML = `
            <input type="checkbox" class="customer-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                   data-id="${customer.id}"
                   data-name="${customer.nama_customer || customer.name || 'Customer ' + customer.id}"
                   data-phone="${customer.no_hp || customer.no_telepon || ''}">
            <span class="text-sm text-gray-700">${customer.nama_customer || customer.name || 'Customer ' + customer.id}</span>
          `;
          container.appendChild(label);
        });
      } else {
        container.innerHTML = '<div class="col-span-full text-center text-red-500">Failed to load customers</div>';
      }
    })
    .catch(error => {
      container.innerHTML = '<div class="col-span-full text-center text-red-500">Error loading customers</div>';
      console.error('Error loading customers:', error);
    });
}

function filterCustomers() {
  const searchTerm = document.getElementById('customerSearch').value.toLowerCase();
  const checkboxes = document.querySelectorAll('.customer-checkbox');

  checkboxes.forEach(checkbox => {
    const label = checkbox.parentElement;
    const text = label.textContent.toLowerCase();

    if (text.includes(searchTerm)) {
      label.style.display = 'flex';
    } else {
      label.style.display = 'none';
    }
  });
}

function toggleRecipientType() {
  const type = document.getElementById('recipient_type').value;
  const selectedCustomersSection = document.getElementById('selectedCustomersSection');
  const customNumbersSection = document.getElementById('customNumbersSection');

  if (type === 'selected') {
    selectedCustomersSection.classList.remove('hidden');
    customNumbersSection.classList.add('hidden');
  } else {
    selectedCustomersSection.classList.add('hidden');
    customNumbersSection.classList.remove('hidden');
  }
}

function addParamRow() {
  const container = document.getElementById('templateParams');
  const rowCount = container.querySelectorAll('.flex.gap-2').length + 1;

  const row = document.createElement('div');
  row.className = 'flex gap-2 items-center';
  row.innerHTML = `
    <input type="text" placeholder="Key (${rowCount})" class="param-key flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="${rowCount}">
    <input type="text" placeholder="Parameter Name" class="param-name flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    <input type="text" placeholder="Value" class="param-value flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    <button type="button" onclick="removeParam(this)" class="px-3 py-2 text-red-600 hover:text-red-800">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
      </svg>
    </button>
  `;

  container.appendChild(row);
}

function removeParam(button) {
  const row = button.parentElement;
  const container = document.getElementById('templateParams');

  if (container.querySelectorAll('.flex.gap-2').length > 1) {
    row.remove();
  } else {
    alert('At least one parameter is required');
  }
}

function sendBroadcastData(form) {
  const resultDiv = document.getElementById('broadcastResult');
  showLoading(resultDiv, 'Sending broadcast...');

  const recipientType = document.getElementById('recipient_type').value;
  let recipients = [];

  if (recipientType === 'selected') {
    const checkedBoxes = document.querySelectorAll('.customer-checkbox:checked');
    checkedBoxes.forEach(checkbox => {
      recipients.push({
        name: checkbox.dataset.name,
        number: checkbox.dataset.phone
      });
    });

    if (recipients.length === 0) {
      showError(resultDiv, 'Please select at least one customer');
      return;
    }
  } else {
    const customNumbers = document.getElementById('customNumbers').value.trim();
    const lines = customNumbers.split('\n');

    lines.forEach(line => {
      const parts = line.split(',');
      if (parts.length >= 2) {
        recipients.push({
          name: parts[0].trim(),
          number: parts[1].trim()
        });
      }
    });

    if (recipients.length === 0) {
      showError(resultDiv, 'Please add at least one recipient');
      return;
    }
  }

  const templateParams = [];
  const paramRows = document.querySelectorAll('#templateParams .flex.gap-2');

  paramRows.forEach(row => {
    const key = row.querySelector('.param-key').value.trim();
    const name = row.querySelector('.param-name').value.trim();
    const value = row.querySelector('.param-value').value.trim();

    if (key && value) {
      templateParams.push({
        key: key,
        value: name || 'message',
        value_text: value
      });
    }
  });

  if (templateParams.length === 0) {
    showError(resultDiv, 'Please add at least one template parameter');
    return;
  }

  const payload = {
    recipients: recipients,
    template_id: document.getElementById('broadcast_template_id').value,
    channel_integration_id: document.getElementById('broadcast_channel_id').value,
    language_code: document.getElementById('broadcast_language').value,
    template_params: templateParams
  };

  fetch('/qontak/broadcast', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(payload)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const summary = `
        <div class="mt-3 p-4 bg-green-50 border border-green-200 rounded-lg">
          <h4 class="font-semibold text-green-800 mb-2">Broadcast Summary</h4>
          <ul class="text-sm text-green-700 space-y-1">
            <li>Total Recipients: ${data.data.total_recipients}</li>
            <li class="text-green-600">Successful: ${data.data.successful}</li>
            ${data.data.failed > 0 ? `<li class="text-red-600">Failed: ${data.data.failed}</li>` : ''}
          </ul>
        </div>
      `;
      showSuccess(resultDiv, data.message + summary);
    } else {
      showError(resultDiv, data.message || 'Error occurred');
    }
  })
  .catch(error => {
    showError(resultDiv, 'Network error: ' + error.message);
  });
}

function showResult(elementId, url, loadingMessage) {
  const element = document.getElementById(elementId);
  showLoading(element, loadingMessage);

  fetch(url)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showSuccess(element, data.message || 'Success!');
      } else {
        showError(element, data.message || 'Error occurred');
      }
    })
    .catch(error => {
      showError(element, 'Network error: ' + error.message);
    });
}

// UI Helper Functions
function showLoading(element, message = 'Loading...') {
  element.innerHTML = '<div class="flex items-center justify-center py-2">' +
    '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>' +
    '<span class="text-sm text-gray-600">' + message + '</span>' +
    '</div>';
}

function showSuccess(element, message) {
  element.innerHTML = '<div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">' +
    '<svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' +
    '</svg>' +
    '<p class="text-sm text-green-800 font-medium">' + message + '</p>' +
    '</div>';
}

function showError(element, message) {
  element.innerHTML = '<div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">' +
    '<svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' +
    '</svg>' +
    '<p class="text-sm text-red-800 font-medium">' + message + '</p>' +
    '</div>';
}

function closeModal() {
  document.getElementById('responseModal').classList.add('hidden');
}
</script>
@endsection
