<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

class FootballAPIService
{
    protected static function headers()
    {
        return [
            'x-rapidapi-host' => 'api-football-v1.p.rapidapi.com',
            'x-rapidapi-key' => env("APIFOOT_KEY"),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Récupérer toutes les ligues
     */
    public static function getLeagues()
    {
        $response = Http::withHeaders(self::headers())
            ->get(env("APIFOOT_KEY_URL") . '/leagues');

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des ligues',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'data' => $response->json('response') ?? []
        ];
    }

    /**
     * Récupérer les fixtures pour une date spécifique
     *
     * @param string $from Date format YYYY-MM-DD
     */
    public static function getFixtures($from)
    {
        $response = Http::withHeaders(self::headers())
            ->get(env("APIFOOT_KEY_URL") . '/fixtures', [
                'query' => ['date' => $from]
            ]);
       // logger($response);
        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des fixtures',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'data' => $response->json('response') ?? []
        ];
    }
}
