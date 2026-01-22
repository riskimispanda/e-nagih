#!/usr/bin/env php
<?php

/**
 * Debug script untuk memeriksa data invoice berdasarkan tahun
 *
 * Usage: php debug_filter.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG FILTER TAHUN ===\n\n";

// Get total invoices
$totalInvoices = Invoice::count();
echo "Total Invoices: $totalInvoices\n\n";

// Get invoices grouped by year
echo "Invoices by Year:\n";
$invoicesByYear = Invoice::selectRaw('YEAR(jatuh_tempo) as year, COUNT(*) as count')
    ->whereNotNull('jatuh_tempo')
    ->groupBy('year')
    ->orderBy('year', 'desc')
    ->get();

foreach ($invoicesByYear as $item) {
    echo "  {$item->year}: {$item->count} invoices\n";
}

echo "\n";

// Test filter for year 2026
echo "Testing filter for year 2026:\n";
$invoices2026 = Invoice::whereYear('jatuh_tempo', 2026)->get();
echo "  Found: {$invoices2026->count()} invoices\n";

if ($invoices2026->count() > 0) {
    echo "  Sample dates:\n";
    foreach ($invoices2026->take(5) as $inv) {
        echo "    - Invoice #{$inv->id}: {$inv->jatuh_tempo}\n";
    }
}

echo "\n";

// Test with agen filter (assuming agen_id = 1 for testing)
echo "Testing with agen filter:\n";
$agenId = DB::table('users')->where('roles_id', 6)->first()->id ?? null;

if ($agenId) {
    echo "  Agen ID: $agenId\n";

    $invoicesAgen = Invoice::whereHas('customer', function ($q) use ($agenId) {
        $q->withTrashed()->where('agen_id', $agenId);
    })->whereYear('jatuh_tempo', 2026)->get();

    echo "  Found: {$invoicesAgen->count()} invoices for year 2026\n";

    if ($invoicesAgen->count() > 0) {
        echo "  Sample:\n";
        foreach ($invoicesAgen->take(3) as $inv) {
            echo "    - Invoice #{$inv->id}: {$inv->jatuh_tempo} (Customer: {$inv->customer->nama_customer})\n";
        }
    }
} else {
    echo "  No agen found\n";
}

echo "\n=== END DEBUG ===\n";
