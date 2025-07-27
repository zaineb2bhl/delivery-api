<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'cle',
        'valeur',
        'description'
    ];

    /**
     * Récupérer la valeur d'un paramètre par sa clé
     *
     * @param string $cle
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($cle, $default = null)
    {
        $parametre = self::where('cle', $cle)->first();
        return $parametre ? $parametre->valeur : $default;
    }

    /**
     * Définir la valeur d'un paramètre
     *
     * @param string $cle
     * @param mixed $valeur
     * @param string|null $description
     * @return Parametre
     */
    public static function setValue($cle, $valeur, $description = null)
    {
        return self::updateOrCreate(
            ['cle' => $cle],
            [
                'valeur' => $valeur,
                'description' => $description
            ]
        );
    }

    /**
     * Vérifier si un paramètre existe
     *
     * @param string $cle
     * @return bool
     */
    public static function exists($cle)
    {
        return self::where('cle', $cle)->exists();
    }
}
