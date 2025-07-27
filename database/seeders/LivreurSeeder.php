<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class LivreurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer le rôle livreur
        $roleLivreur = Role::where('name', 'livreur')->first();

        if (!$roleLivreur) {
            $this->command->error('Rôle livreur non trouvé. Exécutez d\'abord RoleSeeder.');
            return;
        }

        // Créer des livreurs de test
        $livreurs = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@delivery.com',
                'password' => Hash::make('password'),
                'role_id' => $roleLivreur->id,
                'is_available' => true,
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@delivery.com',
                'password' => Hash::make('password'),
                'role_id' => $roleLivreur->id,
                'is_available' => true,
            ],
            [
                'name' => 'Pierre Durand',
                'email' => 'pierre.durand@delivery.com',
                'password' => Hash::make('password'),
                'role_id' => $roleLivreur->id,
                'is_available' => true,
            ],
        ];

        foreach ($livreurs as $livreur) {
            User::updateOrCreate(
                ['email' => $livreur['email']],
                $livreur
            );
        }

        $this->command->info('Livreurs de test créés avec succès !');
    }
}
