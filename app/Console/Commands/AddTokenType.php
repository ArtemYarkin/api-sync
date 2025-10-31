<?php

namespace App\Console\Commands;

use App\Models\TokenType;
use Illuminate\Console\Command;

class AddTokenType extends Command
{
    protected $signature = 'token-type:add {name}';
    protected $description = 'Add a new token type';

    public function handle()
    {
        $tokenType = TokenType::create([
            'name' => $this->argument('name')
        ]);

        $this->info("Token type '{$tokenType->name}' created successfully");
        return Command::SUCCESS;
    }
}