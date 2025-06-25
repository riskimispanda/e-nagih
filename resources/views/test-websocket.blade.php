<!DOCTYPE html>
<html>

<head>
    <title>WebSocket Test</title>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.0/dist/echo.iife.js"></script>
</head>

<body>
    <h1>WebSocket Test</h1>
    <div id="status">Connecting...</div>
    <div id="messages"></div>

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
                    wsHost: '{{ env('REVERB_HOST', 'localhost') }}',
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
</body>

</html>
