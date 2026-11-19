<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'name',
        'adhesion_fee',
        'renewal_fee',
        'annual_fee',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'adhesion_fee' => 'decimal:2',
        'renewal_fee' => 'decimal:2',
        'annual_fee' => 'decimal:2',
    ];

    // Une saison possède plusieurs contributions (obligations financières des membres)
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }
}