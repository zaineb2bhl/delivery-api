<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Routes publiques (sans authentification)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées (avec authentification)
Route::middleware('auth:sanctum')->group(function () {
    // Authentification
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken']);
    
    // Commandes (accessibles à tous les utilisateurs authentifiés selon leur rôle)
    Route::apiResource('commandes', CommandeController::class);
    
    // Livraisons (accessibles selon le rôle)
    Route::apiResource('livraisons', LivraisonController::class);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/stats', [NotificationController::class, 'stats']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::post('/fcm-token', [NotificationController::class, 'registerFcmToken']);
    
    // Routes spécifiques aux admins
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/livreurs-disponibles', [LivraisonController::class, 'getLivreursDisponibles']);
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Bienvenue admin']);
        });
        
        // Historique des connexions
        Route::get('/admin/login-history', function () {
            $histories = \App\Models\LoginHistory::with('user.role')
                ->orderBy('login_at', 'desc')
                ->paginate(50);
            return response()->json($histories);
        });
        
        Route::get('/admin/login-history/user/{userId}', function ($userId) {
            $histories = \App\Models\LoginHistory::where('user_id', $userId)
                ->with('user.role')
                ->orderBy('login_at', 'desc')
                ->paginate(50);
            return response()->json($histories);
        });
        
        Route::get('/admin/login-history/stats', function () {
            $stats = [
                'total_logins' => \App\Models\LoginHistory::count(),
                'successful_logins' => \App\Models\LoginHistory::where('status', 'success')->count(),
                'failed_logins' => \App\Models\LoginHistory::where('status', 'failed')->count(),
                'recent_logins' => \App\Models\LoginHistory::where('login_at', '>=', now()->subDays(7))->count(),
                'mobile_logins' => \App\Models\LoginHistory::where('device_type', 'mobile')->count(),
                'desktop_logins' => \App\Models\LoginHistory::where('device_type', 'desktop')->count(),
            ];
            return response()->json($stats);
        });
    });
    
    // Routes spécifiques aux livreurs
    Route::middleware('role:livreur')->group(function () {
        Route::get('/livreur/dashboard', function () {
            return response()->json(['message' => 'Bienvenue livreur']);
        });
        // Enregistrer la position GPS
        Route::post('/livreur/position', [\App\Http\Controllers\LivreurPositionController::class, 'store']);
    });
    
    // Route pour récupérer l'historique des positions d'un livreur (admin ou client)
    Route::get('/livreur/{userId}/positions', [\App\Http\Controllers\LivreurPositionController::class, 'history']);
    
    // Routes spécifiques aux clients
    Route::middleware('role:client')->group(function () {
        Route::get('/client/dashboard', function () {
            return response()->json(['message' => 'Bienvenue client']);
        });
    });
});
