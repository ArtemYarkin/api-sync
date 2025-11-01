<?php

namespace App\Jobs;

use App\Models\Income;
use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

class SyncIncomesJob extends BaseSyncJob
{
    public function handle(ApiService $apiService): void
    {
        try {
            $apiService->setAccount($this->account);

            $this->logToConsole("Starting incomes sync from {$this->dateFrom} to {$this->dateTo}");

            $this->syncData($apiService);
        } catch (\Exception $e) {
            Log::error("SyncIncomesJob failed for account {$this->account->name}", [
                'error' => $e->getMessage(),
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo
            ]);

            $this->logToConsole("Sync failed: {$e->getMessage()}");

            throw $e;
        }
    }

    protected function syncData(ApiService $apiService): void
    {
        $generator = $apiService->fetchPaginatedData('/api/incomes', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);

        $total = 0;
        foreach ($generator as $incomesChunk) {
            foreach ($incomesChunk as $incomeData) {
                Income::updateOrCreate(
                    [
                        'income_id' => $incomeData['income_id'],
                        'account_id' => $this->account->id,
                        'nm_id' => $incomeData['nm_id'],
                        'date' => $incomeData['date']
                    ],
                    $this->transformIncomeData($incomeData)
                );
            }
            $chunkCount = count($incomesChunk);
            $total += $chunkCount;
            $this->logToConsole("Processed {$chunkCount} incomes (total: {$total})");
        }

        $this->logToConsole("Incomes sync completed: {$total} records");
    }

    private function transformIncomeData(array $data): array
    {
        return [
            'account_id' => $this->account->id,
            'income_id' => $data['income_id'],
            'number' => $data['number'],
            'date' => $data['date'],
            'last_change_date' => $data['last_change_date'],
            'supplier_article' => $data['supplier_article'],
            'tech_size' => $data['tech_size'],
            'barcode' => $data['barcode'],
            'quantity' => $data['quantity'],
            'total_price' => $data['total_price'],
            'date_close' => $data['date_close'],
            'warehouse_name' => $data['warehouse_name'],
            'nm_id' => $data['nm_id'],
            'data' => $data
        ];
    }
}
