<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

class FootballAPIService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.football.base_url');
        $this->token   = config('services.football.token');
    }

    public function get($endpoint, $params = [])
    {
        $response = Http::withHeaders([
            'x-apisports-key' => $this->token,
        ])->get($this->baseUrl . $endpoint, $params);

        // On retourne la Response
        return $response;
    }

    public function getFixtures($date)
    {
        $response = $this->get('/fixtures', ['date' => $date]);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'Erreur API',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'data' => $response->json('response') ?? []
        ];
    }
}


