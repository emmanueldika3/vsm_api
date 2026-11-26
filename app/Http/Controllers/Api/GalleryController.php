<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class GalleryController extends Controller
{
    #[OA\Get(
        path: "/api/gallery",
        summary: "Liste des photos de la galerie VSM",
        security: [["bearerAuth" => []]],
        tags: ["Galerie"],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "query",
                required: false,
                description: "Filtrer par catégorie (match, training, event, general)",
                schema: new OA\Schema(type: "string", example: "match")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Galerie récupérée avec succès"),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = GalleryItem::with('author:id,name,role')
            ->orderByDesc('photo_date')
            ->orderByDesc('created_at');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', strtolower(trim((string) $request->category)));
        }

        $items = $query->get()->map(fn (GalleryItem $item) => $item->toApiArray());

        return response()->json([
            'status' => 'success',
            'data'   => $items,
            'count'  => $items->count(),
        ], 200);
    }

    #[OA\Post(
        path: "/api/gallery",
        summary: "Ajouter une photo à la galerie VSM",
        description: "Accessible à tous les membres authentifiés du club.",
        security: [["bearerAuth" => []]],
        tags: ["Galerie"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["title", "photo"],
                    properties: [
                        new OA\Property(property: "title", type: "string", example: "Victoire au Stade Yoro"),
                        new OA\Property(property: "description", type: "string", nullable: true, example: "Match amical des vétérans"),
                        new OA\Property(property: "category", type: "string", enum: ["match", "training", "event", "general"], example: "match"),
                        new OA\Property(property: "photo_date", type: "string", format: "date", nullable: true, example: "2026-11-18"),
                        new OA\Property(property: "photo", type: "string", format: "binary", description: "Image JPG, PNG ou WEBP (max 5 Mo)")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Photo ajoutée à la galerie"),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 422, description: "Données invalides")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category'    => ['nullable', 'string', Rule::in(['match', 'training', 'event', 'general'])],
            'photo_date'  => ['nullable', 'date'],
            'photo'       => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $path = $request->file('photo')->store('gallery', 'public');

        $item = GalleryItem::create([
            'user_id'     => $user?->id,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category'    => $validated['category'] ?? 'general',
            'image_path'  => $path,
            'photo_date'  => $validated['photo_date'] ?? now()->toDateString(),
        ]);

        $item->load('author:id,name,role');

        return response()->json([
            'status'  => 'success',
            'message' => 'Photo enregistrée dans la galerie avec succès.',
            'data'    => $item->toApiArray(),
        ], 201);
    }

    #[OA\Delete(
        path: "/api/gallery/{id}",
        summary: "Supprimer une photo de la galerie",
        security: [["bearerAuth" => []]],
        tags: ["Galerie"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Photo supprimée"),
            new OA\Response(response: 403, description: "Action non autorisée"),
            new OA\Response(response: 404, description: "Photo introuvable")
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = GalleryItem::find($id);

        if (!$item) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Photo introuvable dans la galerie.',
            ], 404);
        }

        $user = $request->user();
        $canDelete = $user && (
            in_array($user->role, ['president', 'admin', 'secretary'], true)
            || ($item->user_id !== null && $item->user_id === $user->id)
        );

        if (!$canDelete) {
            return response()->json([
                'status'  => 'error',
                'message' => "Vous n'avez pas l'autorisation de supprimer cette photo.",
            ], 403);
        }

        if ($item->image_path && !str_starts_with($item->image_path, 'http')) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Photo supprimée de la galerie avec succès.',
        ], 200);
    }
}