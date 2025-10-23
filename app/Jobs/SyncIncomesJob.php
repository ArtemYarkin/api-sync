<?php

namespace App\Jobs;

use App\Models\Income;
use App\Services\ApiService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncIncomesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $dateFrom,
        private string $dateTo
    ) {}

public function handle(ApiService $apiService): void
{
    try {
        $generator = $apiService->fetchPaginatedData('/api/incomes', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);

        $totalProcessed = 0;
        
        foreach ($generator as $incomesChunk) {
            foreach ($incomesChunk as $incomeData) {
                Income::updateOrCreate( 
                    $this->transformIncomeData($incomeData)
                );
            }      
            $chunkCount = count($incomesChunk);
            $totalProcessed += $chunkCount;
            
        }
        
        
    } catch (\Exception $e) {
        Log::error("SyncIncomesJob failed", [
            'error' => $e->getMessage(),
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo
        ]);
        
        throw $e;
    }
}

private function transformIncomeData(array $data): array
{
    return [
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