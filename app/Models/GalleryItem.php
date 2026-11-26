<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "GalleryItem",
    title: "GalleryItem",
    description: "Élément photo de la galerie VSM",
    required: ["id", "title", "category", "image_url"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Victoire au Stade Yoro"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Match amical Vétérans Santé Mahen"),
        new OA\Property(property: "category", type: "string", enum: ["match", "training", "event", "general"], example: "match"),
        new OA\Property(property: "image_url", type: "string", format: "uri", example: "http://127.0.0.1:8000/storage/gallery/photo1.jpg"),
        new OA\Property(property: "date", type: "string", format: "date", example: "2026-11-19"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-11-19T02:00:00+01:00"),
        new OA\Property(
            property: "author",
            type: "object",
            nullable: true,
            properties: [
                new OA\Property(property: "id", type: "integer", example: 2),
                new OA\Property(property: "name", type: "string", example: "Membre VSM"),
                new OA\Property(property: "role", type: "string", example: "player")
            ]
        )
    ]
)]
class GalleryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'image_path',
        'photo_date',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'photo_date' => 'date',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retourne l'URL publique complète de l'image stockée sur le disque `public`.
     */
    public function getImageUrlAttribute(): string
    {
        $path = trim((string) $this->image_path);

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');
        if (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, strlen('public/'));
        }
        if (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        }

        $configuredUrl = rtrim((string) config('app.url'), '/');
        $baseUrl = ($configuredUrl !== '' && $configuredUrl !== 'http://localhost')
            ? $configuredUrl
            : rtrim(request()->getSchemeAndHttpHost(), '/');

        return $baseUrl . '/storage/' . $normalizedPath;
    }

    public function toApiArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'category'    => $this->category,
            'image_url'   => $this->image_url,
            'date'        => $this->photo_date?->format('Y-m-d') ?? $this->created_at?->format('Y-m-d'),
            'created_at'  => $this->created_at?->toIso8601String(),
            'author'      => $this->author ? [
                'id'   => $this->author->id,
                'name' => $this->author->name,
                'role' => $this->author->role,
            ] : null,
        ];
    }
}