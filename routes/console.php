<?php

use App\Console\Commands\RespaldarBaseDatosCommand;
use App\Console\Commands\SincronizarFuentesCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SincronizarFuentesCommand::class)
    ->hourly()
    ->withoutOverlapping(60);

Schedule::command(RespaldarBaseDatosCommand::class)
    ->dailyAt('02:00')
    ->withoutOverlapping(120);
