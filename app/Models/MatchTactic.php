<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchTactic extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'formation',
        'starting_xi',
        'substitutes',
        'is_published',
    ];

    protected $casts = [
        'starting_xi' => 'array',
        'substitutes' => 'array',
        'is_published' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(EventModel::class, 'event_id');
    }
}