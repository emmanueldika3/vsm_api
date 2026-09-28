<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'home_team',
        'away_team',
        'venue',
        'event_date_time',
        'home_logo_url',
        'away_logo_url',
    ];

    /**
     * Casts d'attributs pour un typage automatique.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_date_time' => 'datetime',
    ];
}