<?php
require 'vendor/autoload.php';

use RouterOS\Client;
use RouterOS\Query;

try {
    $client = new Client([
        'host' => '203.190.43.100',
        'user' => 'panda',
        'pass' => 'panda',
        'port' => 5000, // default API port
        'timeout' => 5,
    ]);

    $query = new Query('/system/identity/print');
    $response = $client->query($query)->read();

    print_r($response);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

