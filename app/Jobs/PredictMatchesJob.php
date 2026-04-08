<?php

namespace App\Jobs;

use App\Models\User;
use App\Service\Gemini\PredictionEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PredictMatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $matches;
    protected User $user;

    public function __construct(array $matches, User $user)
    {
        $this->matches = $matches;
        $this->user = $user;
    }

    public function handle(PredictionEngine $engine)
    {
        // 🔥 traitement IA
        $engine->predict($this->matches, $this->user, true);
    }
}
