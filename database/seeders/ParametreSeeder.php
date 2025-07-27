<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parametre;

class ParametreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Parametre::updateOrCreate(
            ['cle' => 'taxe'],
            [
                'valeur' => '20',
                'description' => 'Taux de taxe en pourcentage'
            ]
        );

        Parametre::updateOrCreate(
            ['cle' => 'frais_livraison'],
            [
                'valeur' => '5.50',
                'description' => 'Frais de livraison standard en euros'
            ]
        );

        Parametre::updateOrCreate(
            ['cle' => 'delai_livraison'],
            [
                'valeur' => '24',
                'description' => 'Délai de livraison standard en heures'
            ]
        );
    }
}
