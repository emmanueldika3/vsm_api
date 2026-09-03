<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Event",
    type: "object",
    title: "Événement",
    description: "Modèle représentant un événement (match ou entraînement)",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Entraînement hebdomadaire"),
        new OA\Property(property: "type", type: "string", enum: ["training", "match"], example: "training"),
        new OA\Property(property: "date", type: "string", format: "date-time", example: "2026-08-22 06:00:00"),
        new OA\Property(property: "location", type: "string", example: "Stade PK11, Douala"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Séance d'entraînement physique et mise en place tactique.")
    ]
)]
class EventController extends Controller
{
    #[OA\Get(
        path: "/events",
        summary: "Liste des matchs et entraînements à venir",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        parameters: [
            new OA\Parameter(
                name: "type",
                in: "query",
                required: false,
                description: "Filtrer par type d'événement (training, match)",
                schema: new OA\Schema(type: "string", enum: ["training", "match"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Calendrier récupéré avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Event")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Entraînement hebdomadaire',
                    'type' => 'training',
                    'date' => '2026-08-22 06:00:00',
                    'location' => 'Stade PK11, Douala',
                    'description' => 'Séance d\'entraînement physique et mise en place tactique.'
                ],
                [
                    'id' => 2,
                    'title' => 'Match Amical vs Vétérans Bonabéri',
                    'type' => 'match',
                    'date' => '2026-08-23 15:00:00',
                    'location' => 'Stade PK11, Douala',
                    'description' => 'Match amical de préparation.'
                ]
            ]
        ]);
    }

    #[OA\Get(
        path: "/events/{id}",
        summary: "Détails d'un événement",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'événement",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails récupérés avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Event")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Événement introuvable")
        ]
    )]
    public function show($id)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (int) $id,
                'title' => 'Entraînement hebdomadaire',
                'type' => 'training',
                'date' => '2026-08-22 06:00:00',
                'location' => 'Stade PK11, Douala',
                'description' => 'Séance d\'entraînement physique et mise en place tactique.'
            ]
        ]);
    }

    #[OA\Post(
        path: "/events",
        summary: "Créer un nouvel événement (Admin / Coach)",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "type", "date", "location"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Match vs FC Akwa"),
                    new OA\Property(property: "type", type: "string", enum: ["training", "match"], example: "match"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "2026-08-30 15:30:00"),
                    new OA\Property(property: "location", type: "string", example: "Stade PK11, Douala"),
                    new OA\Property(property: "description", type: "string", example: "Match amical de préparation.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Événement créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Événement créé avec succès.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 403, description: "Accès non autorisé"),
            new OA\Response(response: 422, description: "Données de formulaire invalides")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:training,match',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Événement créé avec succès.'
        ], 201);
    }

    #[OA\Put(
        path: "/events/{id}",
        summary: "Modifier un événement (Admin / Coach)",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'événement à modifier",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Entraînement déplacé"),
                    new OA\Property(property: "type", type: "string", enum: ["training", "match"], example: "training"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "2026-08-22 07:00:00"),
                    new OA\Property(property: "location", type: "string", example: "Stade PK11, Douala"),
                    new OA\Property(property: "description", type: "string", example: "Changement d'horaire exceptionnel.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Événement mis à jour avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Événement mis à jour avec succès.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 403, description: "Accès non autorisé"),
            new OA\Response(response: 404, description: "Événement introuvable"),
            new OA\Response(response: 422, description: "Données de formulaire invalides")
        ]
    )]
    public function update(Request $request, $id)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Événement mis à jour avec succès.'
        ]);
    }

    #[OA\Delete(
        path: "/events/{id}",
        summary: "Supprimer un événement (Admin / Coach)",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'événement à supprimer",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Événement supprimé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Événement supprimé avec succès.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 403, description: "Accès non autorisé"),
            new OA\Response(response: 404, description: "Événement introuvable")
        ]
    )]
    public function destroy($id)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Événement supprimé avec succès.'
        ]);
    }

    #[OA\Post(
        path: "/events/{id}/presence",
        summary: "Confirmer ou décliner sa présence à un événement",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'événement (Match / Entraînement)",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(
                        property: "status",
                        type: "string",
                        enum: ["present", "absent", "uncertain"],
                        example: "present",
                        description: "Statut de présence du joueur"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Statut de présence mis à jour avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Votre présence a été enregistrée avec succès.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Événement introuvable"),
            new OA\Response(response: 422, description: "Statut de présence invalide")
        ]
    )]
    public function updatePresence(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:present,absent,uncertain',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Votre présence a été enregistrée avec succès.'
        ]);
    }
}