<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    private $serverKey;
    private $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        // Tu devras configurer cette clé dans ton .env
        $this->serverKey = config('services.fcm.server_key');
    }

    /**
     * Envoyer une notification FCM
     */
    public function sendNotification($fcmToken, $title, $message, $data = [])
    {
        if (!$this->serverKey) {
            Log::warning('FCM Server Key non configurée');
            return false;
        }

        $payload = [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default',
                'badge' => 1,
            ],
            'data' => $data,
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                Log::info('Notification FCM envoyée avec succès', [
                    'token' => $fcmToken,
                    'title' => $title,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Erreur lors de l\'envoi de la notification FCM', [
                    'token' => $fcmToken,
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi de la notification FCM', [
                'error' => $e->getMessage(),
                'token' => $fcmToken
            ]);
            return false;
        }
    }

    /**
     * Envoyer une notification à un utilisateur
     */
    public function sendToUser($userId, $title, $message, $type, $data = [])
    {
        // Créer la notification en base
        $notification = Notification::createForUser($userId, $title, $message, $type, $data);

        // Récupérer le token FCM de l'utilisateur
        $user = \App\Models\User::find($userId);
        if ($user && $user->fcm_token) {
            $sent = $this->sendNotification($user->fcm_token, $title, $message, $data);
            if ($sent) {
                $notification->markAsSent();
            }
        }

        return $notification;
    }

    /**
     * Envoyer une notification de statut de livraison
     */
    public function sendLivraisonStatusNotification($livraison, $status)
    {
        $commande = $livraison->commande;
        $client = $commande->client;
        $livreur = $livraison->livreur;

        $messages = [
            'assignee' => [
                'title' => 'Livraison assignée',
                'message' => "Votre commande #{$commande->id} a été assignée à {$livreur->name}"
            ],
            'en_cours' => [
                'title' => 'Livraison en cours',
                'message' => "{$livreur->name} est en route pour livrer votre commande #{$commande->id}"
            ],
            'livree' => [
                'title' => 'Livraison terminée',
                'message' => "Votre commande #{$commande->id} a été livrée avec succès !"
            ],
            'annulee' => [
                'title' => 'Livraison annulée',
                'message' => "Votre commande #{$commande->id} a été annulée"
            ]
        ];

        if (isset($messages[$status])) {
            $data = [
                'commande_id' => $commande->id,
                'livraison_id' => $livraison->id,
                'status' => $status,
                'type' => 'livraison_status'
            ];

            return $this->sendToUser(
                $client->id,
                $messages[$status]['title'],
                $messages[$status]['message'],
                'livraison_status',
                $data
            );
        }

        return null;
    }
} 