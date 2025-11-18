<?php


namespace App\Http\Controllers;


use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MobileController extends Controller
{

    public function deposit(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $country=$request->country;
        $referenceId = Str::uuid()->toString();
        $status='accepted';
        return response()->json([
            'referenceId' => $referenceId,
            'status' => $status
        ]);
    }
    public function withdraw(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $country=$request->country;
        $referenceId = Str::uuid()->toString();
        $status='accepted';
        return response()->json([
            'referenceId' => $referenceId,
            'status' => $status
        ]);
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
        ]);
    }
}
