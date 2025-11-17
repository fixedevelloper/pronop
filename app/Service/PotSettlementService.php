<?php

namespace App\Service;

use App\Models\Pot;
use App\Models\SubscriptionPot;
use App\Models\Prediction;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PotSettlementService
{
    protected $commissionRate = 0.10; // 10% par défaut

    /**
     * Clôturer et distribuer les gains d’un pot
     */
    public function settle(Pot $pot)
    {
        return DB::transaction(function () use ($pot) {

            // 1️⃣ Récupérer les participants validés
            $subs = SubscriptionPot::where('pot_id', $pot->id)
                ->where('status', 'success')
                ->get();

            if ($subs->isEmpty()) {
                throw new \Exception('Aucun participant dans ce pot');
            }

            // 2️⃣ Récupérer les lignes du pot et leurs résultats
            $lines = $pot->linesFoot()->get(); // si foot
            if ($lines->isEmpty()) {
                throw new \Exception('Pas de lignes disponibles pour ce pot');
            }

            // Map id => result
            $lineResults = $lines->pluck('result', 'id')->toArray();

            // 3️⃣ Calculer les points de chaque participant
            $scores = []; // user_id => points
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

                $scores[$userId] = $points;
            }

            // 4️⃣ Déterminer les gagnants (max points)
            $maxPoints = max($scores);
            $winners = array_keys(array_filter($scores, function ($v) use ($maxPoints) {
                return $v === $maxPoints;
            }));

            if (empty($winners)) {
                throw new \Exception('Aucun gagnant');
            }

            // 5️⃣ Calcul du pot et commission
            $potTotal = $pot->total_amount ?? $subs->sum('amount');
            $commission = round($potTotal * $this->commissionRate, 2);
            $distributable = round($potTotal - $commission, 2);

            if ($distributable <= 0) {
                throw new \Exception('Montant insuffisant après commission');
            }

            // 6️⃣ Répartition équitable entre gagnants
            $perWinner = round($distributable / count($winners), 2);

            // 7️⃣ Créditer les wallets et enregistrer transactions
            foreach ($winners as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                $user->wallet_balance = ($user->wallet_balance ?? 0) + $perWinner;
                $user->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'pot_id' => $pot->id,
                    'type' => 'win',
                    'amount' => $perWinner,
                    'status' => 'success',
                    'reference' => uniqid('WIN_')
                ]);
            }

            // 8️⃣ Transaction commission
            if ($commission > 0) {
                Transaction::create([
                    'user_id' => null,
                    'pot_id' => $pot->id,
                    'type' => 'commission',
                    'amount' => $commission,
                    'status' => 'success',
                    'reference' => uniqid('COM_')
                ]);
            }

            // 9️⃣ Marquer le pot comme réglé
            $pot->status = 'settled';
            $pot->save();

            // 10️⃣ Fermer toutes les subscriptions liées
            SubscriptionPot::where('pot_id', $pot->id)->update(['status' => 'closed']);

            return [
                'winners' => $winners,
                'per_winner' => $perWinner,
                'commission' => $commission,
            ];
        });
    }
}

