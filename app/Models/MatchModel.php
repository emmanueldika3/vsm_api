<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchModel extends Model
{
    use HasFactory;

    protected $table = 'matchsitems';

    protected $fillable = [
        'season_id',
        'type',             // 'match' ou 'training'
        'opponent',         // Nom de l'équipe adverse
        'match_date',       // Date et heure de la rencontre
        'vsm_score',        // Score de VSM
        'opponent_score',   // Score de l'adversaire
        'location',         // Lieu de la rencontre
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'vsm_score' => 'integer',
        'opponent_score' => 'integer',
    ];

    /**
     * Relation avec la saison (Season)
     */
    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}