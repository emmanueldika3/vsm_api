<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'amount',
        'status',
        'approved_by',
        'executed_by',
        'approved_at',
        'executed_at',
    ];

    protected $casts = [
        'amount' => 'double',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    // --- RELATIONS ---

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // --- SCOPES ---

    /**
     * Seules les dépenses effectivement payées (sortie de caisse réelle).
     */
    public function scopeExecuted($query)
    {
        return $query->where('status', 'executed');
    }

    /**
     * Ordres de décaissement validés mais en attente de paiement par le trésorier.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Demandes de dépenses initiales en attente d'approbation.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}