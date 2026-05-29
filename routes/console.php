<?php

use App\Models\Order;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Order::where('status', 'dikirim')
        ->where('updated_at', '<=', now()->subMinutes(2))
        ->update(['status' => 'selesai']);
})->everyMinute();
