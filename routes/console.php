<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\ConsultationPaymentService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('consultation-payments:expire', function (ConsultationPaymentService $payments) {
    $payments->expireDueProposals();
    $this->info('Expired unpaid consultation payment requests that passed their firm-set deadline.');
})->purpose('Expire unpaid consultation payment requests');

Schedule::command('consultation-payments:expire')->everyMinute()->withoutOverlapping();
