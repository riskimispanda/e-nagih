<?php

namespace App\Console\Commands;

use App\Events\UpdateBaru;
use Illuminate\Console\Command;

class TestBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:broadcast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test broadcasting an event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Broadcasting test event...');
        
        event(new UpdateBaru(
            ['id' => 1, 'name' => 'Test Customer'],
            'success',
            'This is a test broadcast message'
        ));
        
        $this->info('Event broadcasted successfully!');
        
        return Command::SUCCESS;
    }
}
