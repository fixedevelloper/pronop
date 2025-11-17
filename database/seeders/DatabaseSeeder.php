<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\League;
use App\Models\Fixture;
use App\Models\Pot;
use App\Models\LinePotFoot;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Créer des utilisateurs factices
        $users = User::factory(5)->create();



        $allFixtures = Fixture::all();

        // 4️⃣ Créer 10 pots foot
        for ($p = 1; $p <= 10; $p++) {
            $pot = Pot::create([
                'name' => "Pot Foot $p",
                'entry_fee' => rand(5, 20),
                'total_amount' => null,
                'type' => 'foot',
                'status' => 'open',
                'start_time' => Carbon::now(),
                'end_time' => Carbon::now()->addDays(rand(1, 3)),
                'distribution_rule' => 'winner_takes_all',
                'createdBy' => $users->random()->id,
            ]);

            // 5️⃣ Créer des lignes de pot liées à des fixtures aléatoires
            $fixturesForPot = $allFixtures->random(3);
            foreach ($fixturesForPot as $fixture) {
                LinePotFoot::create([
                    'name' => "Ligne pour {$fixture->team_home_name} vs {$fixture->team_away_name}",
                    'fixture_id' => $fixture->id,
                    'pot_id' => $pot->id,
                    'result' => 'pending',
                ]);
            }
        }
    }
}
