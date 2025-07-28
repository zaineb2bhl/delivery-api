<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Méthode d'inscription
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,livreur,client' // nom du rôle
        ]);

        // Récupérer l'ID du rôle
        $role = Role::where('name', $validated['role'])->first();
        if (!$role) {
            return response()->json(['error' => 'Rôle invalide'], 400);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id
        ]);

        // Charger la relation role pour la réponse
        $user->load('role');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur enregistré avec succès.',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // Méthode de connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'fcm_token' => 'nullable|string', // Token FCM optionnel
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Enregistrer la tentative de connexion échouée
            LoginHistory::logFailedLogin($request->email, 'Identifiants incorrects', $request);
            
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        // Supprimer les anciens tokens (optionnel)
        $user->tokens()->delete();

        // Mettre à jour le token FCM si fourni
        if ($request->fcm_token) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Enregistrer la connexion réussie
        LoginHistory::logSuccessfulLogin($user->id, $request);

        // Créer un nouveau token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Charger la relation role pour la réponse
        $user->load('role');

        return response()->json([
            'message' => 'Connexion réussie.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    // Déconnexion
    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Enregistrer la déconnexion
        $lastLogin = $user->loginHistories()
            ->whereNull('logout_at')
            ->latest()
            ->first();
            
        if ($lastLogin) {
            $lastLogin->logLogout();
        }

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Déconnecté avec succès.'
        ]);
    }

    // Obtenir le profil de l'utilisateur connecté
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        return response()->json([
            'user' => $user
        ]);
    }

    // Mettre à jour le token FCM
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $user = $request->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'message' => 'Token FCM mis à jour avec succès.',
            'user' => $user
        ]);
    }
}
