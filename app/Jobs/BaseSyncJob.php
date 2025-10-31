<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\ApiService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


abstract class BaseSyncJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Account $account,
        protected string $dateFrom,
        protected string $dateTo
    ) {}

    protected function logToConsole(string $message): void
    {
        echo "[" . now()->format('Y-m-d H:i:s') . "] [Account: {$this->account->name}] {$message}\n";
    }

    abstract protected function syncData(ApiService $apiService): void;
}