<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Administrateur du système avec tous les droits'
            ],
            [
                'name' => 'client',
                'display_name' => 'Client',
                'description' => 'Client qui peut passer des commandes'
            ],
            [
                'name' => 'livreur',
                'display_name' => 'Livreur',
                'description' => 'Livreur qui effectue les livraisons'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
