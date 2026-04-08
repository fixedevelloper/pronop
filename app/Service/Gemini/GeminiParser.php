<?php


namespace App\Service\Gemini;


class GeminiParser
{
    public static function parse(string $text): array
    {
        // Nettoyage markdown
        $clean = preg_replace('/```json|```/', '', $text);
        $clean = trim($clean);

        $data = json_decode($clean, true);

        if (!$data) {
            throw new \Exception("JSON Gemini invalide : " . $text);
        }

        return $data;
    }
}
