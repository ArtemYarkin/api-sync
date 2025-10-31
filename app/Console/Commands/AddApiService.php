<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use Illuminate\Console\Command;

class AddApiService extends Command
{
    protected $signature = 'api-service:add {name} {base_url}';
    protected $description = 'Add a new API service';

    public function handle()
    {
        $service = ApiService::create([
            'name' => $this->argument('name'),
            'base_url' => $this->argument('base_url')
        ]);

        $this->info("API Service '{$service->name}' created successfully");
        return Command::SUCCESS;
    }
}