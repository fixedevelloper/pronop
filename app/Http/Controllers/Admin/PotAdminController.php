<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pot;
use App\Service\PotSettlementService;

class PotAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'entry_fee' => 'nullable|numeric',
            'type' => 'required|in:foot,pmu',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'distribution_rule' => 'nullable|string',
        ]);

        $pot = Pot::create($data);
        return response()->json($pot, 201);
    }

    public function settle(Request $request, Pot $pot, PotSettlementService $settlement)
    {
        if ($pot->status !== 'closed' && $pot->status !== 'open') {
            return response()->json(['message' => 'Pot not ready to settle'], 400);
        }

        try {
            $settlement->settle($pot);
            return response()->json(['message' => 'Pot settled successfully']);
        } catch (\Exception $e) {
            \Log::error('Error settling pot: '.$e->getMessage());
            return response()->json(['message' => 'Error settling pot', 'error' => $e->getMessage()], 500);
        }
    }
}
