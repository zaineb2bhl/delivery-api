<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\User;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Les admins voient toutes les commandes
        if ($user->isAdmin()) {
            return Commande::with('client')->get();
        }
        
        // Les clients voient seulement leurs commandes
        if ($user->isClient()) {
            return Commande::where('user_id', $user->id)->with('client')->get();
        }
        
        // Les livreurs voient les commandes qui leur sont assignées
        if ($user->isLivreur()) {
            return Commande::whereHas('livraisons', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('client')->get();
        }
        
        return response()->json(['error' => 'Accès non autorisé'], 403);
    }

    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est un client
        if (!auth()->user()->isClient()) {
            return response()->json(['error' => 'Seuls les clients peuvent créer des commandes'], 403);
        }

        $request->validate([
            'description' => 'required|string|max:1000',
            'adresse_livraison' => 'required|string|max:500',
            'date_commande' => 'required|date',
            'date_livraison_souhaitee' => 'nullable|date|after_or_equal:date_commande',
        ]);

        // Création de la commande
        $commande = Commande::create([
            'user_id' => auth()->id(), // Le client qui passe la commande
            'description' => $request->description,
            'adresse_livraison' => $request->adresse_livraison,
            'date_commande' => $request->date_commande,
            'date_livraison_souhaitee' => $request->date_livraison_souhaitee,
        ]);

        // Charger la relation client
        $commande->load('client');

        return response()->json([
            'message' => 'Commande créée avec succès.',
            'commande' => $commande,
        ], 201);
    }

    public function show($id)
    {
        $commande = Commande::with(['client', 'livraisons.livreur'])->findOrFail($id);
        
        $user = auth()->user();
        
        // Vérifier les permissions
        if ($user->isClient() && $commande->user_id !== $user->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }
        
        if ($user->isLivreur() && !$commande->livraisons->where('user_id', $user->id)->count()) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }
        
        return response()->json($commande);
    }

    public function update(Request $request, $id)
    {
        $commande = Commande::findOrFail($id);
        
        // Seuls les clients peuvent modifier leurs propres commandes
        if (!auth()->user()->isClient() || $commande->user_id !== auth()->id()) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:1000',
            'adresse_livraison' => 'nullable|string|max:500',
            'date_livraison_souhaitee' => 'nullable|date|after_or_equal:date_commande',
        ]);

        $commande->update($validated);

        return response()->json([
            'message' => 'Commande mise à jour avec succès.',
            'commande' => $commande
        ]);
    }

    public function destroy($id)
    {
        $commande = Commande::findOrFail($id);
        
        // Seuls les clients peuvent supprimer leurs propres commandes
        if (!auth()->user()->isClient() || $commande->user_id !== auth()->id()) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }
        
        $commande->delete();
        
        return response()->json([
            'message' => 'Commande supprimée avec succès.'
        ], 204);
    }
}
