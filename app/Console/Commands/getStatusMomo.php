<?php

namespace App\Console\Commands;


use App\Models\Transaction;
use App\Service\MomoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


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
        $paiements = Transaction::query()
            ->where('status', 'pending')
            ->get();

        if ($paiements->isEmpty()) {
            $this->info('Aucun paiement en attente trouvé.');
            return Command::SUCCESS;
        }

        foreach ($paiements as $paiement) {

            $this->info("🔍 Vérification paiement #{$paiement->id} ({$paiement->reference_id})");

            try {
                // Exemple API
                // $statusResponse = $this->momo->getPaymentStatus($paiement->reference_id);
                // $status = $statusResponse['status'];

                $status = 'SUCCESSFUL'; // Forcé pour test

                $this->line("➡️ Statut API : {$status}");
                $this->line("➡️ Statut actuel : {$paiement->status}");

                // Convertir statut API → statut interne
                $mappedStatus = match ($status) {
                'SUCCESSFUL' => 'success',
                'FAILED'     => 'failed',
                default      => 'pending'
            };

            DB::beginTransaction();

            // ⚠️ Important : si déjà crédité, ne pas re-créditer
            if ($mappedStatus === 'success' && !$paiement->confirmed_at) {

                $this->line("💰 Crédit du solde utilisateur...");

                $paiement->user->update([
                    'wallet_balance' => $paiement->user->wallet_balance + $paiement->amount
                ]);
            }

            // Préparer la mise à jour
            $updateData = ['status' => $mappedStatus];

            if ($mappedStatus === 'success') {
                $updateData['confirmed_at'] = $paiement->confirmed_at ?? now();
            }

            $paiement->update($updateData);

            DB::commit();

            $paiement->refresh();

            $this->line("✔️ Nouveau statut : {$paiement->status}");

        } catch (\Exception $e) {

                DB::rollBack();

                $this->error("❌ Erreur paiement #{$paiement->id} : " . $e->getMessage());
                continue;
            }
        }

        $this->info('✅ Vérification des paiements terminée.');
        return Command::SUCCESS;
    }


}
