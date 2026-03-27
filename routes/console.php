<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mealtrack:process-no-shows')
    ->everyThirtyMinutes()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));