<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

class FootballDatsAPIService
{
    protected static function headers()
    {
        return [
            'X-Auth-Token' => env("APIFOOT_KEY"),
            'Content-Type' => 'application/json',
        ];
    }
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.football.base_url');
        $this->token   = config('services.football.token');
    }

    public function get($endpoint, $params = [])
    {
        return Http::withHeaders([
            'X-Auth-Token' => $this->token,
        ])->get($this->baseUrl . $endpoint, $params)->json();
    }

    public function matches($date_start,$date_end)
    {
        return $this->get('/matches',['dateFrom'=>$date_start,'dateTo'=>$date_end,
           // 'permission'=>'TIER_THREE'
        ]);
    }
    //
    // Exemple d'endpoint : Liste des compétitions
    //
    public function competitions()
    {
        return $this->get('/competitions');
    }

    //
    // Matchs d’une compétition
    //
    public function matchesByCompetition($code)
    {
        return $this->get("/competitions/{$code}/matches");
    }

    //
    // Match par ID
    //
    public function matchDetail($matchId)
    {
        return $this->get("/matches/{$matchId}");
    }
}
