<?php

namespace App\Service;

use App\Models\Pot;
use App\Models\SubscriptionPot;
use App\Models\Prediction;

class PotRankingService
{
    /**
     * Retourne le classement des participants pour un pot en temps réel
     *
     * @param Pot $pot
     * @return array
     */
    public function getRanking(Pot $pot)
    {
        // 1️⃣ Récupérer les subscriptions valides
        $subs = $pot->subscriptions()->where('status', 'success')->get();

        if ($subs->isEmpty()) {
            return [];
        }

        // 2️⃣ Récupérer les lignes du pot
        $lines = $pot->linesFoot()->get();
        $lineResults = $lines->pluck('result', 'id')->toArray();

        // 3️⃣ Calculer le score pour chaque participant
        $ranking = [];
        foreach ($subs as $sub) {
            $userId = $sub->user_id;
            $preds = Prediction::whereIn('line_pot_foot_id', array_keys($lineResults))
                ->where('user_id', $userId)
                ->get();

            $points = 0;
            foreach ($preds as $p) {
                $expected = $lineResults[$p->line_pot_foot_id] ?? 'pending';
                if ($expected !== 'pending' && $p->prediction === $expected) {
                    $points++;
                }
            }

            $ranking[] = [
                'user_id' => $userId,
                'username' => $sub->user->name ?? 'Utilisateur',
                'points' => $points
            ];
        }

        // 4️⃣ Trier par points décroissants
        usort($ranking, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        // 5️⃣ Ajouter le rang
        $currentRank = 1;
        $previousPoints = null;
        foreach ($ranking as $key => &$entry) {
            if ($previousPoints !== null && $entry['points'] === $previousPoints) {
                $entry['rank'] = $ranking[$key - 1]['rank']; // égalité
            } else {
                $entry['rank'] = $currentRank;
            }
            $previousPoints = $entry['points'];
            $currentRank++;
        }

        return $ranking;
    }
}
