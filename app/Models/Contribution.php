<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contribution extends Model
{
    protected $fillable = [
        'user_id',
        'season_id',
        'type',
        'title',
        'amount_expected',
        'amount_paid',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount_expected' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Le membre concerné par l'obligation
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // La saison sportive liée
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    // Les différentes tranches / paiements réels associés à cette contribution
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Recalcule automatiquement le montant payé et met à jour le statut
     * en fonction de toutes les transactions enregistrées.
     */
    public function recalculateBalance(): void
    {
        $totalPaid = $this->transactions()->sum('amount');
        $this->amount_paid = $totalPaid;

        if ($totalPaid <= 0) {
            $this->status = 'unpaid';
            $this->paid_at = null;
        } elseif ($totalPaid < $this->amount_expected) {
            $this->status = 'partial';
            $this->paid_at = null;
        } else {
            $this->status = 'paid';
            $this->paid_at = now();
        }

        $this->save();
    }
}