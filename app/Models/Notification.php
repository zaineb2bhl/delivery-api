<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'fcm_token',
        'title',
        'message',
        'type',
        'data',
        'is_read',
        'read_at',
        'is_sent',
        'sent_at'
    ];

    /**
     * Cast des attributs.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_sent' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur (client)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope pour les notifications non envoyées
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Marquer comme lue
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Marquer comme envoyée
     */
    public function markAsSent()
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now()
        ]);
    }

    /**
     * Créer une notification pour un utilisateur
     */
    public static function createForUser($userId, $title, $message, $type, $data = null, $fcmToken = null)
    {
        return self::create([
            'user_id' => $userId,
            'fcm_token' => $fcmToken,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data
        ]);
    }
}
