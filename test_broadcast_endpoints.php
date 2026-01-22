<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\QontakServices;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create Qontak service instance
$qontakService = new QontakServices();

// Test both endpoints
$broadcastId = '44c1999a-6ea8-4828-bf9c-d6a5a4b6b115';

echo "Testing different broadcast log endpoints...\n\n";

$endpoints = [
    "/qontak/v1/broadcasts/$broadcastId/whatsapp/log",
    "/qontak/chat/v1/broadcasts/$broadcastId/whatsapp/log", 
    "/qontak/chat/v1/broadcasts/$broadcastId/log",
    "/chat/v1/broadcasts/$broadcastId/whatsapp/log"
];

foreach ($endpoints as $index => $endpoint) {
    echo "📍 Test " . ($index + 1) . ": $endpoint\n";
    
    $result = $qontakService->testBroadcastLogEndpoint($broadcastId, $endpoint);
    
    if ($result['success']) {
        echo "✅ SUCCESS!\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ ERROR: " . $result['error'] . "\n";
    }
    echo "\n";
}