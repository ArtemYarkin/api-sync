<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    private ?Account $account;
    private ?ApiToken $apiToken;


    public function setAccount(Account $account): self
    {
        $this->account = $account;


        $this->apiToken = $account->apiTokens()->first();

        if (!$this->apiToken) {
            throw new \Exception("No API token found for account {$account->name}");
        }

        $isTokenTypeSupported = $this->apiToken->apiService
            ->tokenTypes()
            ->where('token_types.id', $this->apiToken->token_type_id)
            ->exists();

        if (!$isTokenTypeSupported) {
            throw new \Exception(
                "Token type '{$this->apiToken->tokenType->name}' is not supported by API service '{$this->apiToken->apiService->name}'"
            );
        }

        return $this;
    }


    public function fetchData(string $endpoint, array $params = []): ?array
    {
        $attempts = 0;
        $maxAttempts = 5;
        while ($attempts < $maxAttempts) {
            try {
                $baseUrl = $this->apiToken->apiService->base_url;
                $url = rtrim($baseUrl, '/') . $endpoint;

                $queryParams = array_merge($params, $this->buildAuthParams());

                $this->logToConsole("API Request to: {$endpoint} for account: {$this->account->name}");

                $response = Http::timeout(60)->get($url, $queryParams);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->logToConsole("API Response Success: " . count($data['data'] ?? []) . " items");
                    return $data;
                }

                if ($response->status() === 429) {
                    $attempts++;
                    $this->logToConsole("Rate limit hit. Attempt {$attempts}/{$maxAttempts}");
                    sleep(60);
                    continue;
                }

                $this->logToConsole("API Error: {$response->status()} - {$response->body()}");

                return null;
            } catch (\Exception $e) {
                Log::error("API request exception", [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);
                $this->logToConsole("API Exception: {$e->getMessage()}");
                return null;
            }
        }
        $this->logToConsole("Max attempts reached for {$endpoint}");
        return null;
    }

    private function buildAuthParams(): array
    {
        $tokenType = $this->apiToken->tokenType->name;
        $tokenValue = $this->apiToken->token_value;

        switch ($tokenType) {
            case 'api-key':
                return ['key' => $tokenValue];
            case 'bearer':
                return ['Authorization' => "Bearer {$tokenValue}"];
            case 'basic':
                return ['Authorization' => "Basic {$tokenValue}"];
            case 'login-password':
                $credentials = explode('-', $tokenValue, 2);
                if (count($credentials) === 2) {
                    return [
                        'login' => $credentials[0],
                        'password' => $credentials[1]
                    ];
                }
            default:
                return ['key' => $tokenValue];
        }
    }

    private function logToConsole(string $message): void
    {
        echo "[" . now()->format('Y-m-d H:i:s') . "] {$message}\n";
    }

    public function fetchPaginatedData(string $endpoint, array $params = [], int $limit = 500): \Generator
    {
        $page = 1;
        $totalProcessed = 0;

        $this->logToConsole("Starting paginated data fetch for {$endpoint}");

        do {
            $params['page'] = $page;
            $params['limit'] = $limit;

            $data = $this->fetchData($endpoint, $params);

            if (empty($data['data'])) {
                $this->logToConsole("No more data available at page {$page}");
                break;
            }

            $chunkCount = count($data['data']);
            $totalProcessed += $chunkCount;

            yield $data['data'];

            $page++;


            sleep(1);
        } while ($chunkCount === $limit);

        $this->logToConsole("Finished processing: {$totalProcessed} total items");
    }
}
