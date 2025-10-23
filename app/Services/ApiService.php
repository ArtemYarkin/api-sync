<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.api.base_url'), '/');
        $this->token = config('services.api.token');
    }

    public function fetchData(string $endpoint, array $params = []): ?array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $queryParams = array_merge($params, ['key' => $this->token]);
            
            
            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->get($url, $queryParams);
            if ($response->successful()) {
                $data = $response->json();
                return $data;
            }
            
    
            return null;
            
        } catch (\Exception $e) {
            Log::error("API request exception", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    public function fetchPaginatedData(string $endpoint, array $params = [], int $limit = 500): \Generator
    {
        $page = 1;
        $totalProcessed = 0;
        
        do {
            $params['page'] = $page;
            $params['limit'] = $limit;
            
            $data = $this->fetchData($endpoint, $params);
            
            if (empty($data['data'])) {
                break;
            }
            
            $chunkCount = count($data['data']);
            $totalProcessed += $chunkCount;
            
            yield $data['data'];
            
            $page++;
            

            sleep(1);
            
        } while ($chunkCount === $limit);
    }
}