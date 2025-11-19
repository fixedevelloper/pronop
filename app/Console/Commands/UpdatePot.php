<?php

namespace App\Console\Commands;

use App\Models\Pot;
use App\Service\PotSettlementService;
use Illuminate\Console\Command;

class UpdatePot extends Command
{
    protected $signature = 'app:update-pot';
    protected $description = 'Met à jour les résultats des pots et ferme ceux terminés';

    protected $potSettlementService;

    /**
     * UpdatePot constructor.
     * @param $potSettlementService
     */
    public function __construct(PotSettlementService $potSettlementService)
    {
        $this->potSettlementService = $potSettlementService;
    }

    public function handle()
    {
        $pots = Pot::where('status', 'open')->get();

        foreach ($pots as $pot) {
            $this->info('Mise à jour des pots terminée.');
            // Charger les lignes + fixtures en une requête (optimisation)
            $lines = $pot->footLines()->with('fixture')->get();

            $finishedCount = 0;

            foreach ($lines as $line) {

                $fixture = $line->fixture;

                // Sécurité
                if (!$fixture) {
                    continue;
                }

                // Le match n'est pas terminé
                if ($fixture->st_short !== 'FT') {
                    continue;
                }

                $finishedCount++;

                // Détermination du résultat
                if ($fixture->score_ft_home > $fixture->score_ft_away) {
                    $result = '1v';
                } elseif ($fixture->score_ft_home < $fixture->score_ft_away) {
                    $result = '2v';
                } else {
                    $result = 'x';
                }

                // Mise à jour de la ligne si nouveau résultat
                if ($line->result !== $result) {
                    $line->update(['result' => $result]);
                }
            }

            // Si tous les matchs sont terminés → fermer le pot
            if ($finishedCount === $lines->count() && $lines->count() > 0) {
                $pot->update(['status' => 'closed']);
                $this->potSettlementService->settle($pot);
            }
        }

        $this->info('Mise à jour des pots terminée.');
    }
}

