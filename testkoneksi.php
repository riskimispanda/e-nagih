<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Test 1: Direct Predis
    echo "=== Test Direct Predis ===\n";
    $client = new \Predis\Client([
        'scheme' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ]);
    echo "Ping: " . $client->ping() . "\n";
    $client->set('test', 'Direct Predis OK');
    echo "Get: " . $client->get('test') . "\n\n";

    // Test 2: Via Laravel
    echo "=== Test Via Laravel ===\n";
    $redis = app('redis')->connection();
    echo "Ping: " . $redis->ping() . "\n";
    $redis->set('laravel_test', 'Laravel Redis OK');
    echo "Get: " . $redis->get('laravel_test') . "\n\n";

    echo "✅ Semua test berhasil!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
