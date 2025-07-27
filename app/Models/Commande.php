<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'adresse_livraison',
        'date_commande',
        'date_livraison_souhaitee',
    ];

    protected $dispatchesEvents = [
        'created' => \App\Events\CommandeCreated::class,
    ];

    // Relation avec le client (User)
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation avec les livraisons
    public function livraisons()
    {
        return $this->hasMany(Livraison::class);
    }

    // Relation avec le livreur actuel (via la livraison active)
    public function livreur()
    {
        return $this->hasOneThrough(
            User::class,
            Livraison::class,
            'commande_id', // Clé étrangère sur livraisons
            'id', // Clé primaire sur users
            'id', // Clé primaire sur commandes
            'user_id' // Clé étrangère sur livraisons
        )->where('livraisons.status', '!=', 'annulee');
    }
}
