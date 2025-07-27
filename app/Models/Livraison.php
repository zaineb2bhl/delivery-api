<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'user_id',
        'status',
        'assigned_at',
        'started_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relation avec la commande
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    // Relation avec le livreur
    public function livreur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes pour filtrer par statut
    public function scopeEnAttente($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeAssignee($query)
    {
        return $query->where('status', 'assignee');
    }

    public function scopeEnCours($query)
    {
        return $query->where('status', 'en_cours');
    }

    public function scopeLivree($query)
    {
        return $query->where('status', 'livree');
    }

    public function scopeAnnulee($query)
    {
        return $query->where('status', 'annulee');
    }

    /**
     * Démarrer la livraison (changer statut à 'en_cours')
     */
    public function startDelivery()
    {
        $this->update([
            'status' => 'en_cours',
            'started_at' => now()
        ]);
    }

    /**
     * Terminer la livraison (changer statut à 'livree')
     */
    public function completeDelivery()
    {
        $this->update([
            'status' => 'livree',
            'delivered_at' => now()
        ]);

        // Libérer le livreur
        if ($this->livreur) {
            $this->livreur->update(['is_available' => true]);
        }
    }

    /**
     * Annuler la livraison
     */
    public function cancelDelivery()
    {
        $this->update(['status' => 'annulee']);

        // Libérer le livreur si assigné
        if ($this->livreur && $this->status !== 'en_attente') {
            $this->livreur->update(['is_available' => true]);
        }
    }

    /**
     * Vérifier si la livraison peut être démarrée
     */
    public function canStart()
    {
        return $this->status === 'assignee' && $this->user_id !== null;
    }

    /**
     * Vérifier si la livraison peut être terminée
     */
    public function canComplete()
    {
        return $this->status === 'en_cours';
    }
}
