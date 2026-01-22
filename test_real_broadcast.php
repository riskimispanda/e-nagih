<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\QontakServices;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create Qontak service instance
$qontakService = new QontakServices();

echo "🔍 Getting broadcast history to find valid broadcast IDs...\n\n";

try {
    $history = $qontakService->getBroadcastHistory(['limit' => 5]);
    echo "✅ Broadcast History Retrieved!\n";
    echo json_encode($history, JSON_PRETTY_PRINT) . "\n\n";
    
    if (is_array($history) && !empty($history)) {
        $firstBroadcast = $history[0];
        $broadcastId = $firstBroadcast['id'] ?? null;
        
        if ($broadcastId) {
            echo "🎯 Testing with real broadcast ID: $broadcastId\n\n";
            
            // Test the endpoint with this broadcast ID
            $result = $qontakService->testBroadcastLogEndpoint(
                $broadcastId, 
                "/qontak/v1/broadcasts/{id}/whatsapp/log"
            );
            
            if ($result['success']) {
                echo "✅ SUCCESS!\n";
                echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "❌ ERROR: " . $result['error'] . "\n";
            }
        } else {
            echo "❌ No broadcast ID found in history\n";
        }
    } else {
        echo "ℹ️  No broadcast history found. This means no broadcasts have been sent yet.\n";
        echo "Let's test with the provided broadcast ID anyway to see if the endpoint format is correct:\n\n";
        
        $broadcastId = '44c1999a-6ea8-4828-bf9c-d6a5a4b6b115';
        
        // Test the endpoint
        $result = $qontakService->testBroadcastLogEndpoint(
            $broadcastId, 
            "/qontak/v1/broadcasts/{id}/whatsapp/log"
        );
        
        if ($result['success']) {
            echo "✅ Endpoint format is correct!\n";
            echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ ERROR: " . $result['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}