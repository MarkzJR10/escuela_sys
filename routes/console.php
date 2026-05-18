<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('generate:adeudos')->monthlyOn(1, '00:00');
Schedule::command('generate:adeudos')->monthlyOn(11, '00:00');
