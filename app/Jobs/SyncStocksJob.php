<?php

namespace App\Jobs;

use App\Models\Stock;
use App\Services\ApiService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncStocksJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

public function handle(ApiService $apiService): void
{
    try {
        $today = now()->format('Y-m-d');
        $generator = $apiService->fetchPaginatedData('/api/stocks', [
            'dateFrom' => $today,
        ]);

        $totalProcessed = 0;
        
        foreach ($generator as $stocksChunk) {
            foreach ($stocksChunk as $stockData) {
                Stock::updateOrCreate(
                    $this->transformStockData($stockData, $today)
                );
            }           
            $chunkCount = count($stocksChunk);
            $totalProcessed += $chunkCount;
            
        }
        
        
    } catch (\Exception $e) {
        Log::error("SyncStocksJob failed", [
            'error' => $e->getMessage()
        ]);
        
        throw $e;
    }
}

private function transformStockData(array $data, string $syncDate): array
{
    return [
        'date' => $data['date'] ?? $syncDate,
        'last_change_date' => $data['last_change_date'],
        'supplier_article' => $data['supplier_article'],
        'tech_size' => $data['tech_size'],
        'barcode' => $data['barcode'],
        'quantity' => $data['quantity'],
        'is_supply' => $data['is_supply'],
        'is_realization' => $data['is_realization'],
        'quantity_full' => $data['quantity_full'],
        'warehouse_name' => $data['warehouse_name'],
        'in_way_to_client' => $data['in_way_to_client'],
        'in_way_from_client' => $data['in_way_from_client'],
        'nm_id' => $data['nm_id'],
        'subject' => $data['subject'],
        'category' => $data['category'],
        'brand' => $data['brand'],
        'sc_code' => $data['sc_code'],
        'price' => $data['price'],
        'discount' => $data['discount'],
        'sync_date' => $syncDate,
        'data' => $data
    ];
}
}