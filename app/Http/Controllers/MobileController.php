<?php


namespace App\Http\Controllers;


use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileController extends Controller
{

    public function deposit(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'paymentMethod'=> 'required|string|max:255',
            'amount'  => 'required|numeric|min:100',
            'country'=> 'required|string|max:255',

        ]);
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $country=$request->country;
        $referenceId = Str::uuid()->toString();
        $transaction=Transaction::create([
            'reference'=>$referenceId,
            'amount'=>$request->amount,
            'type'=>'deposit',
            'user_id'=>$user->id
        ]);

        $status='accepted';
        return response()->json([
            'referenceId' => $referenceId,
            'status' => $status
        ]);
    }
    public function withdraw(Request $request)
    {
        // Validation sécurisée
        $validated = $request->validate([
            'amount'  => 'required|numeric|min:1',
            'country' => 'required|string'
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        if ($user->wallet_balance < $validated['amount']) {
            return response()->json([
                'message' => 'Solde insuffisant'
            ], 400);
        }

        $referenceId = Str::uuid()->toString();
        $status = 'accepted';

        // 🔐 Transaction DB pour éviter les incohérences
        DB::beginTransaction();
        try {

            // Débiter le portefeuille
            $user->update([
                'wallet_balance' => $user->wallet_balance - $validated['amount']
            ]);

            // Créer la transaction
            $transaction = Transaction::create([
                'reference' => $referenceId,
                'amount' => $validated['amount'],
                'type' => 'withdrawal',
                'user_id' => $user->id,
                'country' => $validated['country']
            ]);
            logger($user);
            DB::commit();

            return response()->json([
                'referenceId' => $referenceId,
                'status'      => $status,
                'balance'     => $user->wallet_balance
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus($referenceId)
    {
       // $response=$this->momo->getPaymentStatus($referenceId);
        // Rechercher le paiement correspondant
        $paiement = Transaction::where('reference', $referenceId)->first();

        // Si non trouvé
        if (!$paiement) {
            return response()->json([
                'referenceId' => $referenceId,
                'status' => 'not_found',
                'message' => 'Aucun paiement trouvé pour cette référence.'
            ], 404);
        }

        // Si trouvé
        return response()->json([
            'referenceId' => $paiement->reference_id,
            'status' => $paiement->status,
            'amount' => $paiement->amount,
            'confirmed_at' => $paiement->confirmed_at,
            'balance'=>$paiement->user->wallet_balance
        ]);
    }
}
