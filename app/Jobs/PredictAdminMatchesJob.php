<?php

namespace App\Jobs;

use App\Models\User;
use App\Service\Gemini\PredictionAdminEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PredictAdminMatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $matches;

    public function __construct(array $matches)
    {
        $this->matches = $matches;

    }

    public function handle(PredictionAdminEngine $engine)
    {
        // 🔥 traitement IA
        $engine->predict($this->matches,  true);
    }
}
