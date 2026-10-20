<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billz:sync-sales')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('billz:sync-shops')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('billz:sync-products')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('billz:sync-clients')->dailyAt('03:00')->withoutOverlapping();
// Sotuvchi baholash (kechikish > 0) va T1–T4 soʻrovnomalar — har daqiqada
Schedule::command('surveys:dispatch')->everyMinute()->withoutOverlapping();
Schedule::command('notify:daily')->dailyAt('09:00');
Schedule::command('notify:weekly')->weeklyOn(1, '09:00'); // dushanba 09:00
Schedule::command('notify:alerts')->everyFifteenMinutes()->withoutOverlapping();
// Oylik sotuvchilar reytingi — har oyning 1-kuni 09:00
Schedule::command('notify:monthly-sellers')->monthlyOn(1, '09:00')->withoutOverlapping();
