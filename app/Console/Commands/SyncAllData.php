<?php

namespace App\Console\Commands;

use App\Jobs\SyncSalesJob;
use App\Jobs\SyncOrdersJob;
use App\Jobs\SyncStocksJob;
use App\Jobs\SyncIncomesJob;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAllData extends Command
{
    protected $signature = 'sync:all 
                            {--dateFrom= : Start date (Y-m-d)} 
                            {--dateTo= : End date (Y-m-d)} ';

    protected $description = 'Sync all data from API';

    public function handle(): int
    {
        $dateFrom = $this->option('dateFrom') ?? now()->subDays(7)->format('Y-m-d');
        $dateTo = $this->option('dateTo') ?? now()->format('Y-m-d');
        
        $this->info("Starting sync from {$dateFrom} to {$dateTo}");
        $jobs = [
            new SyncSalesJob($dateFrom, $dateTo),
            new SyncOrdersJob($dateFrom, $dateTo),
            new SyncIncomesJob($dateFrom, $dateTo),
            new SyncStocksJob()
        ];

        $batch = Bus::batch($jobs)->dispatch();

        $this->info("Sync jobs dispatched successfully! Batch ID: {$batch->id}");

        return Command::SUCCESS;
    }
}