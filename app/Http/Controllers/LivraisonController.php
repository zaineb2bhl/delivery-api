<?php

namespace App\Http\Controllers;

use App\Models\Livraison;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Http\Request;

class LivraisonController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Les admins voient toutes les livraisons
        if ($user->isAdmin()) {
            return Livraison::with(['commande.client', 'livreur'])->get();
        }
        
        // Les livreurs voient seulement leurs livraisons
        if ($user->isLivreur()) {
            return Livraison::where('user_id', $user->id)
                ->with(['commande.client'])
                ->get();
        }
        
        // Les clients voient les livraisons de leurs commandes
        if ($user->isClient()) {
            return Livraison::whereHas('commande', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['commande', 'livreur'])->get();
        }
        
        return response()->json(['error' => 'Accès non autorisé'], 403);
    }

    public function store(Request $request)
    {
        // Seuls les admins peuvent assigner des livraisons
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Seuls les administrateurs peuvent assigner des livraisons'], 403);
        }

        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'user_id' => 'required|exists:users,id', // ID du livreur
        ]);

        // Vérifier que l'utilisateur est bien un livreur
        $livreur = User::find($request->user_id);
        if (!$livreur->isLivreur()) {
            return response()->json(['error' => 'L\'utilisateur doit être un livreur'], 400);
        }

        // Vérifier que la commande n'a pas déjà une livraison active
        $existingLivraison = Livraison::where('commande_id', $request->commande_id)
            ->whereNotIn('status', ['annulee'])
            ->first();
            
        if ($existingLivraison) {
            return response()->json(['error' => 'Cette commande a déjà une livraison assignée'], 400);
        }

        // Créer la livraison
        $livraison = Livraison::create([
            'commande_id' => $request->commande_id,
            'user_id' => $request->user_id,
            'status' => 'assignee',
            'assigned_at' => now(),
        ]);

        $livraison->load(['commande.client', 'livreur']);

        return response()->json([
            'message' => 'Livraison assignée avec succès.',
            'livraison' => $livraison
        ], 201);
    }

    public function show($id)
    {
        $livraison = Livraison::with(['commande.client', 'livreur'])->findOrFail($id);
        
        $user = auth()->user();
        
        // Vérifier les permissions
        if ($user->isLivreur() && $livraison->user_id !== $user->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }
        
        if ($user->isClient() && $livraison->commande->user_id !== $user->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }
        
        return response()->json($livraison);
    }

    public function update(Request $request, $id)
    {
        $livraison = Livraison::findOrFail($id);
        $user = auth()->user();
        
        // Seuls les livreurs peuvent mettre à jour leurs livraisons
        if (!$user->isLivreur() || $livraison->user_id !== $user->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'action' => 'required|in:start,complete,cancel,update_notes',
            'notes' => 'nullable|string|max:1000',
        ]);

        switch ($request->action) {
            case 'start':
                if (!$livraison->canStart()) {
                    return response()->json(['error' => 'La livraison ne peut pas être démarrée'], 400);
                }
                $livraison->startDelivery();
                $message = 'Livraison démarrée avec succès.';
                break;

            case 'complete':
                if (!$livraison->canComplete()) {
                    return response()->json(['error' => 'La livraison ne peut pas être terminée'], 400);
                }
                $livraison->completeDelivery();
                $message = 'Livraison terminée avec succès.';
                break;

            case 'cancel':
                $livraison->cancelDelivery();
                $message = 'Livraison annulée avec succès.';
                break;

            case 'update_notes':
                $livraison->update(['notes' => $request->notes]);
                $message = 'Notes mises à jour avec succès.';
                break;
        }

        $livraison->load(['commande.client', 'livreur']);

        return response()->json([
            'message' => $message,
            'livraison' => $livraison
        ]);
    }

    public function destroy($id)
    {
        // Seuls les admins peuvent supprimer des livraisons
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $livraison = Livraison::findOrFail($id);
        $livraison->delete();

        return response()->json([
            'message' => 'Livraison supprimée avec succès.'
        ], 204);
    }

    // Méthode pour obtenir les livreurs disponibles
    public function getLivreursDisponibles()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $livreurs = User::whereHas('role', function($query) {
            $query->where('name', 'livreur');
        })->where('is_available', true)->get();

        return response()->json($livreurs);
    }
}
