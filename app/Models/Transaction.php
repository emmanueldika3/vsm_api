<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'contribution_id',
        'user_id',
        'amount',
        'payment_method',
        'reference',
        'executed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Boot method pour déclencher automatiquement le recalcul du solde
     * de la contribution parente à chaque création, modification ou suppression d'une tranche.
     */
    protected static function booted()
    {
        static::saved(function (Transaction $transaction) {
            if ($transaction->contribution) {
                $transaction->contribution->recalculateBalance();
            }
        });

        static::deleted(function (Transaction $transaction) {
            if ($transaction->contribution) {
                $transaction->contribution->recalculateBalance();
            }
        });
    }

    // La contribution (obligation) à laquelle se rattache cette tranche
    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    // Le membre qui effectue le paiement
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Le trésorier (administrateur) qui a validé l'encaissement
    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}