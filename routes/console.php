<?php

use App\Enums\AppointmentStatus;
use App\Models\Scheduling\Appointment;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    $cutoff = Carbon::now()->subHours(24);

    Appointment::where('status', AppointmentStatus::PENDING->value)
        ->where('created_at', '<', $cutoff)
        ->update(['status' => AppointmentStatus::CANCELLED->value]);
})->everyMinute();
