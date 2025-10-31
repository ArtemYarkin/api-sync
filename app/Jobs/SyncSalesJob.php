<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

class SyncSalesJob extends BaseSyncJob
{
    public function handle(ApiService $apiService): void
    {
        try {
            $apiService->setAccount($this->account);
            
            $this->logToConsole("Starting sales sync from {$this->dateFrom} to {$this->dateTo}");
            
            $this->syncData($apiService);
            
        } catch (\Exception $e) {
            Log::error("SyncSalesJob failed for account {$this->account->name}", [
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
        $generator = $apiService->fetchPaginatedData('/api/sales', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);

        $total = 0;
        foreach ($generator as $salesChunk) {
            foreach ($salesChunk as $saleData) {
                Sale::firstOrCreate([
                    'data' => $saleData,
                    'account_id' => $this->account->id
                ],
                    $this->transformSaleData($saleData)
                );
            }
            $chunkCount = count($salesChunk);
            $total += $chunkCount;
            $this->logToConsole("Processed {$chunkCount} sales (total: {$total})");
        }
        
        $this->logToConsole("Sales sync completed: {$total} records");
    }

    private function transformSaleData(array $data): array
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
            'is_supply' => $data['is_supply'],
            'is_realization' => $data['is_realization'],
            'promo_code_discount' => $data['promo_code_discount'],
            'warehouse_name' => $data['warehouse_name'],
            'country_name' => $data['country_name'],
            'oblast_okrug_name' => $data['oblast_okrug_name'],
            'region_name' => $data['region_name'],
            'income_id' => $data['income_id'],
            'sale_id' => $data['sale_id'],
            'odid' => $data['odid'],
            'spp' => $data['spp'],
            'for_pay' => $data['for_pay'],
            'finished_price' => $data['finished_price'],
            'price_with_disc' => $data['price_with_disc'],
            'nm_id' => $data['nm_id'],
            'subject' => $data['subject'],
            'category' => $data['category'],
            'brand' => $data['brand'],
            'is_storno' => $data['is_storno'],
            'data' => $data
        ];
    }
}