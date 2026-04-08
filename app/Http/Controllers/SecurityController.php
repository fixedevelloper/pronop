<?php


namespace App\Http\Controllers;
use App\Http\Helpers\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    /**
     * 🔹 Authentification d'un utilisateur (login)
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required|string|min:4',
        ]);

        // Vérifie les identifiants
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants invalides',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        // Crée un token API
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'balance' => $user->wallet_balance,
                'phone' => $user->phone,
                'token' => $token,
            ],
        ]);
    }
    public function register(Request $request)
    {
        logger($request->all());

        // 🔹 Validation
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        // 🔹 Vérifier si utilisateur existe
        $existingUser = User::where('phone', $request->phone)
            ->orWhere('email', $request->email)
            ->first();

        if ($existingUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Un utilisateur avec ce téléphone ou email existe déjà.',
            ], 409);
        }

        // 🔹 Création de l'utilisateur
        $user = User::create([
            'phone' => $request->phone,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'owner',
        ]);
        // 🔹 Génération du token JWT
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->refresh;
        logger($user);
        // 🔹 Retour JSON avec token
        return response()->json([
            'status' => 'success',
            'message' => 'Inscription réussie',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'token' => $token, // 🔹 Token utilisable côté NextAuth
            ],
        ]);
    }


    /**
     * 🔹 Déconnexion
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * 🔹 Retourne les infos du user connecté
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {

        $request->validate([
            'fullName' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
        ]);
        $customer = Auth::user();

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }
        $customer->update([
            'name' => $request->fullName,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);


        return Helpers::success([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'balance' => $customer->sold,
            'date_birth' => date('Y-m-d')
        ]);
    }

    public function changePassword(Request $request)
    {

        $request->validate([
            'new_password' => 'required|string',
            'password' => 'required|string',
        ]);
        $customer = Auth::user();

        if (!$customer) {
            return Helpers::error('$customer est requis', 400);
        }
        if (!Auth::attempt(['phone' => $customer->phone, 'password' => $request->password])) {
            return Helpers::error('Invalid credentials');

        }
        $customer->update([
            'password' => Hash::make($request->new_password)

        ]);

        return Helpers::success([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'balance' => $customer->sold,
            'date_birth' => date('Y-m-d')
        ]);
    }
}
