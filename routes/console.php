<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


//Schedule::command('momo:status')->everyMinute();
Schedule::command('app:update-pot')->everyMinute();
