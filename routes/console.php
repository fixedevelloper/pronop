<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Schedule::command('app:get-status-momo')->everyMinute();
Schedule::command('app:update-pot')->everyMinute();
Schedule::command('app:update-fixture')->everyFifteenMinutes();
