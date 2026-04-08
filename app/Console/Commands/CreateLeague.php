<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Service\FootballAPIService;
use Illuminate\Console\Command;

class CreateLeague extends Command
{

    protected $football;

    public function __construct(FootballAPIService $football)
    {
        parent::__construct();
        $this->football = $football;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-league';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->createLeagues();

    }

    public function createLeagues()
    {
        $data = $this->football->getLeagues();
        $response = $data['data'];

        foreach ($response as $item) {

            $leagueData = $item['league'];
            $countryData = $item['country'];

            $league = League::query()->firstWhere('league_id', $leagueData['id']);

            if (!$league) {
                $league = new League();
            }

            $league->league_id = $leagueData['id'];
            $league->name = $leagueData['name'];
            $league->type = $leagueData['type'];
            $league->logo = $leagueData['logo'];
            $league->country_code = $countryData['code'] ?? null;

            $league->save();
        }
    }


}
