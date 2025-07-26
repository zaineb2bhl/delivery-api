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
}
