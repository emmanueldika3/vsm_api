<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',         // Ex: "Saison 2026-2027"
        'adhesion_fee', // Montant de l'adhésion pour cet exercice
        'annual_fee',   // Montant de la cotisation annuelle pour cet exercice
        'is_active',    // Booléen pour définir la saison en cours (true/false)
        'start_date',   // Date de début de l'exercice
        'end_date',     // Date de fin de l'exercice
    ];

    protected $casts = [
        'adhesion_fee' => 'float',
        'annual_fee'   => 'float',
        'is_active'    => 'boolean',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    /**
     * Relation optionnelle : Si vous souhaitez lier des contributions directement à une saison
     * (Vous pouvez aussi laisser la liaison par date comme dans le contrôleur).
     */
    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }
}