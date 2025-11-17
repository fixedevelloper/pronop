<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Artisan::command('momo:status',function (){
    info('Commande momo:status exécutée automatiquement.');
})->everyMinute();
