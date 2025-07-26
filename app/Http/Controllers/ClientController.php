<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Lister tous les clients
    public function index()
    {
        return Client::all();
    }

    // Créer un nouveau client
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse_livraison' => 'required|string',
            'entreprise' => 'nullable|string|max:255',
        ]);

        $client = Client::create($validated);
        return response()->json($client, 201);
    }

    // Afficher un client spécifique
    public function show($id)
    {
        return Client::findOrFail($id);
    }

    // Mettre à jour un client
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $client->update($request->all());
        return response()->json($client, 200);
    }

    // Supprimer un client
    public function destroy($id)
    {
        Client::destroy($id);
        return response()->json(null, 204);
    }
}
