<?php

namespace App\Console\Commands;

use App\Models\PointSale;
use App\Service\MomoService;
use Illuminate\Console\Command;

use App\Models\Paiement;

class GetStatusMomo extends Command
{
    protected $signature = 'app:get-status-momo';
    protected $description = 'Vérifie et met à jour le statut des paiements MoMo en attente.';

    private MomoService $momo;

    public function __construct(MomoService $momo)
    {
        parent::__construct();
        $this->momo = $momo;
    }

    public function handle(): int
    {
        $paiements = Paiement::query()->where('status', 'pending')->get();
        $salePoints=PointSale::query()->where('status', 'pending')->get();
        if ($paiements->isEmpty() && $salePoints->isEmpty()) {
            $this->info('Aucun paiement en attente trouvé.');
            return Command::SUCCESS;
        }

        foreach ($paiements as $paiement) {
            $this->info("Vérification du paiement #{$paiement->id} ({$paiement->reference_id}) ...");

            try {
                $statusResponse = $this->momo->getPaymentStatus($paiement->reference_id);
                $status = $statusResponse['status'];

                $this->line("Statut reçu : {$status}");
                $this->line("Statut avant update : {$paiement->status}");

                $updateData = [
                    'status' => match($status){
                    'SUCCESSFUL' => 'confirmed',
        'FAILED' => 'failed',
        default => 'pending',
    },
];

if ($status === 'SUCCESSFUL') {
    $updateData['confirmed_at'] = now();
}

$success = $paiement->update($updateData);

if (!$success) {
    $this->line("⚠️ Mise à jour échouée");
}

$paiement->refresh();
$this->line("Statut après update : {$paiement->status}");

            } catch (\Exception $e) {
                $this->error("Erreur pour le paiement #{$paiement->id} : " . $e->getMessage());
                continue;
            }
        }



        foreach ($salePoints as $salePoint) {
            $this->info("Vérification du paiement #{$salePoint->id} ({$salePoint->referenceId}) ...");

            try {
                $statusResponse = $this->momo->getPaymentStatus($salePoint->referenceId);
                $status = $statusResponse['status'];

                $this->line("Statut reçu : {$status}");
                $this->line("Statut avant update : {$salePoint->status}");

                $updateData = [
                    'status' => match($status){
                    'SUCCESSFUL' => 'confirmed',
        'FAILED' => 'failed',
        default => 'pending',
    },
];

if ($status === 'SUCCESSFUL') {
    $updateData['confirmed_at'] = now();
}

$success = $salePoint->update($updateData);

if (!$success) {
    $this->line("⚠️ Mise à jour échouée");
}

$salePoint->refresh();
$this->line("Statut après update : {$salePoint->status}");

            } catch (\Exception $e) {
                $this->error("Erreur pour le paiement #{$salePoint->id} : " . $e->getMessage());
                continue;
            }
        }

        $this->info('✅ Vérification des paiements terminée.');
        return Command::SUCCESS;
    }
}
