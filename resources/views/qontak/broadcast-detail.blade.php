@extends('layouts.contentNavbarLayout')

@section('title', 'Broadcast Detail')

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in {
        animation: fadeIn 0.6s ease-out;
    }
    .glass-morphism {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .status-glow {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.1);
    }
    .hover-scale {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-scale:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
</style>

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-12 text-center fade-in">
            <div class="inline-flex items-center px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full shadow-lg mb-6">
                <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mr-3"></div>
                <span class="text-sm font-medium text-gray-600">Broadcast Analytics</span>
            </div>
            <h1 class="text-5xl font-bold mb-4">
                <span class="gradient-text">Broadcast Detail</span>
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Comprehensive analysis and detailed information about your WhatsApp broadcast performance
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-center gap-4 mb-12 fade-in" style="animation-delay: 0.2s;">
            <a href="{{ route('qontak.broadcast-logs') }}"
               class="group inline-flex items-center px-6 py-3 bg-white text-gray-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-200">
                <i class="fas fa-arrow-left mr-3 group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span class="font-medium">Back to Logs</span>
            </a>
            <button onclick="refreshData()"
                    class="group inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <i class="fas fa-sync-alt mr-3 group-hover:rotate-180 transition-transform duration-700" id="refreshIcon"></i>
                <span class="font-medium">Refresh Data</span>
            </button>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="flex flex-col items-center justify-center py-24">
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-blue-200 rounded-full"></div>
                    <div class="w-16 h-16 border-4 border-transparent border-t-blue-600 rounded-full animate-spin absolute top-0"></div>
                </div>
                <p class="mt-8 text-gray-600 font-medium animate-pulse">Loading broadcast analytics...</p>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden">
            <div class="glass-morphism rounded-2xl p-8 max-w-2xl mx-auto status-glow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-2xl mb-6">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Unable to Load Data</h3>
                    <p class="text-gray-600 mb-6" id="errorMessage">An error occurred while fetching broadcast details.</p>
                    <button onclick="loadData()"
                            class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors shadow-lg">
                        <i class="fas fa-retry mr-2"></i>
                        <span class="font-medium">Try Again</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div id="broadcastDetails" class="hidden space-y-8 fade-in">
            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Message ID Card -->
                <div class="glass-morphism rounded-2xl p-6 hover-scale">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-hashtag text-white text-lg"></i>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-gray-900">Message ID</h3>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <code class="text-sm font-mono text-gray-800 break-all" id="messageId">N/A</code>
                    </div>
                </div>

                <!-- Contact Card -->
                <div class="glass-morphism rounded-2xl p-6 hover-scale">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-gray-900">Contact</h3>
                    </div>
                    <div class="space-y-2">
                        <p class="font-medium text-gray-900" id="contactName">N/A</p>
                        <p class="text-sm text-gray-600" id="phoneNumber">N/A</p>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="glass-morphism rounded-2xl p-6 hover-scale">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-gray-900">Status</h3>
                    </div>
                    <div class="flex items-center justify-center">
                        <span id="statusBadge" class="px-4 py-2 text-sm font-bold rounded-full bg-gray-100 text-gray-800">pending</span>
                    </div>
                </div>

                <!-- Date Card -->
                <div class="glass-morphism rounded-2xl p-6 hover-scale">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar text-white text-lg"></i>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-gray-900">Created</h3>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm text-gray-800" id="createdAt">N/A</p>
                    </div>
                </div>
            </div>

            <!-- Main Information Panel -->
            <div class="glass-morphism rounded-2xl overflow-hidden shadow-2xl">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-info-circle text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-white">Detailed Information</h2>
                    </div>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Broadcast Details</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-xs text-gray-500 font-medium">Broadcast ID</label>
                                        <div class="bg-gray-50 rounded-lg p-3 mt-1">
                                            <code class="text-sm font-mono text-gray-900 break-all" id="broadcastId">N/A</code>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 font-medium">Channel Integration</label>
                                        <div class="bg-gray-50 rounded-lg p-3 mt-1">
                                            <code class="text-sm font-mono text-gray-900 break-all" id="channelId">N/A</code>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 font-medium">Pacing</label>
                                        <div class="mt-1">
                                            <span id="pacingBadge" class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Content Panel -->
            <div class="glass-morphism rounded-2xl overflow-hidden shadow-2xl">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-white">Message Content</h2>
                    </div>
                </div>
                <div class="p-8">
                    <div id="messageContent" class="space-y-6">
                        <p class="text-gray-500 text-center py-8">No message content available</p>
                    </div>
                </div>
            </div>

            <!-- Analytics Dashboard -->
            <div class="glass-morphism rounded-2xl overflow-hidden shadow-2xl">
                <div class="bg-gradient-to-r from-green-600 to-teal-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-chart-pie text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-white">Performance Analytics</h2>
                    </div>
                </div>
                <div class="p-8">
                    <div id="statusSummary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <p class="text-gray-500 text-center py-8 md:col-span-2 lg:col-span-3">No analytics data available</p>
                    </div>
                </div>
            </div>

            <!-- Technical Details -->
            <div class="glass-morphism rounded-2xl overflow-hidden shadow-2xl">
                <div class="bg-gradient-to-r from-gray-700 to-gray-900 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-code text-white text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Technical Details</h2>
                        </div>
                        <button onclick="copyRawResponse()"
                                class="text-white/80 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="p-8">
                    <div class="bg-gray-900 rounded-xl p-6">
                        <pre id="rawResponse" class="text-green-400 text-sm font-mono overflow-x-auto whitespace-pre-wrap max-h-96">No technical data available</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
// Wait for everything to be loaded completely
window.addEventListener('load', function() {
    console.log('Page fully loaded, initializing...');

    // Global variables
    let broadcastId = '{{ $broadcastId }}';
    let broadcastData = null;
    let isLoading = false;
    let error = null;

    // Get server data safely
    try {
        const dataElement = document.getElementById('serverData');
        if (dataElement && dataElement.value) {
            broadcastData = JSON.parse(dataElement.value);
            console.log('Server data loaded:', broadcastData);
        }
    } catch (e) {
        console.error('Error parsing server data:', e);
    }

    // Initialize
    if (!broadcastData) {
        console.log('No server data, loading from API...');
        loadData();
    } else {
        console.log('Displaying server data...');
        displayData(broadcastData);
    }

    function loadData() {
        console.log('Loading data...');
        isLoading = true;
        error = null;

        showLoading();
        hideError();
        hideDetails();

        fetch(`/api/qontak/broadcast-detail/${broadcastId}`)
            .then(response => response.json())
            .then(data => {
                console.log('API response:', data);
                if (data.success) {
                    broadcastData = data.data;
                    displayData(broadcastData);
                } else {
                    error = data.message || 'Failed to load broadcast details';
                    showError(error);
                }
            })
            .catch(err => {
                console.error('API error:', err);
                error = err.message || 'Network error occurred';
                showError(error);
            })
            .finally(() => {
                isLoading = false;
                hideLoading();
            });
    }

    function refreshData() {
        const refreshIcon = document.getElementById('refreshIcon');
        if (refreshIcon) {
            refreshIcon.classList.add('fa-spin');
        }
        loadData();
        setTimeout(() => {
            if (refreshIcon) {
                refreshIcon.classList.remove('fa-spin');
            }
        }, 1000);
    }

    function displayData(data) {
        console.log('Displaying data:', data);
        if (!data) return;

        // Handle case where data is an array - take first item
        let broadcast = data;
        if (Array.isArray(data)) {
            broadcast = data.length > 0 ? data[0] : null;
            console.log('Data is array, using first item:', broadcast);
        }

        if (!broadcast) return;

        // Update basic fields
        updateField('messageId', broadcast.id);
        updateField('broadcastId', broadcast.messages_broadcast_id);
        updateField('contactName', broadcast.contact_full_name);
        updateField('phoneNumber', broadcast.contact_phone_number);
        updateField('channelId', broadcast.channel_integration_id);
        updateField('createdAt', broadcast.created_at ?
            new Date(broadcast.created_at).toLocaleString() : 'N/A');

        // Update badges
        updateStatusBadge(document.getElementById('statusBadge'), broadcast.status || 'pending');
        updatePacingBadge(document.getElementById('pacingBadge'), broadcast.is_pacing || false);

        // Update content sections
        updateStatusSummary(broadcast.messages_response);
        updateMessageContent(broadcast.messages);
        updateRawResponse(broadcast.messages_response);

        showDetails();
    }

    function updateField(fieldId, value) {
        const element = document.getElementById(fieldId);
        if (element && value) {
            element.textContent = value;
        }
    }

    function updateStatusBadge(element, status) {
        if (!element) return;

        const statusConfig = {
            'sent': { bg: 'bg-blue-100', text: 'text-blue-800', icon: 'fa-paper-plane' },
            'delivered': { bg: 'bg-green-100', text: 'text-green-800', icon: 'fa-check-circle' },
            'read': { bg: 'bg-purple-100', text: 'text-purple-800', icon: 'fa-eye' },
            'failed': { bg: 'bg-red-100', text: 'text-red-800', icon: 'fa-exclamation-circle' },
            'pending': { bg: 'bg-yellow-100', text: 'text-yellow-800', icon: 'fa-clock' }
        };

        const config = statusConfig[status.toLowerCase()] || statusConfig['pending'];
        element.className = `px-4 py-2 text-sm font-bold rounded-full ${config.bg} ${config.text}`;
        element.innerHTML = `<i class="fas ${config.icon} mr-2"></i>${status}`;
    }

    function updatePacingBadge(element, isPacing) {
        if (!element) return;

        const config = isPacing ?
            { bg: 'bg-green-100', text: 'text-green-800', icon: 'fa-check' } :
            { bg: 'bg-gray-100', text: 'text-gray-800', icon: 'fa-times' };

        element.className = `px-3 py-1 text-sm font-semibold rounded-full ${config.bg} ${config.text}`;
        element.innerHTML = `<i class="fas ${config.icon} mr-2"></i>${isPacing ? 'Yes' : 'No'}`;
    }

    function updateStatusSummary(messagesResponse) {
        const container = document.getElementById('statusSummary');
        if (!container) return;

        if (!messagesResponse) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8 md:col-span-2 lg:col-span-3">No analytics data available</p>';
            return;
        }

        const cards = Object.entries(messagesResponse).map(([key, response]) => {
            const colors = {
                'sent': { bg: 'from-blue-500', to: 'to-blue-600', icon: 'fa-paper-plane' },
                'delivered': { bg: 'from-green-500', to: 'to-emerald-600', icon: 'fa-check-circle' },
                'read': { bg: 'from-purple-500', to: 'to-purple-600', icon: 'fa-eye' },
                'failed': { bg: 'from-red-500', to: 'to-red-600', icon: 'fa-exclamation-circle' },
                'pending': { bg: 'from-yellow-500', to: 'to-yellow-600', icon: 'fa-clock' }
            };

            const color = colors[key.toLowerCase()] || { bg: 'from-gray-500', to: 'to-gray-600', icon: 'fa-question' };

            return `
                <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br ${color.bg} ${color.to} rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas ${color.icon} text-white text-lg"></i>
                            </div>
                            <h4 class="ml-4 text-lg font-bold text-gray-900 capitalize">${key}</h4>
                        </div>
                        <div class="text-3xl font-bold text-gray-900">${response.count || 0}</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-600">
                            ${response.contacts ? `${response.contacts.length} contacts affected` : 'No contact details'}
                        </p>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = cards;
    }

    function updateMessageContent(messages) {
        const container = document.getElementById('messageContent');
        if (!container) return;

        if (!messages) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">No message content available</p>';
            return;
        }

        let contentHTML = '';

        if (messages.header) {
            contentHTML += `
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-heading text-white text-sm"></i>
                        </div>
                        <h4 class="font-bold text-gray-900">Header</h4>
                    </div>
                    <div class="bg-white rounded-lg p-4 text-gray-800">
                        ${messages.header.text || JSON.stringify(messages.header)}
                    </div>
                </div>
            `;
        }

        if (messages.body) {
            contentHTML += `
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-align-left text-white text-sm"></i>
                        </div>
                        <h4 class="font-bold text-gray-900">Body</h4>
                    </div>
                    <div class="bg-white rounded-lg p-4 text-gray-800">
                        ${messages.body.text || JSON.stringify(messages.body)}
                    </div>
                </div>
            `;
        }

        if (messages.buttons) {
            contentHTML += `
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-mouse-pointer text-white text-sm"></i>
                        </div>
                        <h4 class="font-bold text-gray-900">Buttons</h4>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <pre class="text-green-400 text-sm font-mono">${JSON.stringify(messages.buttons, null, 2)}</pre>
                    </div>
                </div>
            `;
        }

        container.innerHTML = contentHTML || '<p class="text-gray-500 text-center py-8">No message content available</p>';
    }

    function updateRawResponse(messagesResponse) {
        const container = document.getElementById('rawResponse');
        if (!container) return;

        container.textContent = messagesResponse ?
            JSON.stringify(messagesResponse, null, 2) :
            'No technical data available';
    }

    function copyRawResponse() {
        const container = document.getElementById('rawResponse');
        if (container) {
            navigator.clipboard.writeText(container.textContent).then(() => {
                // Show success feedback
                const button = event.currentTarget;
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check text-green-400"></i>';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 2000);
            });
        }
    }

    // UI Helper functions
    function showLoading() {
        const element = document.getElementById('loadingState');
        if (element) element.classList.remove('hidden');
    }

    function hideLoading() {
        const element = document.getElementById('loadingState');
        if (element) element.classList.add('hidden');
    }

    function showError(message) {
        const errorElement = document.getElementById('errorMessage');
        const containerElement = document.getElementById('errorState');
        if (errorElement) errorElement.textContent = message;
        if (containerElement) containerElement.classList.remove('hidden');
    }

    function hideError() {
        const element = document.getElementById('errorState');
        if (element) element.classList.add('hidden');
    }

    function showDetails() {
        const element = document.getElementById('broadcastDetails');
        if (element) element.classList.remove('hidden');
    }

    function hideDetails() {
        const element = document.getElementById('broadcastDetails');
        if (element) element.classList.add('hidden');
    }

    // Make functions globally accessible
    window.loadData = loadData;
    window.refreshData = refreshData;
    window.copyRawResponse = copyRawResponse;
});
</script>

<!-- Hidden input to store server data -->
<input type="hidden" id="serverData" value="{{ $broadcastData ? json_encode($broadcastData) : '' }}">
