<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::job(new \App\Jobs\ProcessDueFlowStepsJob)
    ->everyMinute()
    ->timezone('America/Sao_Paulo')
    ->name('process-due-flow-steps');

\Illuminate\Support\Facades\Schedule::job(new \App\Jobs\MarkExpiredTrialsJob)
    ->dailyAt('00:05')
    ->timezone('America/Sao_Paulo')
    ->name('mark-expired-trials');
