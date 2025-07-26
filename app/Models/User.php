<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Commande;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Les attributs pouvant être assignés.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_available',
    ];

    /**
     * Les attributs à cacher pour les tableaux (JSON, etc.).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast des attributs.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relation avec le rôle
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Vérifie si l'utilisateur est un admin.
     */
    public function isAdmin()
    {
        return $this->role->name === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est un livreur.
     */
    public function isLivreur()
    {
        return $this->role->name === 'livreur';
    }

    /**
     * Vérifie si l'utilisateur est un client.
     */
    public function isClient()
    {
        return $this->role->name === 'client';
    }

    /**
     * Relation avec les commandes (pour les clients)
     */
    public function commandes()
    {
        return $this->hasMany(Commande::class, 'user_id');
    }

    /**
     * Relation avec les livraisons (pour les livreurs)
     */
    public function livraisons()
    {
        return $this->hasMany(Livraison::class, 'user_id');
    }
}
