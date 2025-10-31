<?php

namespace App\Console\Commands;

use App\Jobs\SyncSalesJob;
use App\Jobs\SyncOrdersJob;
use App\Jobs\SyncStocksJob;
use App\Jobs\SyncIncomesJob;
use App\Models\Account;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAllData extends Command
{
     protected $signature = 'sync:all 
                            {--account= : Account ID to sync}
                            {--dateFrom= : Start date (Y-m-d)} 
                            {--dateTo= : End date (Y-m-d)} 
                            {--all-accounts : Sync all accounts}';

    protected $description = 'Sync data from API for specific account or all accounts';

    public function handle(): int
    {
        $dateFrom = $this->option('dateFrom') ?? now()->subDays(7)->format('Y-m-d');
        $dateTo = $this->option('dateTo') ?? now()->format('Y-m-d');
        
        $accounts = $this->getAccountsToSync();

        if ($accounts->isEmpty()) {
            $this->error('No accounts found to sync');
            return Command::FAILURE;
        }

        $this->info("Starting sync from {$dateFrom} to {$dateTo} for " . $accounts->count() . " accounts");

        foreach ($accounts as $account) {
            $this->syncAccount($account, $dateFrom, $dateTo);
        }

        $this->info("Sync jobs dispatched successfully!");

        return Command::SUCCESS;
    }

    private function getAccountsToSync()
    {
        if ($this->option('all-accounts')) {
            return Account::with('apiTokens')->get();
        }

        if ($this->option('account')) {
            return Account::where('id', $this->option('account'))->with('apiTokens')->get();
        }

        $this->error('Please specify --account=ID or --all-accounts');
        return collect();
    }

    private function syncAccount(Account $account, string $dateFrom, string $dateTo): void
    {
        $jobs = [
            new SyncSalesJob($account, $dateFrom, $dateTo),
            new SyncOrdersJob($account, $dateFrom, $dateTo),
            new SyncIncomesJob($account, $dateFrom, $dateTo),
            new SyncStocksJob($account)
        ];

        $batch = Bus::batch($jobs)->dispatch();

        $this->info("Dispatched sync jobs for account: {$account->name}");
    }
}