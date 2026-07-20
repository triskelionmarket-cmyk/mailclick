<?php

namespace Acelle\Console\Commands;

use Illuminate\Console\Command;
use Acelle\Model\SendingServer;
use Acelle\Model\SendingServerTwilio;

class TestTwilioServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:twilio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Twilio sending server class compatibility';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing SendingServerTwilio class...');
        
        try {
            // Test that the class can be instantiated
            $server = new SendingServerTwilio();
            $this->info('✅ Class instantiated successfully');
            
            // Test method inheritance
            $type = SendingServer::TYPE_TWILIO;
            $rules = SendingServer::rules($type);
            $this->info('✅ Parent class rules() method works');
            
            // Test that calling rules() from child class works
            $childRules = SendingServerTwilio::rules($type);
            $this->info('✅ Child class inherits rules() method properly');
            
            // Test that an instance can use getRules()
            $server->type = $type;
            $instanceRules = $server->getRules();
            $this->info('✅ Instance can use getRules() successfully');
            
            $this->info('All tests passed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
