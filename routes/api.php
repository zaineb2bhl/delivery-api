<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\LivraisonController;

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
    
    // Commandes (accessibles à tous les utilisateurs authentifiés selon leur rôle)
    Route::apiResource('commandes', CommandeController::class);
    
    // Livraisons (accessibles selon le rôle)
    Route::apiResource('livraisons', LivraisonController::class);
    
    // Routes spécifiques aux admins
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/livreurs-disponibles', [LivraisonController::class, 'getLivreursDisponibles']);
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Bienvenue admin']);
        });
    });
    
    // Routes spécifiques aux livreurs
    Route::middleware('role:livreur')->group(function () {
        Route::get('/livreur/dashboard', function () {
            return response()->json(['message' => 'Bienvenue livreur']);
        });
    });
    
    // Routes spécifiques aux clients
    Route::middleware('role:client')->group(function () {
        Route::get('/client/dashboard', function () {
            return response()->json(['message' => 'Bienvenue client']);
        });
    });
});
