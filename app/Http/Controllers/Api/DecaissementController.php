<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Decaissement;
use App\Models\User;
use App\Notifications\DecaissementOrdonneNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Décaissements",
    description: "Gestion financière et ordonnancement des dépenses par le Président"
)]
class DecaissementController extends Controller
{
    #[OA\Get(
        path: "/api/decaissements",
        summary: "Consulter la liste des décaissements et les statistiques financières",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des décaissements récupérée avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "stats",
                            properties: [
                                new OA\Property(property: "total_demande", type: "number", format: "float", example: 150000.00),
                                new OA\Property(property: "total_ordonne", type: "number", format: "float", example: 100000.00),
                                new OA\Property(property: "total_en_attente", type: "number", format: "float", example: 50000.00)
                            ],
                            type: "object"
                        ),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function index(): JsonResponse
    {
        $decaissements = Decaissement::with(['demandeur:id,name', 'validateur:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_demande' => Decaissement::sum('montant'),
            'total_ordonne' => Decaissement::whereIn('statut', ['valide', 'paye'])->sum('montant'),
            'total_en_attente' => Decaissement::where('statut', 'en_attente')->sum('montant'),
        ];

        return response()->json([
            'status' => 'success',
            'stats' => $stats,
            'data' => $decaissements,
        ]);
    }

    #[OA\Post(
        path: "/api/decaissements/{id}/ordonner",
        summary: "Ordonner un décaissement (Réservé au Président)",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID du décaissement à ordonner",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Décaissement ordonné et membres notifiés",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Décaissement ordonné avec succès et membres notifiés."),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Décaissement déjà traité",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Ce décaissement a déjà été traité.")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé - Rôle Président requis",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Seul le Président est habilité à ordonner un décaissement.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Décaissement introuvable")
        ]
    )]
    public function ordonner(Request $request, $id): JsonResponse
    {
        if ($request->user()->role !== 'president') {
            return response()->json([
                'status' => 'error',
                'message' => 'Seul le Président est habilité à ordonner un décaissement.'
            ], 403);
        }

        $decaissement = Decaissement::findOrFail($id);

        if ($decaissement->statut !== 'en_attente') {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce décaissement a déjà été traité.'
            ], 400);
        }

        // Validation par le Président
        $decaissement->update([
            'statut' => 'valide',
            'ordonne_par' => $request->user()->id,
            'ordonne_le' => now(),
        ]);

        // Notification envoyée à tous les membres VSM
        $membres = User::where('is_active', true)->get();
        Notification::send($membres, new DecaissementOrdonneNotification($decaissement));

        return response()->json([
            'status' => 'success',
            'message' => 'Décaissement ordonné avec succès et membres notifiés.',
            'data' => $decaissement
        ]);
    }
}