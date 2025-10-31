<?php
// app/Console/Commands/AddApiToken.php
namespace App\Console\Commands;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\ApiToken;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AddApiToken extends Command
{
    protected $signature = 'api-token:add {account_id} {api_service_id} {token_type_id} {token_value}';
    protected $description = 'Add API token to an account';

    public function handle()
    {
        $account = Account::find($this->argument('account_id'));
        $apiService = ApiService::find($this->argument('api_service_id'));
        $tokenType = TokenType::find($this->argument('token_type_id'));

        if (!$account) {
            $this->error('Account not found');
            return Command::FAILURE;
        }

        if (!$apiService) {
            $this->error('API Service not found');
            return Command::FAILURE;
        }

        if (!$tokenType) {
            $this->error('Token type not found');
            return Command::FAILURE;
        }

        
        if (!$apiService->tokenTypes->contains($tokenType)) {
            $this->error("API Service '{$apiService->name}' does not support token type '{$tokenType->name}'");
            return Command::FAILURE;
        }

        $apiToken = ApiToken::create([
            'account_id' => $account->id,
            'api_service_id' => $apiService->id,
            'token_type_id' => $tokenType->id,
            'token_value' => $this->argument('token_value')
        ]);

        $this->info("API Token created successfully for account '{$account->name}'");
        $this->info("Service: {$apiService->name}, Token Type: {$tokenType->name}");
        return Command::SUCCESS;
    }
}