<?php

namespace App\Listeners;

use App\Events\CommandeCreated;
use App\Models\Livraison;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AutoAssignLivraison
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CommandeCreated  $event
     * @return void
     */
    public function handle(CommandeCreated $event)
    {
        $commande = $event->commande;
        
        // Créer automatiquement la livraison avec statut 'en_attente'
        $livraison = Livraison::create([
            'commande_id' => $commande->id,
            'user_id' => null, // Pas de livreur assigné au début
            'status' => 'en_attente',
            'created_at' => now()
        ]);
        
        // Chercher un livreur disponible
        $livreurDisponible = User::whereHas('role', function($query) {
            $query->where('name', 'livreur');
        })->where('is_available', true)->first();
        
        if ($livreurDisponible) {
            // Assigner automatiquement le livreur
            $livraison->update([
                'user_id' => $livreurDisponible->id,
                'status' => 'assignee',
                'assigned_at' => now()
            ]);
            
            // Marquer le livreur comme non disponible temporairement
            $livreurDisponible->update(['is_available' => false]);
        }
        
        // Log pour debug
        \Log::info('Livraison auto-assignée pour la commande #' . $commande->id, [
            'commande_id' => $commande->id,
            'livraison_id' => $livraison->id,
            'livreur_id' => $livreurDisponible ? $livreurDisponible->id : null,
            'status' => $livraison->status
        ]);
    }
}
