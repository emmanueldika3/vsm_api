<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPresence;
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
        new OA\Property(property: "event_date_time", type: "string", format: "date-time", example: "2026-08-22 06:00:00"),
        new OA\Property(property: "venue", type: "string", example: "Stade PK11, Douala"),
        new OA\Property(property: "home_team", type: "string", nullable: true, example: "VSM FC"),
        new OA\Property(property: "away_team", type: "string", nullable: true, example: "FC Akwa"),
        new OA\Property(property: "home_logo_url", type: "string", nullable: true, example: "https://example.com/logo1.png"),
        new OA\Property(property: "away_logo_url", type: "string", nullable: true, example: "https://example.com/logo2.png"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Séance d'entraînement physique."),
        new OA\Property(property: "user_presence", type: "string", enum: ["present", "absent", "uncertain", "none"], example: "present"),
        new OA\Property(property: "present_count", type: "integer", example: 12),
        new OA\Property(property: "uncertain_count", type: "integer", example: 3),
        new OA\Property(property: "absent_count", type: "integer", example: 2)
    ]
)]
class EventController extends Controller
{
    #[OA\Get(
        path: "/events",
        summary: "Liste de tous les matchs et entraînements",
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
        $query = Event::query();

        if ($request->has('type') && in_array($request->type, ['training', 'match'])) {
            $query->where('type', $request->type);
        }

        $userId = $request->user()?->id;
        $events = $query->orderBy('event_date_time', 'asc')->get()->map(function ($event) use ($userId) {
            return $this->formatEventData($event, $userId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $events
        ], 200);
    }

    #[OA\Get(
        path: "/events/upcoming",
        summary: "Prochain événement à venir avec le statut de présence du membre",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Prochain événement récupéré avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Event")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function upcoming(Request $request)
    {
        try {
            $event = Event::where('event_date_time', '>=', now())
                          ->orderBy('event_date_time', 'asc')
                          ->first();

            if (!$event) {
                return response()->json([
                    'status' => 'success',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $this->formatEventData($event, $request->user()?->id)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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
    public function show(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Événement introuvable.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->formatEventData($event, $request->user()?->id)
        ], 200);
    }

    #[OA\Post(
        path: "/events",
        summary: "Créer un nouvel événement (Admin / Coach)",
        security: [["bearerAuth" => []]],
        tags: ["Événements"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "type", "event_date_time", "venue"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Match vs FC Akwa"),
                    new OA\Property(property: "type", type: "string", enum: ["training", "match"], example: "match"),
                    new OA\Property(property: "event_date_time", type: "string", format: "date-time", example: "2026-08-30 15:30:00"),
                    new OA\Property(property: "venue", type: "string", example: "Stade PK11, Douala"),
                    new OA\Property(property: "home_team", type: "string", example: "VSM FC"),
                    new OA\Property(property: "away_team", type: "string", example: "FC Akwa"),
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
                        new OA\Property(property: "message", type: "string", example: "Événement créé avec succès."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Event")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 422, description: "Données de formulaire invalides")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:training,match',
            'event_date_time' => 'required|date',
            'venue' => 'required|string|max:255',
            'home_team' => 'nullable|string|max:255',
            'away_team' => 'nullable|string|max:255',
            'home_logo_url' => 'nullable|url',
            'away_logo_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        $event = Event::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Événement créé avec succès.',
            'data' => $this->formatEventData($event, $request->user()?->id)
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
                    new OA\Property(property: "event_date_time", type: "string", format: "date-time", example: "2026-08-22 07:00:00"),
                    new OA\Property(property: "venue", type: "string", example: "Stade PK11, Douala"),
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
                        new OA\Property(property: "message", type: "string", example: "Événement mis à jour avec succès."),
                        new OA\Property(property: "data", ref: "#/components/schemas/Event")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Événement introuvable"),
            new OA\Response(response: 422, description: "Données de formulaire invalides")
        ]
    )]
    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Événement introuvable.'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:training,match',
            'event_date_time' => 'sometimes|date',
            'venue' => 'sometimes|string|max:255',
            'home_team' => 'nullable|string|max:255',
            'away_team' => 'nullable|string|max:255',
            'home_logo_url' => 'nullable|url',
            'away_logo_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $event->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Événement mis à jour avec succès.',
            'data' => $this->formatEventData($event, $request->user()?->id)
        ], 200);
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
            new OA\Response(response: 404, description: "Événement introuvable")
        ]
    )]
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Événement introuvable.'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Événement supprimé avec succès.'
        ], 200);
    }

    #[OA\Post(
        path: "/events/{id}/presence",
        summary: "Confirmer, décliner ou signaler son retard à un événement",
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
                        description: "Statut de présence du membre"
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
                        new OA\Property(property: "message", type: "string", example: "Statut de présence mis à jour avec succès."),
                        new OA\Property(property: "user_presence", type: "string", example: "present"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Event")
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
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Événement introuvable.'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:present,absent,uncertain',
        ]);

        $userId = $request->user()->id;

        $presence = EventPresence::updateOrCreate(
            [
                'event_id' => $id,
                'user_id' => $userId,
            ],
            [
                'status' => $validated['status'],
            ]
        );

        $formattedEvent = $this->formatEventData($event, $userId);

        return response()->json([
            'status' => 'success',
            'message' => 'Statut de présence mis à jour avec succès.',
            'user_presence' => $presence->status,
            'data' => $formattedEvent
        ], 200);
    }

    /**
     * Méthode privée pour formater l'événement avec présence et compteurs.
     */
    private function formatEventData(Event $event, ?int $userId): array
    {
        $userPresence = 'none';

        if ($userId) {
            $presence = EventPresence::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->first();

            if ($presence) {
                $userPresence = $presence->status;
            }
        }

        $presentCount = EventPresence::where('event_id', $event->id)->where('status', 'present')->count();
        $uncertainCount = EventPresence::where('event_id', $event->id)->where('status', 'uncertain')->count();
        $absentCount = EventPresence::where('event_id', $event->id)->where('status', 'absent')->count();

        $data = $event->toArray();
        $data['user_presence'] = $userPresence;
        $data['present_count'] = $presentCount;
        $data['uncertain_count'] = $uncertainCount;
        $data['absent_count'] = $absentCount;

        return $data;
    }
}