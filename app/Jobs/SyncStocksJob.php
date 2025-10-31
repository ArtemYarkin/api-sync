<?php
// app/Jobs/SyncStocksJob.php
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

    public function __construct(
        protected \App\Models\Account $account
    ) {}

    public function handle(ApiService $apiService): void
    {
        try {
            $apiService->setAccount($this->account);
            $today = now()->format('Y-m-d');
            $this->logToConsole("Starting stocks sync for {$today}");
            $generator = $apiService->fetchPaginatedData('/api/stocks', [
                'dateFrom' => $today,
            ]);

            $total = 0;
            foreach ($generator as $stocksChunk) {
                foreach ($stocksChunk as $stockData) {
                    Stock::firstOrCreate(
                        [
                            'data' => $stockData,
                            'account_id' => $this->account->id
                        ],
                        $this->transformStockData($stockData, $today)
                    );
                }
                $chunkCount = count($stocksChunk);
                $total += $chunkCount;
                $this->logToConsole("Processed {$chunkCount} stocks (total: {$total})");
            }

            $this->logToConsole("Stocks sync completed: {$total} records");
        } catch (\Exception $e) {
            Log::error("SyncStocksJob failed for account {$this->account->name}", [
                'error' => $e->getMessage()
            ]);

            $this->logToConsole("Sync failed: {$e->getMessage()}");

            throw $e;
        }
    }

    private function transformStockData(array $data, string $syncDate): array
    {
        return [
            'account_id' => $this->account->id,
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

    private function logToConsole(string $message): void
    {
        echo "[" . now()->format('Y-m-d H:i:s') . "] [Account: {$this->account->name}] {$message}\n";
    }
}
