<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Jobs\SmokeQueueJob;

Route::get('/_queue_smoke', function () {
    SmokeQueueJob::dispatch();
    return 'queued';
});
