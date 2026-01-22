<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateInvoice extends Command
{
    protected $signature = 'app:generate-invoice
                            {--debug : Debug mode, hanya tampilkan info tanpa generate}
                            {--check : Cek saja, tampilkan laporan tanpa generate}
                            {--fix-gaps : Generate hanya untuk bulan yang terlewat di antara invoice yang ada}
                            {--force-all : Generate semua bulan dalam periode (override existing)}
                            {--from-last : Generate dari bulan setelah invoice terakhir (DEFAULT STRATEGY)}
                            {--start-date= : Tanggal mulai periode (format: YYYY-MM, default: 12 bulan lalu)}
                            {--end-date= : Tanggal akhir periode (format: YYYY-MM, default: bulan ini)}
                            {--customer-id=* : Generate untuk customer tertentu saja (bisa multiple)}
                            {--paket-id=* : Generate untuk paket tertentu saja (bisa multiple)}
                            {--rollback : Rollback invoice yang baru digenerate}
                            {--rollback-date= : Rollback invoice setelah tanggal tertentu}
                            {--rollback-all : Rollback semua invoice yang auto-generated}
                            {--dry-run : Simulasi saja, tidak benar-benar generate}
                            {--max-months=24 : Maksimal bulan yang diproses}
                            {--details : Tampilkan output detail}';

    protected $description = 'Generate invoice untuk bulan-bulan yang terlewat (default: dari invoice terakhir)';

    private $generatedInvoices = [];
    private $statistics = [
        'total_customers' => 0,
        'total_months' => 0,
        'generated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'zero_bills' => 0,
        'gaps_fixed' => 0,
        'from_last_invoice' => 0
    ];

    public function handle()
    {
        $startTime = microtime(true);

        // Cek jika mode rollback
        if ($this->option('rollback') || $this->option('rollback-date') || $this->option('rollback-all')) {
            return $this->handleRollback();
        }

        $this->showHeader();

        // Tentukan periode
        $period = $this->determinePeriod();
        $this->showPeriodInfo($period);

        // Ambil data customer
        $customers = $this->getCustomers();
        $this->statistics['total_customers'] = $customers->count();

        // Tentukan strategi generate
        $strategy = $this->determineStrategy();
        $this->showStrategyInfo($strategy);

        if ($this->option('debug')) {
            return $this->runDebugMode($customers, $period, $strategy);
        }

        if ($this->option('check')) {
            return $this->runCheckMode($customers, $period, $strategy);
        }

        if ($this->option('dry-run')) {
            return $this->runDryRun($customers, $period, $strategy);
        }

        return $this->runGeneration($customers, $period, $strategy, $startTime);
    }

    /**
     * Tampilkan header
     */
    private function showHeader()
    {
        $this->info("==========================================");
        $this->info("      INVOICE GENERATION SYSTEM");
        $this->info("      STRATEGY: FROM LAST INVOICE");
        $this->info("==========================================");
        $this->newLine();
    }

    /**
     * Tentukan periode
     */
    private function determinePeriod()
    {
        $maxMonths = (int) $this->option('max-months');

        // Tanggal mulai
        if ($this->option('start-date')) {
            $startDate = Carbon::parse($this->option('start-date') . '-01')->startOfMonth();
        } else {
            $startDate = Carbon::now()->subMonths($maxMonths)->startOfMonth();
        }

        // Tanggal akhir
        if ($this->option('end-date')) {
            $endDate = Carbon::parse($this->option('end-date') . '-01')->endOfMonth();
        } else {
            $endDate = Carbon::now()->endOfMonth();
        }

        // Validasi
        if ($startDate->gt($endDate)) {
            $this->error("❌ Tanggal mulai tidak boleh lebih besar dari tanggal akhir");
            exit(1);
        }

        $totalMonths = $startDate->diffInMonths($endDate) + 1;
        $this->statistics['total_months'] = $totalMonths;

        if ($totalMonths > $maxMonths) {
            $this->warn("⚠️  Periode {$totalMonths} bulan melebihi maksimal {$maxMonths} bulan");
            $this->warn("   Akan diproses {$maxMonths} bulan terakhir");
            $startDate = $endDate->copy()->subMonths($maxMonths - 1)->startOfMonth();
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'months' => $startDate->diffInMonths($endDate) + 1
        ];
    }

    /**
     * Tampilkan info periode
     */
    private function showPeriodInfo($period)
    {
        $this->info("📅 PERIODE:");
        $this->info("   Mulai      : " . $period['start']->format('d F Y'));
        $this->info("   Akhir      : " . $period['end']->format('d F Y'));
        $this->info("   Total Bulan: " . $period['months'] . " bulan");
        $this->newLine();
    }

    /**
     * Ambil data customer
     */
    private function getCustomers()
    {
        $query = Customer::whereIn('status_id', [3, 4, 9])
            ->whereNull('deleted_at');

        // Filter customer ID
        $customerIds = $this->option('customer-id');
        if (!empty($customerIds)) {
            $query->whereIn('id', $customerIds);
            $this->info("👤 Filter customer ID: " . implode(', ', $customerIds));
        }

        // Filter paket ID
        $paketIds = $this->option('paket-id');
        if (!empty($paketIds)) {
            $query->whereIn('paket_id', $paketIds);
            $this->info("📦 Filter paket ID: " . implode(', ', $paketIds));
        }

        $customers = $query->get();

        $this->info("👥 Total customer yang akan diproses: " . $customers->count());
        $this->newLine();

        return $customers;
    }

    /**
     * Tentukan strategi generate
     */
    private function determineStrategy()
    {
        if ($this->option('force-all')) {
            return 'FORCE_ALL';
        }

        if ($this->option('fix-gaps')) {
            return 'FIX_GAPS';
        }

        // Default: dari invoice terakhir
        return 'FROM_LAST_INVOICE';
    }

    /**
     * Tampilkan info strategi
     */
    private function showStrategyInfo($strategy)
    {
        $strategyNames = [
            'FORCE_ALL' => 'Generate semua bulan dalam periode',
            'FIX_GAPS' => 'Generate hanya untuk celah antara invoice',
            'FROM_LAST_INVOICE' => 'Generate dari bulan setelah invoice terakhir (DEFAULT)'
        ];

        $this->info("🎯 STRATEGY: {$strategy}");
        $this->info("   " . $strategyNames[$strategy]);
        $this->newLine();
    }

    /**
     * Mode debug
     */
    private function runDebugMode($customers, $period, $strategy)
    {
        $this->info("🔍 DEBUG MODE - ANALISIS DATA");
        $this->newLine();

        // Statistik umum
        $this->showGeneralStatistics($customers);

        // Analisis per customer (maks 5)
        $sampleCustomers = $customers->take(5);
        $this->info("📋 SAMPLE 5 CUSTOMER PERTAMA:");

        foreach ($sampleCustomers as $customer) {
            $this->analyzeCustomerInvoices($customer, $period, $strategy);
        }

        // Analisis distribusi invoice
        $this->analyzeInvoiceDistribution($period);

        return 0;
    }

    /**
     * Tampilkan statistik umum
     */
    private function showGeneralStatistics($customers)
    {
        $total = $customers->count();
        $withInvoices = $customers->filter(function ($customer) {
            return $customer->invoice()->exists();
        })->count();

        $this->info("📊 STATISTIK UMUM:");
        $this->info("   Total Customer Aktif   : {$total}");
        $this->info("   Customer dengan Invoice: {$withInvoices} (" . round($withInvoices/$total*100, 1) . "%)");
        $this->info("   Customer tanpa Invoice : " . ($total - $withInvoices) .
                   " (" . round(($total-$withInvoices)/$total*100, 1) . "%)");
        $this->newLine();
    }

    /**
     * Analisis invoice customer dengan strategi
     */
    private function analyzeCustomerInvoices($customer, $period, $strategy)
    {
        // Cari invoice terakhir
        $lastInvoice = Invoice::where('customer_id', $customer->id)
            ->orderBy('jatuh_tempo', 'desc')
            ->first();

        // Ambil invoice dalam periode
        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereBetween('jatuh_tempo', [$period['start'], $period['end']])
            ->orderBy('jatuh_tempo')
            ->get();

        $existingMonths = [];
        foreach ($invoices as $invoice) {
            $date = $this->parseDate($invoice->jatuh_tempo);
            $existingMonths[$date->format('Y-m')] = $date->format('F Y');
        }

        $this->info("👤 {$customer->nama_customer} (ID: {$customer->id})");
        $this->info("   📦 Paket ID: {$customer->paket_id}");

        // Tampilkan invoice terakhir
        if ($lastInvoice) {
            $lastDate = $this->parseDate($lastInvoice->jatuh_tempo);
            $this->info("   📅 Invoice terakhir: {$lastDate->format('F Y')}");

            // Tentukan akan generate dari mana berdasarkan strategi
            if ($strategy === 'FROM_LAST_INVOICE') {
                $startFrom = $lastDate->copy()->addMonth()->startOfMonth();
                if ($startFrom->lt($period['start'])) {
                    $startFrom = clone $period['start'];
                }
                $this->info("   🚀 Akan generate dari: {$startFrom->format('F Y')}");
            }
        } else {
            $this->info("   📅 Invoice terakhir: Belum ada");
            $this->info("   🚀 Akan generate dari: 3 bulan terakhir");
        }

        $this->info("   📊 Invoice dalam periode: " . count($existingMonths) . " dari {$period['months']} bulan");

        // Cek bulan yang akan digenerate berdasarkan strategi
        $monthsToGenerate = $this->getMonthsToGenerate($customer, $period, $strategy);

        if (!empty($monthsToGenerate)) {
            $this->info("   ✅ Akan generate: " . count($monthsToGenerate) . " bulan");

            if (count($monthsToGenerate) <= 5) {
                $this->info("      Bulan: " . implode(', ', $monthsToGenerate));
            } else {
                $this->info("      Bulan: " .
                    implode(', ', array_slice($monthsToGenerate, 0, 3)) .
                    ", ... (" . end($monthsToGenerate) . ")");
            }
        } else {
            $this->info("   ⏭️ Tidak perlu generate (sudah update)");
        }

        $this->newLine();
    }

    /**
     * Dapatkan bulan yang akan digenerate berdasarkan strategi
     */
    private function getMonthsToGenerate($customer, $period, $strategy)
    {
        $monthsToGenerate = [];

        switch ($strategy) {
            case 'FORCE_ALL':
                // Semua bulan dalam periode
                $currentMonth = clone $period['start'];
                while ($currentMonth->lte($period['end'])) {
                    $monthsToGenerate[] = $currentMonth->format('F Y');
                    $currentMonth->addMonth();
                }
                break;

            case 'FIX_GAPS':
                // Hanya bulan yang menjadi celah
                $invoices = Invoice::where('customer_id', $customer->id)
                    ->whereBetween('jatuh_tempo', [$period['start'], $period['end']])
                    ->orderBy('jatuh_tempo')
                    ->get();

                $existingMonths = [];
                foreach ($invoices as $invoice) {
                    $date = $this->parseDate($invoice->jatuh_tempo);
                    $existingMonths[$date->format('Y-m')] = true;
                }

                $currentMonth = clone $period['start'];
                while ($currentMonth->lte($period['end'])) {
                    $monthKey = $currentMonth->format('Y-m');
                    if (!isset($existingMonths[$monthKey])) {
                        $monthsToGenerate[] = $currentMonth->format('F Y');
                    }
                    $currentMonth->addMonth();
                }
                break;

            case 'FROM_LAST_INVOICE':
            default:
                // Dari bulan setelah invoice terakhir
                $lastInvoice = Invoice::where('customer_id', $customer->id)
                    ->orderBy('jatuh_tempo', 'desc')
                    ->first();

                if ($lastInvoice) {
                    $lastDate = $this->parseDate($lastInvoice->jatuh_tempo);
                    $startFrom = $lastDate->copy()->addMonth()->startOfMonth();

                    if ($startFrom->lt($period['start'])) {
                        $startFrom = clone $period['start'];
                    }
                } else {
                    $startFrom = $period['end']->copy()->subMonths(2)->startOfMonth();
                }

                if ($startFrom->lte($period['end'])) {
                    // Ambil semua invoice untuk cek duplikat
                    $allInvoices = Invoice::where('customer_id', $customer->id)
                        ->orderBy('jatuh_tempo')
                        ->get();

                    $existingMonths = [];
                    foreach ($allInvoices as $invoice) {
                        $date = $this->parseDate($invoice->jatuh_tempo);
                        $existingMonths[$date->format('Y-m')] = true;
                    }

                    $currentMonth = clone $startFrom;
                    while ($currentMonth->lte($period['end'])) {
                        $monthKey = $currentMonth->format('Y-m');
                        if (!isset($existingMonths[$monthKey])) {
                            $monthsToGenerate[] = $currentMonth->format('F Y');
                        }
                        $currentMonth->addMonth();
                    }
                }
                break;
        }

        return $monthsToGenerate;
    }

    /**
     * Analisis distribusi invoice
     */
    private function analyzeInvoiceDistribution($period)
    {
        $this->info("📈 DISTRIBUSI INVOICE PER BULAN:");

        $distribution = Invoice::select(
                DB::raw('YEAR(jatuh_tempo) as tahun'),
                DB::raw('MONTH(jatuh_tempo) as bulan'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(tagihan) as total_tagihan')
            )
            ->whereHas('customer', function ($query) {
                $query->whereIn('status_id', [3, 4, 9])
                      ->whereNull('deleted_at');
            })
            ->whereBetween('jatuh_tempo', [$period['start'], $period['end']])
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        if ($distribution->isEmpty()) {
            $this->info("   ❌ Tidak ada data invoice dalam periode ini");
            return;
        }

        $tableData = [];
        foreach ($distribution as $dist) {
            $date = Carbon::create($dist->tahun, $dist->bulan, 1);
            $tableData[] = [
                $date->format('F Y'),
                $dist->total,
                'Rp ' . number_format($dist->total_tagihan, 0, ',', '.'),
                round($dist->total_tagihan / $dist->total) > 0 ?
                    'Rp ' . number_format(round($dist->total_tagihan / $dist->total), 0, ',', '.') : 'Rp 0'
            ];
        }

        $this->table(
            ['Bulan', 'Jumlah Invoice', 'Total Tagihan', 'Rata-rata'],
            $tableData
        );
    }

    /**
     * Mode check
     */
    private function runCheckMode($customers, $period, $strategy)
    {
        $this->info("🔍 CHECK MODE - LAPORAN KELENGKAPAN INVOICE");
        $this->newLine();

        $reportData = [];
        $totalToGenerate = 0;
        $customersToGenerate = 0;

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            $monthsToGenerate = $this->getMonthsToGenerate($customer, $period, $strategy);

            if (!empty($monthsToGenerate)) {
                $customersToGenerate++;
                $totalToGenerate += count($monthsToGenerate);

                $lastInvoice = Invoice::where('customer_id', $customer->id)
                    ->orderBy('jatuh_tempo', 'desc')
                    ->first();

                $reportData[] = [
                    'customer' => $customer,
                    'months' => $monthsToGenerate,
                    'count' => count($monthsToGenerate),
                    'last_invoice' => $lastInvoice ?
                        $this->parseDate($lastInvoice->jatuh_tempo)->format('F Y') : 'Tidak ada'
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Tampilkan summary
        $this->showCheckSummary($customers->count(), $customersToGenerate, $totalToGenerate, $period, $strategy);

        // Tampilkan detail jika ada yang perlu digenerate
        if (!empty($reportData)) {
            $this->showGenerationDetails($reportData);
        }

        return 0;
    }

    /**
     * Tampilkan summary check
     */
    private function showCheckSummary($totalCustomers, $customersToGenerate, $totalToGenerate, $period, $strategy)
    {
        $this->info("🎯 SUMMARY ({$strategy}):");
        $this->table(
            ['Item', 'Jumlah', 'Persentase'],
            [
                ['Total Customer', $totalCustomers, '100%'],
                ['Customer perlu generate', $customersToGenerate,
                 round($customersToGenerate/$totalCustomers*100, 1) . '%'],
                ['Customer tidak perlu', $totalCustomers - $customersToGenerate,
                 round(($totalCustomers-$customersToGenerate)/$totalCustomers*100, 1) . '%'],
                ['Total invoice akan dibuat', $totalToGenerate, '-'],
                ['Rata-rata per customer', $customersToGenerate > 0 ?
                 round($totalToGenerate/$customersToGenerate, 1) : 0, '-'],
                ['Periode', $period['months'] . ' bulan',
                 $period['start']->format('M Y') . ' - ' . $period['end']->format('M Y')]
            ]
        );
    }

    /**
     * Tampilkan detail generate
     */
    private function showGenerationDetails($reportData)
    {
        // Urutkan berdasarkan jumlah terbanyak
        usort($reportData, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $this->info("📋 10 CUSTOMER DENGAN INVOICE TERBANYAK AKAN DIGENERATE:");

        $tableData = [];
        foreach (array_slice($reportData, 0, 10) as $item) {
            $customer = $item['customer'];
            $monthsPreview = implode(', ', array_slice($item['months'], 0, 3));
            if (count($item['months']) > 3) {
                $monthsPreview .= ', ...';
            }

            $tableData[] = [
                $customer->nama_customer,
                $customer->id,
                $item['last_invoice'],
                $item['count'],
                $monthsPreview
            ];
        }

        $this->table(
            ['Customer', 'ID', 'Invoice Terakhir', 'Jumlah', 'Contoh Bulan'],
            $tableData
        );
    }

    /**
     * Dry run mode
     */
    private function runDryRun($customers, $period, $strategy)
    {
        $this->info("🧪 DRY RUN MODE - SIMULASI GENERATE");
        $this->info("   (Tidak ada invoice yang benar-benar dibuat)");
        $this->info("   📌 STRATEGY: {$strategy}");
        $this->newLine();

        $simulationData = [];
        $totalToGenerate = 0;
        $customersToGenerate = 0;

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            $monthsToGenerate = $this->getMonthsToGenerate($customer, $period, $strategy);

            if (!empty($monthsToGenerate)) {
                $customersToGenerate++;
                $totalToGenerate += count($monthsToGenerate);

                $lastInvoice = Invoice::where('customer_id', $customer->id)
                    ->orderBy('jatuh_tempo', 'desc')
                    ->first();

                $simulationData[] = [
                    'customer' => $customer,
                    'months' => $monthsToGenerate,
                    'count' => count($monthsToGenerate),
                    'last_invoice' => $lastInvoice ?
                        $this->parseDate($lastInvoice->jatuh_tempo)->format('F Y') : 'Tidak ada'
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Tampilkan hasil simulasi
        $this->info("🎯 HASIL SIMULASI ({$strategy}):");
        $this->table(
            ['Item', 'Jumlah'],
            [
                ['Total Customer', $customers->count()],
                ['Customer perlu generate', $customersToGenerate],
                ['Customer tidak perlu', $customers->count() - $customersToGenerate],
                ['Total invoice akan dibuat', $totalToGenerate],
                ['Rata-rata per customer', $customersToGenerate > 0 ?
                 round($totalToGenerate/$customersToGenerate, 1) : 0]
            ]
        );

        // Tampilkan detail
        if (!empty($simulationData)) {
            $this->info("\n📋 CONTOH CUSTOMER YANG AKAN DIGENERATE:");

            // Urutkan berdasarkan jumlah terbanyak
            usort($simulationData, function ($a, $b) {
                return $b['count'] <=> $a['count'];
            });

            $tableData = [];
            foreach (array_slice($simulationData, 0, 5) as $data) {
                $customer = $data['customer'];
                $monthsPreview = implode(', ', array_slice($data['months'], 0, 3));
                if (count($data['months']) > 3) {
                    $monthsPreview .= ', ...';
                }

                $tableData[] = [
                    $customer->nama_customer,
                    $customer->id,
                    $data['last_invoice'],
                    $data['count'],
                    $monthsPreview
                ];
            }

            $this->table(
                ['Customer', 'ID', 'Invoice Terakhir', 'Jumlah', 'Contoh Bulan'],
                $tableData
            );
        }

        return 0;
    }

    /**
     * Mode generate sebenarnya
     */
    private function runGeneration($customers, $period, $strategy, $startTime)
    {
        $this->info("🚀 GENERATE MODE: {$strategy}");
        $this->newLine();

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            try {
                switch ($strategy) {
                    case 'FORCE_ALL':
                        $this->generateForAllMonths($customer, $period);
                        break;

                    case 'FIX_GAPS':
                        $this->generateForGapsOnly($customer, $period);
                        break;

                    case 'FROM_LAST_INVOICE':
                    default:
                        $this->generateFromLastInvoice($customer, $period);
                        break;
                }
            } catch (\Exception $e) {
                $this->statistics['errors']++;
                Log::error('Invoice generation error for customer: ' . $customer->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Tampilkan summary
        $this->showGenerationSummary($startTime, $strategy);

        // Simpan backup
        $this->saveGeneratedInvoicesBackup();

        return 0;
    }

    /**
     * Generate untuk semua bulan (force mode)
     */
    private function generateForAllMonths($customer, $period)
    {
        $currentMonth = clone $period['start'];

        while ($currentMonth->lte($period['end'])) {
            $monthKey = $currentMonth->format('Y-m');

            // Cek apakah sudah ada
            $exists = Invoice::where('customer_id', $customer->id)
                ->whereMonth('jatuh_tempo', $currentMonth->month)
                ->whereYear('jatuh_tempo', $currentMonth->year)
                ->exists();

            if (!$exists) {
                $this->createInvoice($customer, $currentMonth);
            } else {
                $this->statistics['skipped']++;
            }

            $currentMonth->addMonth();
        }
    }

    /**
     * Generate hanya untuk gap (fix gaps mode)
     */
    private function generateForGapsOnly($customer, $period)
    {
        // Ambil semua invoice dalam periode
        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereBetween('jatuh_tempo', [$period['start'], $period['end']])
            ->orderBy('jatuh_tempo')
            ->get();

        if ($invoices->isEmpty()) {
            // Jika tidak ada invoice sama sekali, generate untuk 3 bulan terakhir
            $startFrom = $period['end']->copy()->subMonths(2)->startOfMonth();
            $currentMonth = clone $startFrom;

            while ($currentMonth->lte($period['end'])) {
                $this->createInvoice($customer, $currentMonth);
                $currentMonth->addMonth();
            }
            return;
        }

        // Cari gap antara invoice
        $invoiceMonths = [];
        foreach ($invoices as $invoice) {
            $date = $this->parseDate($invoice->jatuh_tempo);
            $invoiceMonths[$date->format('Y-m')] = $date;
        }

        ksort($invoiceMonths);
        $months = array_keys($invoiceMonths);

        // Cek gap antara invoice
        for ($i = 0; $i < count($months) - 1; $i++) {
            $current = Carbon::createFromFormat('Y-m', $months[$i]);
            $next = Carbon::createFromFormat('Y-m', $months[$i + 1]);

            $diff = $current->diffInMonths($next);
            if ($diff > 1) {
                $gapMonth = $current->copy()->addMonth();
                while ($gapMonth->lt($next)) {
                    $this->createInvoice($customer, $gapMonth);
                    $this->statistics['gaps_fixed']++;
                    $gapMonth->addMonth();
                }
            }
        }

        // Cek gap setelah invoice terakhir dalam periode
        $lastMonth = Carbon::createFromFormat('Y-m', end($months));
        if ($lastMonth->lt($period['end'])) {
            $gapMonth = $lastMonth->copy()->addMonth();
            while ($gapMonth->lte($period['end'])) {
                $this->createInvoice($customer, $gapMonth);
                $this->statistics['gaps_fixed']++;
                $gapMonth->addMonth();
            }
        }
    }

    /**
     * Generate dari invoice terakhir (DEFAULT STRATEGY)
     */
    private function generateFromLastInvoice($customer, $period)
    {
        // 1. Cari invoice terakhir (apapun periodenya)
        $lastInvoice = Invoice::where('customer_id', $customer->id)
            ->orderBy('jatuh_tempo', 'desc')
            ->first();

        // 2. Tentukan mulai generate dari mana
        if ($lastInvoice) {
            $lastDate = $this->parseDate($lastInvoice->jatuh_tempo);
            $startFrom = $lastDate->copy()->addMonth()->startOfMonth();

            // Jika startFrom sebelum periode start, gunakan periode start
            if ($startFrom->lt($period['start'])) {
                $startFrom = clone $period['start'];
            }

            $this->statistics['from_last_invoice']++;

            if ($this->option('verbose')) {
                $this->info("👤 {$customer->nama_customer}: Invoice terakhir {$lastDate->format('F Y')}, " .
                           "mulai generate dari {$startFrom->format('F Y')}");
            }
        } else {
            // Jika belum ada invoice sama sekali, generate untuk 3 bulan terakhir
            $startFrom = $period['end']->copy()->subMonths(2)->startOfMonth();

            if ($this->option('verbose')) {
                $this->info("👤 {$customer->nama_customer}: Belum ada invoice, " .
                           "generate 3 bulan terakhir dari {$startFrom->format('F Y')}");
            }
        }

        // 3. Pastikan startFrom tidak setelah periode end
        if ($startFrom->gt($period['end'])) {
            // Tidak perlu generate, sudah update
            if ($this->option('verbose')) {
                $this->info("👤 {$customer->nama_customer}: Sudah update, tidak perlu generate");
            }
            return;
        }

        // 4. Ambil SEMUA invoice customer (dalam periode apapun)
        $allInvoices = Invoice::where('customer_id', $customer->id)
            ->orderBy('jatuh_tempo')
            ->get();

        $existingMonths = [];
        foreach ($allInvoices as $invoice) {
            $date = $this->parseDate($invoice->jatuh_tempo);
            $existingMonths[$date->format('Y-m')] = true;
        }

        // 5. Generate dari startFrom sampai periode end
        $currentMonth = clone $startFrom;
        $generatedCount = 0;

        while ($currentMonth->lte($period['end'])) {
            $monthKey = $currentMonth->format('Y-m');

            if (!isset($existingMonths[$monthKey])) {
                $this->createInvoice($customer, $currentMonth);
                $generatedCount++;
            } else {
                $this->statistics['skipped']++;
            }

            $currentMonth->addMonth();
        }

        // 6. Log hasil
        if ($this->option('verbose') && $generatedCount > 0) {
            $this->info("   ✅ Generated {$generatedCount} invoice baru");
        }
    }

    /**
     * Buat invoice baru
     */
    private function createInvoice($customer, $month)
    {
        $jatuhTempo = $month->copy()->endOfMonth();
        $merchant = 'INV-' . $customer->id . '-' . $month->format('Ym') . '-' . time();

        // Cek harga paket
        $tagihan = 0;
        if ($customer->paket && $customer->paket->harga) {
            $tagihan = $customer->paket->harga;
        } else {
            $this->statistics['zero_bills']++;
        }

        try {
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'paket_id'    => $customer->paket_id,
                'status_id'   => 7,
                'tagihan'     => $tagihan,
                'merchant_ref' => $merchant,
                'jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
                'keterangan'  => 'Auto-generated by system on ' . now()->format('d/m/Y H:i'),
                'created_at'  => now(),
            ]);

            $this->generatedInvoices[] = [
                'id' => $invoice->id,
                'customer_id' => $customer->id,
                'customer_name' => $customer->nama_customer,
                'bulan' => $month->format('F Y'),
                'jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
                'tagihan' => $tagihan,
                'created_at' => now()->format('Y-m-d H:i:s')
            ];

            $this->statistics['generated']++;

            if ($this->option('verbose')) {
                $this->info("✅ {$customer->nama_customer} - {$month->format('F Y')} - Rp " .
                    number_format($tagihan, 0, ',', '.'));
            }

            return $invoice;

        } catch (\Exception $e) {
            $this->error("❌ Gagal buat invoice untuk {$customer->nama_customer}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tampilkan summary generation
     */
    private function showGenerationSummary($startTime, $strategy)
    {
        $executionTime = round(microtime(true) - $startTime, 2);

        $this->info("🎯 GENERATION SUMMARY ({$strategy}):");
        $this->table(
            ['Item', 'Jumlah', 'Keterangan'],
            [
                ['Total Customer', $this->statistics['total_customers'], 'Customer aktif'],
                ['Total Bulan Periode', $this->statistics['total_months'], 'Rentang waktu'],
                ['Invoice Baru Dibuat', $this->statistics['generated'], 'Berhasil dibuat'],
                ['Invoice Sudah Ada', $this->statistics['skipped'], 'Dilewati'],
                ['Dari Invoice Terakhir', $this->statistics['from_last_invoice'], 'Customer'],
                ['Gap Difix', $this->statistics['gaps_fixed'], 'Celah ditutup'],
                ['Tagihan Rp 0', $this->statistics['zero_bills'], 'Paket tidak ditemukan'],
                ['Errors', $this->statistics['errors'], 'Gagal generate'],
                ['Waktu Eksekusi', $executionTime . 's', 'Total waktu']
            ]
        );

        // Warning jika ada tagihan 0
        if ($this->statistics['zero_bills'] > 0) {
            $this->warn("\n⚠️  {$this->statistics['zero_bills']} invoice dibuat dengan tagihan Rp 0");
            $this->warn("   Periksa relasi paket dan harga di database");
        }

        // Info backup
        if (!empty($this->generatedInvoices)) {
            $this->info("\n💾 Backup otomatis disimpan untuk rollback jika diperlukan");
            $this->info("   Gunakan: php artisan app:generate-invoice --rollback");
        }
    }

    /**
     * Simpan backup
     */
    private function saveGeneratedInvoicesBackup()
    {
        if (empty($this->generatedInvoices)) {
            return;
        }

        $backupDir = storage_path('app/backups/invoices/');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'invoice_backup_' . date('Ymd_His') . '.json';
        $filepath = $backupDir . $filename;

        $backupData = [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => $this->determinePeriod(),
            'statistics' => $this->statistics,
            'invoices' => $this->generatedInvoices
        ];

        file_put_contents($filepath, json_encode($backupData, JSON_PRETTY_PRINT));

        $this->info("\n📁 Backup disimpan: " . $filepath);
    }

    /**
     * Handle rollback
     */
    private function handleRollback()
    {
        $this->info("🔄 ROLLBACK MODE");
        $this->newLine();

        if ($this->option('rollback-all')) {
            return $this->rollbackAllAutoGenerated();
        }

        if ($this->option('rollback-date')) {
            return $this->rollbackByDate();
        }

        return $this->rollbackLastBatch();
    }

    /**
     * Rollback invoice yang baru saja digenerate
     */
    private function rollbackLastBatch()
    {
        $this->info("🔙 Rollback invoice yang baru digenerate");

        // Cari file backup terakhir
        $backupFile = $this->getLatestBackupFile();

        if (!$backupFile) {
            $this->error("❌ Tidak ditemukan file backup untuk rollback");
            $this->info("💡 Gunakan --rollback-date atau --rollback-all untuk opsi lain");
            return 1;
        }

        $backupData = json_decode(file_get_contents($backupFile), true);

        if (empty($backupData['invoices'])) {
            $this->error("❌ Tidak ada data invoice di file backup");
            return 1;
        }

        $this->info("📁 Menggunakan backup file: " . basename($backupFile));
        $this->info("📊 Akan di-rollback: " . count($backupData['invoices']) . " invoice");

        if (!$this->confirm('Apakah Anda yakin ingin melakukan rollback?', false)) {
            $this->info("❌ Rollback dibatalkan");
            return 0;
        }

        $deletedCount = 0;
        foreach ($backupData['invoices'] as $invoiceData) {
            try {
                $invoice = Invoice::find($invoiceData['id']);

                if ($invoice) {
                    $invoice->delete();
                    $this->info("🗑️  Invoice {$invoiceData['bulan']} untuk {$invoiceData['customer_name']} dihapus");
                    $deletedCount++;
                } else {
                    $this->warn("⚠️  Invoice ID {$invoiceData['id']} tidak ditemukan");
                }
            } catch (\Exception $e) {
                $this->error("❌ Gagal menghapus invoice ID {$invoiceData['id']}: " . $e->getMessage());
            }
        }

        // Hapus file backup setelah rollback
        unlink($backupFile);

        $this->info("\n✅ Rollback selesai! {$deletedCount} invoice berhasil dihapus");
        $this->info("📁 File backup dihapus: " . basename($backupFile));

        return 0;
    }

    /**
     * Rollback by date
     */
    private function rollbackByDate()
    {
        $dateStr = $this->option('rollback-date');

        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $this->error("❌ Format tanggal tidak valid. Gunakan format: YYYY-MM-DD");
            return 1;
        }

        $this->info("🔙 Rollback invoice setelah tanggal: " . $date->format('d F Y'));

        // Cari invoice yang auto-generated setelah tanggal tertentu
        $invoices = Invoice::where('keterangan', 'like', '%Auto-generated%')
            ->whereDate('created_at', '>=', $date)
            ->get();

        $this->info("📊 Ditemukan: " . $invoices->count() . " invoice untuk di-rollback");

        if ($invoices->isEmpty()) {
            $this->info("✅ Tidak ada invoice yang perlu di-rollback");
            return 0;
        }

        // Preview invoice yang akan dihapus
        $this->info("\n📋 PREVIEW INVOICE YANG AKAN DIHAPUS:");
        $this->table(
            ['Customer', 'Bulan', 'Jatuh Tempo', 'Tagihan', 'Created'],
            $invoices->map(function ($invoice) {
                return [
                    $invoice->customer->nama_customer ?? 'N/A',
                    Carbon::parse($invoice->jatuh_tempo)->format('F Y'),
                    $invoice->jatuh_tempo,
                    'Rp ' . number_format($invoice->tagihan, 0, ',', '.'),
                    $invoice->created_at->format('d-m-Y H:i')
                ];
            })
        );

        if (!$this->confirm('Apakah Anda yakin ingin menghapus invoice di atas?', false)) {
            $this->info("❌ Rollback dibatalkan");
            return 0;
        }

        $deletedCount = 0;
        foreach ($invoices as $invoice) {
            try {
                $customerName = $invoice->customer->nama_customer ?? 'N/A';
                $invoice->delete();
                $this->info("🗑️  Invoice untuk {$customerName} dihapus");
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("❌ Gagal menghapus invoice ID {$invoice->id}: " . $e->getMessage());
            }
        }

        $this->info("\n✅ Rollback selesai! {$deletedCount} invoice berhasil dihapus");

        return 0;
    }

    /**
     * Rollback semua invoice yang auto-generated
     */
    private function rollbackAllAutoGenerated()
    {
        $this->info("🔙 Rollback SEMUA invoice yang auto-generated");

        $invoices = Invoice::where('keterangan', 'like', '%Auto-generated%')
            ->orWhere('keterangan', 'like', '%Generated untuk%')
            ->orWhere('keterangan', 'like', '%Auto-generated by system%')
            ->get();

        $this->info("📊 Ditemukan: " . $invoices->count() . " invoice auto-generated");

        if ($invoices->isEmpty()) {
            $this->info("✅ Tidak ada invoice auto-generated");
            return 0;
        }

        // Group by customer untuk summary
        $summary = $invoices->groupBy('customer_id')->map(function ($customerInvoices) {
            return [
                'customer_name' => $customerInvoices->first()->customer->nama_customer ?? 'N/A',
                'invoice_count' => $customerInvoices->count(),
                'total_amount' => $customerInvoices->sum('tagihan')
            ];
        });

        $this->info("\n📋 SUMMARY INVOICE AUTO-GENERATED:");
        $this->table(
            ['Customer', 'Jumlah Invoice', 'Total Tagihan'],
            $summary->map(function ($item) {
                return [
                    $item['customer_name'],
                    $item['invoice_count'],
                    'Rp ' . number_format($item['total_amount'], 0, ',', '.')
                ];
            })
        );

        if (!$this->confirm('Apakah Anda yakin ingin menghapus SEMUA invoice auto-generated di atas?', false)) {
            $this->info("❌ Rollback dibatalkan");
            return 0;
        }

        $deletedCount = 0;
        foreach ($invoices as $invoice) {
            try {
                $invoice->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("❌ Gagal menghapus invoice ID {$invoice->id}: " . $e->getMessage());
            }
        }

        $this->info("\n✅ Rollback selesai! {$deletedCount} invoice auto-generated berhasil dihapus");

        return 0;
    }

    /**
     * Cari file backup terakhir
     */
    private function getLatestBackupFile()
    {
        $backupDir = storage_path('app/backups/invoices/');

        if (!file_exists($backupDir)) {
            return null;
        }

        $files = glob($backupDir . 'invoice_backup_*.json');

        if (empty($files)) {
            return null;
        }

        // Sort by modification time (newest first)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files[0];
    }

    /**
     * Parse date dari string atau Carbon
     */
    private function parseDate($date)
    {
        return is_string($date) ? Carbon::parse($date) : $date;
    }
}
