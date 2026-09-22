<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'phone',
    'email',
    'password',
    'role',
    'status',
    'jersey_number',
    'position',
    'photo_url',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'jersey_number' => 'integer',
        ];
    }

    // ==========================================
    // SCOPES DE REQUÊTE
    // ==========================================

    /**
     * Scope pour filtrer les membres en attente de validation
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour filtrer les membres actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ==========================================
    // RELATIONS
    // ==========================================

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    // ==========================================
    // HELPERS DE STATUT ET RÔLE
    // ==========================================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    public function isPresident(): bool
    {
        return $this->role === 'president';
    }

    public function isCoach(): bool
    {
        return $this->role === 'coach';
    }

    // ==========================================
    // ACCESSORS ET FORMATAGE API
    // ==========================================

    /**
     * Accessor pour obtenir l'URL absolue de la photo de profil
     */
    public function getPhotoUrlAttribute(): ?string
    {
        $photo = $this->attributes['photo_url'] ?? null;

        if ($photo) {
            return str_starts_with($photo, 'http') ? $photo : asset('storage/' . $photo);
        }

        return null;
    }

    /**
     * Formatage structuré pour les réponses API REST (Flutter)
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'jersey_number' => $this->jersey_number,
            'position' => $this->position,
            'photo_url' => $this->photo_url,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}