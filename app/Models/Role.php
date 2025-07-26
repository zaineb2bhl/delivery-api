<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    // Relation avec les utilisateurs
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes pour filtrer par nom de rôle
    public function scopeAdmin($query)
    {
        return $query->where('name', 'admin');
    }

    public function scopeClient($query)
    {
        return $query->where('name', 'client');
    }

    public function scopeLivreur($query)
    {
        return $query->where('name', 'livreur');
    }
}
