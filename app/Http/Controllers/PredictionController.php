<?php
namespace App\Http\Controllers;

use App\Models\Pot;
use App\Models\LinePotFoot;
use App\Models\Prediction;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function store(Request $request, Pot $pot, LinePotFoot $line)
    {
        $this->validate($request, [
            'prediction' => 'required|in:1v,2v,x'
        ]);

        $user = $request->user();

        // Vérifier que le line appartient bien au pot
        if ($line->pot_id ?? null) {
            // si tu as une relation pot->lines, assure toi que line belongs to pot
        }

        // Vérifier deadline : pot open et pas commencé
        if ($pot->status !== 'open') {
            return response()->json(['message' => 'Pot closed or not open'], 400);
        }

        // Autoriser modification jusqu'à deadline (on suppose start_time)
        if ($pot->start_time && now()->greaterThanOrEqualTo($pot->start_time)) {
            return response()->json(['message' => 'Deadline reached'], 400);
        }

        $prediction = Prediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'line_pot_foot_id' => $line->id
            ],
            [
                'prediction' => $request->input('prediction')
            ]
        );

        return response()->json(['message' => 'Pronostic enregistré', 'prediction' => $prediction]);
    }
}
