<?php

namespace App\Http\Controllers;

use App\Models\LivreurPosition;
use Illuminate\Http\Request;

class LivreurPositionController extends Controller
{
    // Enregistrer la position GPS du livreur
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'recorded_at' => 'required|date',
            'accuracy' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $position = LivreurPosition::create([
            'user_id' => auth()->id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'recorded_at' => $request->recorded_at,
            'accuracy' => $request->accuracy,
            'source' => $request->source,
        ]);

        return response()->json(['position' => $position], 201);
    }

    // Récupérer l'historique des positions d'un livreur
    public function history($userId)
    {
        $positions = LivreurPosition::where('user_id', $userId)
            ->orderBy('recorded_at')
            ->get();
        return response()->json(['positions' => $positions]);
    }
}
