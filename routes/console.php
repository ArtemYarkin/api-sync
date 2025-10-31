<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('sync:all --all-accounts --dateFrom=' . now()->subDay()->format('Y-m-d') . ' --dateTo=' . now()->format('Y-m-d'))
         ->twiceDaily(10, 18)
         ->timezone('Europe/Moscow');