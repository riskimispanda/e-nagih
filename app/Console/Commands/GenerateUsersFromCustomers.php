<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class GenerateUsersFromCustomers extends Command
{
    protected $signature = 'generate:users
                            {--batch=100 : Batch size}
                            {--test : Test mode (no database changes)}
                            {--limit=0 : Limit number of customers to process}';

    protected $description = 'Generate user accounts from existing customers';

    public function handle()
    {
        // Deteksi model customer
        $customerModel = $this->getCustomerModel();

        if (!$customerModel) {
            $this->error('Customer model not found!');
            return 1;
        }

        $this->info('========================================');
        $this->info('GENERATE USERS FROM CUSTOMERS');
        $this->info('========================================');
        $this->info('Model: ' . $customerModel);
        $this->info('Table: ' . (new $customerModel)->getTable());

        $totalCustomers = $customerModel::count();
        $this->info('Total Customers: ' . $totalCustomers);

        if ($totalCustomers === 0) {
            $this->error('No customer data found!');
            return 1;
        }

        $batchSize = (int) $this->option('batch');
        $testMode = $this->option('test');
        $limit = (int) $this->option('limit');

        if ($limit > 0 && $limit < $totalCustomers) {
            $totalCustomers = $limit;
        }

        $this->info('Batch Size: ' . $batchSize);
        $this->info('Test Mode: ' . ($testMode ? 'ON' : 'OFF'));
        $this->info('Limit: ' . ($limit > 0 ? $limit : 'No limit'));
        $this->info('');

        if (!$this->confirm('Do you want to continue?')) {
            $this->info('Process cancelled.');
            return 0;
        }

        $successCount = 0;
        $failedCount = 0;
        $alreadyHasUser = 0;
        $processedCount = 0;

        if (!$testMode) {
            DB::beginTransaction();
        }

        $bar = $this->output->createProgressBar($totalCustomers);
        $bar->start();

        try {
            $query = $customerModel::query();
            if ($limit > 0) {
                $query->limit($limit);
            }

            $query->chunk($batchSize, function ($customers) use (
                &$successCount,
                &$failedCount,
                &$alreadyHasUser,
                &$processedCount,
                $customerModel,
                $testMode,
                $bar
            ) {
                foreach ($customers as $customer) {
                    $processedCount++;
                    $bar->advance();

                    try {
                        // Skip jika sudah punya user
                        if (!empty($customer->user_id) && User::where('id', $customer->user_id)->exists()) {
                            $alreadyHasUser++;
                            continue;
                        }

                        // Ambil nama customer
                        $name = $this->getCustomerName($customer);

                        // Buat email unik
                        $email = $this->generateUniqueEmail($name);

                        if (!$testMode) {
                            // Buat user
                            $user = User::create([
                                'name' => $name,
                                'email' => $email,
                                'password' => bcrypt('password123'),
                                'roles_id' => 8,
                                'email_verified_at' => now(),
                            ]);

                            // Update customer
                            $customer->user_id = $user->id;
                            $customer->save();
                        }

                        $successCount++;

                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Error creating user for customer ID {$customer->id}: " . $e->getMessage());
                    }
                }
            });

            $bar->finish();
            $this->newLine(2);

            if (!$testMode) {
                DB::commit();
            }

            // Tampilkan hasil
            $this->info('========================================');
            $this->info('✅ PROCESS COMPLETED!');
            $this->info('========================================');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Customers', $totalCustomers],
                    ['Processed', $processedCount],
                    ['Successfully Created', $successCount],
                    ['Already Had User', $alreadyHasUser],
                    ['Failed', $failedCount],
                ]
            );

            $this->info('');
            $this->info('Login Information:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Email Format', 'nama.customer@niscala.net'],
                    ['Password', 'password123'],
                    ['Role ID', '8 (Customer)'],
                ]
            );

            if ($testMode) {
                $this->warn('⚠️ TEST MODE: No database changes were made.');
            }

        } catch (\Exception $e) {
            $bar->finish();

            if (!$testMode) {
                DB::rollBack();
            }

            $this->error('❌ ERROR: ' . $e->getMessage());
            Log::error('Generate Users Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function getCustomerModel()
    {
        if (class_exists('App\Models\Customer')) {
            return 'App\Models\Customer';
        } elseif (class_exists('App\Models\Customers')) {
            return 'App\Models\Customers';
        } elseif (class_exists('App\Customer')) {
            return 'App\Customer';
        }

        return null;
    }

    private function getCustomerName($customer)
    {
        if (!empty($customer->nama_customer)) {
            return $customer->nama_customer;
        } elseif (!empty($customer->name)) {
            return $customer->name;
        } elseif (!empty($customer->nama)) {
            return $customer->nama;
        } elseif (!empty($customer->customer_name)) {
            return $customer->customer_name;
        }

        return 'Customer ' . $customer->id;
    }

    private function generateUniqueEmail($name)
    {
        $baseEmail = strtolower(preg_replace('/[^\w]/', '.', $name));
        $baseEmail = preg_replace('/\.+/', '.', $baseEmail);
        $baseEmail = trim($baseEmail, '.');

        $email = $baseEmail . '@niscala.net';
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@niscala.net';
            $counter++;
        }

        return $email;
    }
}
