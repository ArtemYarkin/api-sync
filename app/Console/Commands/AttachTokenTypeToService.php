<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AttachTokenTypeToService extends Command
{
    protected $signature = 'api-service:attach-token-type {api_service_id} {token_type_id}';
    protected $description = 'Attach token type to API service';

    public function handle()
    {
        $apiService = ApiService::find($this->argument('api_service_id'));
        $tokenType = TokenType::find($this->argument('token_type_id'));

        if (!$apiService) {
            $this->error('API Service not found');
            return Command::FAILURE;
        }

        if (!$tokenType) {
            $this->error('Token type not found');
            return Command::FAILURE;
        }

        $apiService->tokenTypes()->attach($tokenType);

        $this->info("Token type '{$tokenType->name}' attached to API service '{$apiService->name}'");
        return Command::SUCCESS;
    }
}