<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrdersJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $dateFrom,
        private string $dateTo
    ) {}

public function handle(ApiService $apiService): void
{
    try {
        $generator = $apiService->fetchPaginatedData('/api/orders', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);

        $totalProcessed = 0;
        
        foreach ($generator as $ordersChunk) {
            foreach ($ordersChunk as $orderData) {
                Order::updateOrCreate(
                    $this->transformOrderData($orderData)
                );
            }         
            $chunkCount = count($ordersChunk);
            $totalProcessed += $chunkCount;
        
        }
        
        
    } catch (\Exception $e) {
        Log::error("SyncOrdersJob failed", [
            'error' => $e->getMessage(),
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo
        ]);
        
        throw $e;
    }
}

private function transformOrderData(array $data): array
{
    return [
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