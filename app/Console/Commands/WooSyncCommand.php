<?php

namespace Acelle\Console\Commands;

use Illuminate\Console\Command;
use Acelle\Model\WooStore;
use Acelle\Jobs\WooSyncJob;

class WooSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'woo:sync {store_id? : Optional WooStore ID to sync specific store} {--full : Perform full sync instead of incremental}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizează datele din WooCommerce (produse, comenzi, clienți, recenzii, categorii)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $storeId = $this->argument('store_id');
        $full = $this->option('full');

        if ($storeId) {
            $stores = WooStore::where('id', $storeId)->get();
        } else {
            $stores = WooStore::all();
        }

        if ($stores->isEmpty()) {
            $this->info('Niciun magazin WooCommerce găsit pentru sincronizare.');
            return 0;
        }

        foreach ($stores as $store) {
            $this->info("Pornire sincronizare magazin: {$store->store_name} (#{$store->id})...");
            
            // Dispatch job asynchronously or run synchronously if CLI
            WooSyncJob::dispatchSync($store, (bool) $full);
            
            $this->info("✅ Sincronizare finalizată pentru {$store->store_name}.");
        }

        return 0;
    }
}
