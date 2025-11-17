<?php


namespace App\Http\Controllers;
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
        logger($request->all());
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
                'role' => $user->user_type,
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
            'user_type' => 'customer',
        ]);
        // 🔹 Génération du token JWT
        $token = $user->createToken('auth_token')->plainTextToken;

        // 🔹 Retour JSON avec token
        return response()->json([
            'status' => 'success',
            'message' => 'Inscription réussie',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->user_role,
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
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    }
}
