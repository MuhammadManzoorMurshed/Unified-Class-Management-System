<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;  // ✅ Log Facade import

class TestJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        Log::channel('stack')->info('✅ Queue working perfectly! This is a test job executed successfully.');
    }
}
