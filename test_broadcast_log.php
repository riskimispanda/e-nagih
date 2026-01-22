<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\QontakServices;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create Qontak service instance
$qontakService = new QontakServices();

// Test the broadcast log endpoint
$broadcastId = '44c1999a-6ea8-4828-bf9c-d6a5a4b6b115';

echo "Testing Qontak Broadcast Log API...\n";
echo "Broadcast ID: $broadcastId\n";
echo "Endpoint: https://api.mekari.com/qontak/chat/v1/broadcasts/$broadcastId/whatsapp/log\n\n";

try {
    $result = $qontakService->getBroadcastLog($broadcastId);
    
    echo "✅ SUCCESS!\n";
    echo "Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    
    // Let's also try to debug the authentication
    echo "\n🔍 Testing authentication...\n";
    try {
        $authResult = $qontakService->testConnection();
        echo "Auth Test Result:\n";
        echo json_encode($authResult, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $authError) {
        echo "Auth Test Failed: " . $authError->getMessage() . "\n";
    }
}