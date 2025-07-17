@php
use Illuminate\Support\Facades\Vite;
@endphp
<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
@vite(['resources/assets/js/config.js'])

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.0/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<!-- Laravel Echo Event Listener -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusEl = document.getElementById('status');
        const messagesEl = document.getElementById('messages');

        // Log function
        function log(message) {
            const div = document.createElement('div');
            div.textContent = message;
            messagesEl.appendChild(div);
            console.log(message);
        }

        try {
            // Initialize Pusher
            window.Pusher = Pusher;

            // Configure Echo
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: '{{ env('REVERB_APP_KEY') }}',
                wsHost: '{{ env('REVERB_HOST') }}',
                wsPort: {{ env('REVERB_PORT', 8080) }},
                forceTLS: false,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
            });

            log('Echo initialized');

            // Subscribe to the channel
            const channel = window.Echo.channel('updates-data');

            log('Subscribed to channel: updates-data');

            // Listen for events
            channel.listen('.data.updated', function(event) {
                log('Received event: ' + JSON.stringify(event));
                statusEl.textContent = 'Connected and received event!';
                statusEl.style.color = 'green';
            });

            // Check connection status
            setTimeout(function() {
                if (window.Echo.connector.pusher.connection.state === 'connected') {
                    statusEl.textContent = 'Connected to WebSocket server!';
                    statusEl.style.color = 'green';
                    log('Connection state: ' + window.Echo.connector.pusher.connection.state);
                } else {
                    statusEl.textContent = 'Failed to connect: ' + window.Echo.connector.pusher
                    .connection.state;
                    statusEl.style.color = 'red';
                    log('Connection state: ' + window.Echo.connector.pusher.connection.state);
                }
            }, 3000);

            // Log connection events
            window.Echo.connector.pusher.connection.bind('connected', function() {
                log('Connection event: connected');
            });

            window.Echo.connector.pusher.connection.bind('connecting', function() {
                log('Connection event: connecting');
            });

            window.Echo.connector.pusher.connection.bind('disconnected', function() {
                log('Connection event: disconnected');
            });

            window.Echo.connector.pusher.connection.bind('failed', function() {
                log('Connection event: failed');
            });

            window.Echo.connector.pusher.connection.bind('error', function(error) {
                log('Connection event: error - ' + JSON.stringify(error));
            });
        } catch (error) {
            statusEl.textContent = 'Error: ' + error.message;
            statusEl.style.color = 'red';
            log('Error: ' + error.message);
        }
    });
</script>
<script>
    @if (session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        topLayer: true,
        animation: true
    });
    @endif

    @if (session('toast_success'))
    Swal.fire({
        icon: 'success',
        toast: true,
        position: 'top-end',
        text: '{{ session('toast_success') }}',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            popup: 'colored-toast mini-toast',
        },
        background: 'white',
        opacity: 0.8,
        color: '#000000',
        topLayer: true,
        animation: true
    });
    @endif

    @if (session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ session('error') }}',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        topLayer: true,
        animation: true
    });
    @endif

    @if (session('toast_error'))
    Swal.fire({
        icon: 'error',
        toast: true,
        position: 'top-end',
        text: '{{ session('toast_error') }}',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        topLayer: true,
        animation: true
    });
    @endif
</script>

<script>
    function formatNomorIndonesia(nomor) {
        nomor = nomor.replace(/[^0-9]/g, ''); // hanya angka
        if (nomor.startsWith('62')) {
            return '0' + nomor.slice(2);
        }
        if (nomor.startsWith('0')) {
            return nomor;
        }
        return '0' + nomor;
    }

    // Format semua sel dengan class "nomor-hp"
    document.querySelectorAll('.nomor-hp').forEach(function(cell) {
        const original = cell.textContent.trim();
        const formatted = formatNomorIndonesia(original);
        cell.textContent = formatted;
    });
</script>
