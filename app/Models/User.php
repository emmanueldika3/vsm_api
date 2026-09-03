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
    'is_active',
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
            'is_active' => 'boolean',
            'jersey_number' => 'integer',
        ];
    }

    /**
     * Vérifie si le membre est actif
     */
    public function isActive(): bool
    {
        return $this->is_active || $this->status === 'active';
    }

    /**
     * Vérifie si l'utilisateur est un administrateur
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est le trésorier
     */
    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    /**
     * Vérifie si l'utilisateur est le président
     */
    public function isPresident(): bool
    {
        return $this->role === 'president';
    }

    /**
     * Vérifie si l'utilisateur est le coach / entraîneur
     */
    public function isCoach(): bool
    {
        return $this->role === 'coach';
    }

    /**
     * Formatage structuré pour les réponses API REST
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
            'is_active' => (bool) $this->is_active,
            'jersey_number' => $this->jersey_number,
            'position' => $this->position,
            'photo_url' => $this->photo_url,
        ];
    }
}