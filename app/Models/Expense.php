<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Expense",
    title: "Expense",
    description: "Modèle représentant une dépense / un décaissement du club",
    required: ["title", "amount", "status"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Frais de transport match amical"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Paiement du bus."),
        new OA\Property(property: "amount", type: "number", format: "float", example: 85000.00),
        new OA\Property(property: "status", type: "string", enum: ["pending", "approved", "rejected", "executed"], example: "executed"),
        new OA\Property(property: "rejection_reason", type: "string", nullable: true, example: "Justificatif manquant"),
        new OA\Property(property: "approved_by", type: "integer", nullable: true, example: 1),
        new OA\Property(property: "executed_by", type: "integer", nullable: true, example: 1),
        new OA\Property(property: "approved_at", type: "string", format: "date-time", nullable: true, example: "2026-09-12T15:30:00Z"),
        new OA\Property(property: "executed_at", type: "string", format: "date-time", nullable: true, example: "2026-09-13T10:00:00Z"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-09-12T12:00:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-09-13T10:00:00Z")
    ]
)]
class Expense extends Model
{
    use HasFactory;

    /**
     * Table associée au modèle.
     *
     * @var string
     */
    protected $table = 'expenses';

    /**
     * Attributs assignables en masse (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'amount',
        'status',
        'rejection_reason',
        'approved_by',
        'executed_by',
        'approved_at',
        'executed_at',
    ];

    /**
     * Transtypage des attributs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS ELOQUENT
    |--------------------------------------------------------------------------
    */

    /**
     * Utilisateur qui a validé / ordonnancé le décaissement.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Utilisateur (trésorier/admin) qui a exécuté le paiement.
     */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES LOCALES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope : Décaissements en attente d'ordonnancement.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope : Décaissements validés / approuvés.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope : Décaissements exécutés.
     */
    public function scopeExecuted(Builder $query): Builder
    {
        return $query->where('status', 'executed');
    }

    /**
     * Scope : Décaissements rejetés.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }
}