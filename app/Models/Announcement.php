<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'content',
        'category',
        'target_audience',
        'is_urgent',
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
    ];

    /**
     * Valeurs par défaut pour les attributs du modèle.
     */
    protected $attributes = [
        'category' => 'general',
        'target_audience' => 'all',
        'is_urgent' => false,
    ];

    /**
     * Relation avec l'utilisateur qui a rédigé le communiqué.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}