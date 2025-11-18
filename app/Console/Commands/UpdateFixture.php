<?php

namespace App\Console\Commands;

use App\Service\FootballDatsAPIService;
use Illuminate\Console\Command;
use App\Service\FootballAPIService;
use App\Models\Fixture;
use Carbon\Carbon;

class UpdateFixture extends Command
{

    protected $football;

    public function __construct(FootballDatsAPIService $football)
    {
        parent::__construct();
        $this->football = $football;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-fixture';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les fixtures de football depuis l’API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Début de la synchronisation des fixtures...');

        $this->getFixtures();

        $this->info('Synchronisation terminée.');
        return 0;
    }

    /**
     * Récupère et sauvegarde les fixtures pour aujourd’hui et demain.
     */
    protected function getFixtures2(): void
    {
        $from = date('Y-m-d');
        $to = date('Y-m-d', strtotime($from . ' +1 day'));
        $dates = [$from, $to];

        foreach ($dates as $date) {
            $data = FootballAPIService::getFixtures($date);

            if (!isset($data['success']) || !$data['success']) {
                $this->warn("Échec de récupération des fixtures pour $date.");
                continue;
            }

            $response = $data['data'];

            foreach ($response as $item) {
                $fixtureData = $item->fixture;
                $leagueData = $item->league;
                $teams = $item->teams;
                $score = $item->score;

                Fixture::updateOrCreate(
                    ['fixture_id' => $fixtureData->id],
                    [
                        'league_id' => $leagueData->id,
                        'team_home_name' => $teams->home->name,
                        'team_away_name' => $teams->away->name,
                        'timezone' => $fixtureData->timezone,
                        'timestamp' => $fixtureData->timestamp,
                        'date' => $fixtureData->date,
                        'date_timestamp' => Carbon::parse($fixtureData->date)->getTimestamp(),
                        'referee' => $fixtureData->referee ?? '',
                        'st_long' => $fixtureData->status->long,
                        'st_short' => $fixtureData->status->short,
                        'st_elapsed' => $fixtureData->status->elapsed ?? ' ',
                        'team_home_winner' => $teams->home->winner ?? 0,
                        'team_away_winner' => $teams->away->winner ?? 0,
                        'goal_home' => $item->goals->home,
                        'goal_away' => $item->goals->away,
                        'score_ht_home' => $score->halftime->home,
                        'score_ht_away' => $score->halftime->away,
                        'score_ft_home' => $score->fulltime->home,
                        'score_ft_away' => $score->fulltime->away,
                        'score_ext_home' => $score->extratime->home,
                        'score_ext_away' => $score->extratime->away,
                        'score_pt_home' => $score->penalty->home,
                        'score_pt_away' => $score->penalty->away,
                    ]
                );
            }

            $this->info(count($response) . " fixtures mises à jour pour $date.");
        }
    }
    protected function getFixtures(): void
    {
        $from = date('Y-m-d');
        $to = date('Y-m-d', strtotime($from . ' +3 day'));
        $dates = [$from, $to];

        foreach ($dates as $date) {

            // 🔥 Appel API corrigé
            $data = $this->football->matches($from, $to);


            $matches = $data['matches'] ?? [];

            foreach ($matches as $item) {

                $fixtureId  = $item['id'];
                $competition = $item['competition'];

                $teamHome = $item['homeTeam'];
                $teamAway = $item['awayTeam'];
                logger($teamHome['name']);
                $score     = $item['score'];
                $fullTime  = $score['fullTime'] ?? ['home' => null, 'away' => null];
                $halfTime  = $score['halfTime'] ?? ['home' => null, 'away' => null];
                $extraTime = $score['extraTime'] ?? ['home' => null, 'away' => null];
                $penalties = $score['penalties'] ?? ['home' => null, 'away' => null];

                Fixture::updateOrCreate(
                    ['fixture_id' => $fixtureId],
                    [
                        'league_id'         => $competition['id'],
                        'league_season'=>$item['season']['id'],
                        'team_home_name'    => $teamHome['name'],
                        'team_home_logo'    => $teamHome['crest'] ?? '',
                        'team_away_name'    => $teamAway['name'],
                        'team_away_logo'    => $teamAway['crest'] ?? '',

                        'date'              => $item['utcDate'],
                        'timezone' => Carbon::parse($item['utcDate'])->timezone,
                        'timestamp'         => Carbon::parse($item['utcDate'])->timestamp,
                        'day_timestamp'    => Carbon::parse($item['utcDate'])->timestamp,

                        'st_long'           => $score['winner'] ?? '',
                        'st_short'          => $item['status'] ?? '',
                        'st_elapsed'        => $item['status'] ?? '',

                        'referee'           => $item['referees'][0]['name'] ?? '',

                        'team_home_winner'  => ($score['winner'] ?? '') === 'HOME_TEAM' ? 1 : 0,
                        'team_away_winner'  => ($score['winner'] ?? '') === 'AWAY_TEAM' ? 1 : 0,

                        'goal_home'         => $fullTime['home'],
                        'goal_away'         => $fullTime['away'],

                        'score_ht_home'     => $halfTime['home'],
                        'score_ht_away'     => $halfTime['away'],

                        'score_ft_home'     => $fullTime['home'],
                        'score_ft_away'     => $fullTime['away'],

                        'score_ext_home'    => $extraTime['home'],
                        'score_ext_away'    => $extraTime['away'],

                        'score_pt_home'     => $penalties['home'],
                        'score_pt_away'     => $penalties['away'],
                    ]
                );
            }

            $this->info(count($matches) . " fixtures mises à jour pour $date.");
        }
    }

}
