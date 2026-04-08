<?php


namespace App\Http\Controllers;


use App\Http\Resources\FixtureResource;
use App\Models\Fixture;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FixtureController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $search = $request->query('search');

        $start = Carbon::today();
        $end = Carbon::today()->endOfDay();

        $query = Fixture::with('league')
            ->whereBetween('date', [$start, $end]);

        // 🔍 Recherche sur équipes
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('team_home_name', 'like', "%{$search}%")
                    ->orWhere('team_away_name', 'like', "%{$search}%");
            });
        }

        // 🔹 Trier par nom de league (relation join)
        $query->join('leagues', 'fixtures.league_id', '=', 'leagues.id')
            ->orderBy('leagues.name', 'asc')
            ->select('fixtures.*'); // important pour ne pas ramener les colonnes de la table join

        // 🔹 Pagination
        $fixtures = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => FixtureResource::collection($fixtures),
            'meta' => [
                'total' => $fixtures->total(),
                'per_page' => $fixtures->perPage(),
                'current_page' => $fixtures->currentPage(),
                'last_page' => $fixtures->lastPage(),
            ]
        ]);
    }
    // Controller
    public function fixtures(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        $start = Carbon::today();
        $end = Carbon::today()->endOfDay();
        $fixtures = Fixture::with(['league', 'aiPrediction'])
            ->whereBetween('date', [$start, $end])
            ->orderBy('timestamp', 'desc')
            ->paginate($perPage);

        return FixtureResource::collection($fixtures);
    }

    public function show(Fixture $fixture)
    {
        $fixture->load(['league', 'aiPrediction', 'linePotFoot']);

        return new FixtureResource($fixture);
    }

}
