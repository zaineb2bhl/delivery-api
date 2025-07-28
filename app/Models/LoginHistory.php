<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'location',
        'status',
        'failure_reason',
        'login_at',
        'logout_at'
    ];

    /**
     * Cast des attributs.
     *
     * @var array
     */
    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les connexions réussies
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope pour les connexions échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope pour les connexions récentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }

    /**
     * Enregistrer une connexion réussie
     */
    public static function logSuccessfulLogin($userId, $request = null)
    {
        $data = [
            'user_id' => $userId,
            'status' => 'success',
            'login_at' => now(),
        ];

        if ($request) {
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['device_type'] = self::getDeviceType($request->userAgent());
        }

        return self::create($data);
    }

    /**
     * Enregistrer une connexion échouée
     */
    public static function logFailedLogin($email, $reason, $request = null)
    {
        $user = User::where('email', $email)->first();
        
        $data = [
            'user_id' => $user ? $user->id : null,
            'status' => 'failed',
            'failure_reason' => $reason,
            'login_at' => now(),
        ];

        if ($request) {
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['device_type'] = self::getDeviceType($request->userAgent());
        }

        return self::create($data);
    }

    /**
     * Enregistrer une déconnexion
     */
    public function logLogout()
    {
        $this->update(['logout_at' => now()]);
    }

    /**
     * Détecter le type d'appareil
     */
    private static function getDeviceType($userAgent)
    {
        if (preg_match('/(android|iphone|ipad|mobile)/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/(tablet|ipad)/i', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
}
