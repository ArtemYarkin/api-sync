<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class AddCompany extends Command
{
    protected $signature = 'company:add {name}';
    protected $description = 'Add a new company';

    public function handle()
    {
        $company = Company::create([
            'name' => $this->argument('name')
        ]);

        $this->info("Company '{$company->name}' created successfully");
        return Command::SUCCESS;
    }
}