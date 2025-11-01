<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

class SyncOrdersJob extends BaseSyncJob
{
    public function handle(ApiService $apiService): void
    {
        try {
            $apiService->setAccount($this->account);

            $this->logToConsole("Starting orders sync from {$this->dateFrom} to {$this->dateTo}");

            $this->syncData($apiService);
        } catch (\Exception $e) {
            Log::error("SyncOrdersJob failed for account {$this->account->name}", [
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
        $generator = $apiService->fetchPaginatedData('/api/orders', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);

        $total = 0;
        foreach ($generator as $ordersChunk) {
            foreach ($ordersChunk as $orderData) {
                Order::updateOrCreate(
                    [
                        'g_number' => $orderData['g_number'],
                        'account_id' => $this->account->id,
                        'nm_id' => $orderData['nm_id'],
                        'supplier_article' => $orderData['supplier_article'],
                        'date' => $orderData['date']
                    ],
                    $this->transformOrderData($orderData)
                );
            }
            $chunkCount = count($ordersChunk);
            $total += $chunkCount;
            $this->logToConsole("Processed {$chunkCount} orders (total: {$total})");
        }

        $this->logToConsole("Orders sync completed: {$total} records");
    }

    private function transformOrderData(array $data): array
    {
        return [
            'account_id' => $this->account->id,
            'g_number' => $data['g_number'],
            'date' => $data['date'],
            'last_change_date' => $data['last_change_date'],
            'supplier_article' => $data['supplier_article'],
            'tech_size' => $data['tech_size'],
            'barcode' => $data['barcode'],
            'total_price' => $data['total_price'],
            'discount_percent' => $data['discount_percent'],
            'warehouse_name' => $data['warehouse_name'],
            'oblast' => $data['oblast'],
            'income_id' => $data['income_id'],
            'odid' => $data['odid'],
            'nm_id' => $data['nm_id'],
            'subject' => $data['subject'],
            'category' => $data['category'],
            'brand' => $data['brand'],
            'is_cancel' => $data['is_cancel'],
            'cancel_dt' => $data['cancel_dt'],
            'data' => $data
        ];
    }
}
