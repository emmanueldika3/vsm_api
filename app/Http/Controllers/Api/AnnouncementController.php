<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Announcements",
    description: "Endpoints pour la gestion des communiqués officiels VSM"
)]
class AnnouncementController extends Controller
{
    #[OA\Get(
        path: "/api/announcements",
        summary: "Liste de tous les communiqués",
        description: "Récupère la liste des communiqués triés du plus récent au plus ancien. Les membres simples ne voient que les annonces destinées à tout le club.",
        tags: ["Announcements"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "author_id", type: "integer", example: 1),
                            new OA\Property(property: "title", type: "string", example: "Réunion générale"),
                            new OA\Property(property: "content", type: "string", example: "Ordre du jour : Préparation du prochain match."),
                            new OA\Property(property: "category", type: "string", enum: ["training", "meeting", "match", "general"], example: "meeting"),
                            new OA\Property(property: "target_audience", type: "string", enum: ["all", "board"], example: "all"),
                            new OA\Property(property: "is_urgent", type: "boolean", example: true),
                            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-08-28T14:30:00.000000Z"),
                            new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-08-28T14:30:00.000000Z"),
                            new OA\Property(
                                property: "author",
                                type: "object",
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Emmanuel Dika"),
                                    new OA\Property(property: "email", type: "string", example: "admin@vsm.com")
                                ]
                            )
                        ]
                    )
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Announcement::with('author:id,name,email')
            ->orderBy('created_at', 'desc');

        // Vérifier si l'utilisateur fait partie du bureau exécutif / staff
        $isBoardMember = $user && in_array($user->role, ['president', 'admin', 'coach', 'treasurer']);

        // Si l'utilisateur n'est pas membre du bureau, on restreint aux annonces 'all'
        if (!$isBoardMember) {
            $query->where('target_audience', 'all');
        }

        return response()->json($query->paginate(10), 200);
    }

    #[OA\Post(
        path: "/api/announcements",
        summary: "Créer un nouveau communiqué",
        description: "Enregistre un nouveau communiqué en base de données (Réservé au bureau et au coach).",
        tags: ["Announcements"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "content", "category", "target_audience"],
                properties: [
                    new OA\Property(property: "title", type: "string", maxLength: 255, example: "Cotisation mensuelle"),
                    new OA\Property(property: "content", type: "string", example: "Prière de régler vos cotisations avant le 5 du mois."),
                    new OA\Property(property: "category", type: "string", enum: ["training", "meeting", "match", "general"], example: "general"),
                    new OA\Property(property: "target_audience", type: "string", enum: ["all", "board"], example: "all"),
                    new OA\Property(property: "is_urgent", type: "boolean", example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Communiqué créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 2),
                        new OA\Property(property: "author_id", type: "integer", example: 1),
                        new OA\Property(property: "title", type: "string", example: "Cotisation mensuelle"),
                        new OA\Property(property: "content", type: "string", example: "Prière de régler vos cotisations avant le 5 du mois."),
                        new OA\Property(property: "category", type: "string", example: "general"),
                        new OA\Property(property: "target_audience", type: "string", example: "all"),
                        new OA\Property(property: "is_urgent", type: "boolean", example: false),
                        new OA\Property(property: "created_at", type: "string", format: "date-time"),
                        new OA\Property(property: "updated_at", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé - Rôle insuffisant",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Accès refusé. Seul le bureau/coach peut créer un communiqué.")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié"
            ),
            new OA\Response(
                response: 422,
                description: "Erreur de validation des données"
            )
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Contrôle d'accès par rôle
        if (!$user || !in_array($user->role, ['president', 'admin', 'coach', 'treasurer'])) {
            return response()->json([
                'message' => 'Accès refusé. Seul le bureau/coach peut créer un communiqué.'
            ], 403);
        }

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'category'        => 'required|in:training,meeting,match,general',
            'target_audience' => 'required|in:all,board',
            'is_urgent'       => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'author_id'       => $user->id,
            'title'           => $validated['title'],
            'content'         => $validated['content'],
            'category'        => $validated['category'],
            'target_audience' => $validated['target_audience'],
            'is_urgent'       => $validated['is_urgent'] ?? false,
        ]);

        return response()->json($announcement->load('author:id,name,email'), 201);
    }

    #[OA\Put(
        path: "/api/announcements/{id}",
        summary: "Mettre à jour un communiqué",
        description: "Modifie les informations d'un communiqué existant.",
        tags: ["Announcements"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du communiqué",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "content", "category", "target_audience"],
                properties: [
                    new OA\Property(property: "title", type: "string", maxLength: 255, example: "Mise à jour : Réunion générale"),
                    new OA\Property(property: "content", type: "string", example: "La réunion est reportée à 16h00."),
                    new OA\Property(property: "category", type: "string", enum: ["training", "meeting", "match", "general"], example: "meeting"),
                    new OA\Property(property: "target_audience", type: "string", enum: ["all", "board"], example: "board"),
                    new OA\Property(property: "is_urgent", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Communiqué mis à jour avec succès"
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé - Rôle insuffisant"
            ),
            new OA\Response(
                response: 404,
                description: "Communiqué non trouvé"
            )
        ]
    )]
    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['president', 'admin', 'coach', 'treasurer'])) {
            return response()->json([
                'message' => 'Accès refusé. Vous n\'avez pas les droits pour modifier ce communiqué.'
            ], 403);
        }

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'category'        => 'required|in:training,meeting,match,general',
            'target_audience' => 'required|in:all,board',
            'is_urgent'       => 'nullable|boolean',
        ]);

        $announcement->update([
            'title'           => $validated['title'],
            'content'         => $validated['content'],
            'category'        => $validated['category'],
            'target_audience' => $validated['target_audience'],
            'is_urgent'       => $validated['is_urgent'] ?? $announcement->is_urgent,
        ]);

        return response()->json($announcement->load('author:id,name,email'), 200);
    }

    #[OA\Delete(
        path: "/api/announcements/{id}",
        summary: "Supprimer un communiqué",
        description: "Supprime définitivement un communiqué de la base de données.",
        tags: ["Announcements"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du communiqué",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Communiqué supprimé avec succès"
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé - Rôle insuffisant"
            ),
            new OA\Response(
                response: 404,
                description: "Communiqué non trouvé"
            )
        ]
    )]
   
    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['president', 'admin', 'coach', 'treasurer'])) {
            return response()->json([
                'message' => 'Accès refusé. Vous n\'avez pas les droits pour supprimer ce communiqué.'
            ], 403);
        }

        $announcement->delete();

        return response()->json(['message' => 'Communiqué supprimé'], 200);
    }
    #[OA\Get(
        path: "/api/announcements/{id}",
        summary: "Afficher les détails d'un communiqué",
        description: "Récupère un communiqué spécifique par son ID. L'accès est restreint aux membres du bureau si l'audience ciblée est 'board'.",
        tags: ["Announcements"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du communiqué",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails du communiqué récupérés avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "author_id", type: "integer", example: 1),
                        new OA\Property(property: "title", type: "string", example: "Réunion générale"),
                        new OA\Property(property: "content", type: "string", example: "Ordre du jour : Préparation du prochain match."),
                        new OA\Property(property: "category", type: "string", enum: ["training", "meeting", "match", "general"], example: "meeting"),
                        new OA\Property(property: "target_audience", type: "string", enum: ["all", "board"], example: "board"),
                        new OA\Property(property: "is_urgent", type: "boolean", example: true),
                        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-08-28T14:30:00.000000Z"),
                        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-08-28T14:30:00.000000Z"),
                        new OA\Property(
                            property: "author",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Emmanuel Dika"),
                                new OA\Property(property: "email", type: "string", example: "admin@vsm.com")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé - Ce communiqué est réservé au bureau",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Accès refusé. Ce communiqué est réservé au bureau.")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié"
            ),
            new OA\Response(
                response: 404,
                description: "Communiqué non trouvé"
            )
        ]
    )]
    public function show(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();

        // Vérifier si l'utilisateur est membre du bureau
        $isBoardMember = $user && in_array($user->role, ['president', 'admin', 'coach', 'treasurer']);

        // Si l'annonce est réservée au bureau et que l'utilisateur est un simple membre
        if ($announcement->target_audience === 'board' && !$isBoardMember) {
            return response()->json([
                'message' => 'Accès refusé. Ce communiqué est réservé au bureau.'
            ], 403);
        }

        return response()->json($announcement->load('author:id,name,email'), 200);
    }
     #[OA\Get(
        path: "/announcements/latest",
        summary: "Récupérer le dernier communiqué publié",
        tags: ["Communiqués"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dernier communiqué récupéré avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            nullable: true,
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "title", type: "string", example: "Réunion générale"),
                                new OA\Property(property: "content", type: "string", example: "Ordre du jour : Préparation du tournoi..."),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-09-27T03:00:00.000000Z")
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function latest()
    {
        // Récupère le dernier communiqué créé
        $announcement = Announcement::latest()->first();

        return response()->json([
            'status' => 'success',
            'data' => $announcement
        ], 200);
    }
}