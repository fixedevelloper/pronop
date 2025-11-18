<?php


namespace App\Http\Controllers;


use App\Http\Resources\FixtureResource;
use App\Models\Fixture;
use Illuminate\Http\Request;

class FixtureController extends Controller
{

    public function index(Request $request)
    {
        // 🔹 Pagination dynamique (par défaut 5)
        $perPage = $request->query('per_page', 10);

        // 🔹 Récupération des fixtures du jour
        $pots =Fixture::whereRaw("DATE(`date`) = ?", [date('Y-m-d')])
            ->orderBy('timestamp', 'desc')
            ->paginate($perPage);


        // 🔹 Retourner une ResourceCollection avec pagination intacte
        return FixtureResource::collection($pots);
    }

}
