<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;   // <-- important
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SmokeQueueJob implements ShouldQueue   // <-- important
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // চাইলে নির্দিষ্ট কিউ ব্যবহার করতে পারেন (ঐচ্ছিক):
    // public $queue = 'emails';

    public function handle(): void
    {
        Log::info('✅ Queue OK at ' . now());
    }
}
