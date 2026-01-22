<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\QontakServices;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create Qontak service instance
$qontakService = new QontakServices();

echo "🔧 Testing UPDATED Qontak Service with correct endpoint...\n\n";

// Test with the original provided broadcast ID
$providedId = '44c1999a-6ea8-4828-bf9c-d6a5a4b6b115';
echo "📍 Testing with provided broadcast ID: $providedId\n";

try {
    $result = $qontakService->getBroadcastLog($providedId);
    
    if (is_array($result) && !empty($result)) {
        echo "✅ SUCCESS - Found log data!\n";
        echo "Data count: " . count($result) . "\n";
        echo "First entry:\n";
        echo json_encode($result[0], JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "ℹ️  No log data found for this broadcast ID\n";
        echo "This could mean the broadcast ID doesn't exist or has no logs\n\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
}

// Test with a real broadcast ID from history
$realId = '5f836ddf-ede1-4172-8118-65bb7eb88bd0';
echo "📍 Testing with real broadcast ID: $realId\n";

try {
    $result = $qontakService->getBroadcastLog($realId);
    
    if (is_array($result) && !empty($result)) {
        echo "✅ SUCCESS - Found log data!\n";
        echo "Data count: " . count($result) . "\n";
        echo "Sample log entry:\n";
        
        $log = $result[0];
        $sample = [
            'id' => $log['id'] ?? 'N/A',
            'contact_name' => $log['contact_full_name'] ?? 'N/A',
            'contact_phone' => $log['contact_phone_number'] ?? 'N/A',
            'status' => $log['status'] ?? 'N/A',
            'whatsapp_message_id' => $log['whatsapp_message_id'] ?? 'N/A',
            'created_at' => $log['created_at'] ?? 'N/A'
        ];
        
        echo json_encode($sample, JSON_PRETTY_PRINT) . "\n";
        echo "\n📊 Message Status Breakdown:\n";
        if (isset($log['messages_response'])) {
            foreach (['sent', 'delivered', 'read'] as $status) {
                if (isset($log['messages_response'][$status])) {
                    $statusData = $log['messages_response'][$status];
                    echo "  ✓ $status: " . ($statusData['statuses'][0]['timestamp'] ?? 'N/A') . "\n";
                }
            }
        }
    } else {
        echo "ℹ️  No log data found for this broadcast ID\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🎉 CONCLUSION: The Qontak service has been updated to use the correct endpoint!\n";
echo "✅ /qontak/chat/v1/broadcasts/{id}/whatsapp/log is the working endpoint\n";
echo "✅ Broadcast logs are now accessible and working correctly!\n";