<?php


namespace App\Service\Gemini;


use Gemini\Laravel\Facades\Gemini;

class GeminiService
{

    public function predictMatches(array $matches, bool $withAnalysis = false): array
    {
        $prompt = GeminiPromptBuilder::buildBatchPrompt($matches, $withAnalysis);

        $response = Gemini::generativeModel(config('gemini.model'))
            ->generateContent($prompt);

        $text = $response->text();

        return GeminiParser::parse($text);
    }
}
