<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Hello World!');
            return 0;
        } catch (\Throwable $e) {
            Log::error("Error in test-command: " . $e->getMessage());
            $this->error('An error occurred');
            return 1;
        }
    }
}
