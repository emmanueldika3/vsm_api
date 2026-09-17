<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Decaissement;

#[OA\Tag(
    name: 'Admin Finances',
    description: 'Gestion financière du tableau de bord d\'administration VSM'
)]
class FinanceController extends Controller
{
    /**
     * Calcule le solde disponible en caisse.
     */
    #[OA\Get(
        path: '/api/admin/finances/cash-balance',
        summary: 'Obtenir le solde disponible en caisse',
        description: 'Calcule le solde net disponible (Cotisations reçues - Dépenses exécutées).',
        security: [['sanctum' => []]],
        tags: ['Admin Finances'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Solde récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_balance', type: 'number', format: 'float', example: 1250000.00),
                                new OA\Property(property: 'currency', type: 'string', example: 'XAF')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Erreur lors du calcul du solde...')
                    ]
                )
            )
        ]
    )]
    public function getCashBalance(): JsonResponse
{
    // Total des entrées validées
    $totalInflow = Contribution::where('status', 'paid')->sum('amount');

    // Total des sorties exécutées
    $totalOutflow = Expense::where('status', 'executed')->sum('amount');

    // Calcul du solde net
    $totalBalance = (float) ($totalInflow - $totalOutflow);

    return response()->json([
        'status' => 'success',
        'data' => [
            'total_balance' => $totalBalance,
            'currency' => 'XAF',
            'is_negative' => $totalBalance < 0,
        ]
    ], 200);
}

    /**
     * Statistiques des décaissements exécutés.
     */
    #[OA\Get(
        path: '/api/admin/finances/executed-disbursements',
        summary: 'Obtenir le cumul des décaissements exécutés',
        description: 'Calcule le total des dépenses décaissées (status = executed) et leur nombre.',
        security: [['sanctum' => []]],
        tags: ['Admin Finances'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques des décaissements récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_executed', type: 'number', format: 'float', example: 450000.00),
                                new OA\Property(property: 'currency', type: 'string', example: 'XAF'),
                                new OA\Property(property: 'executed_count', type: 'integer', example: 12)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Erreur lors du calcul des décaissements...')
                    ]
                )
            )
        ]
    )]
    public function getExecutedDisbursements(Request $request): JsonResponse
    {
        try {
            $query = Expense::executed();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_executed' => (float) $query->sum('amount'),
                    'currency' => 'XAF',
                    'executed_count' => $query->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Statistiques des décaissements en attente de validation.
     */
    #[OA\Get(
        path: '/api/admin/finances/pending-disbursements',
        summary: 'Obtenir le cumul des décaissements en attente',
        description: 'Calcule le total des dépenses en attente de validation (status = pending) et leur nombre.',
        security: [['sanctum' => []]],
        tags: ['Admin Finances'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques des décaissements en attente récupérées avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_pending', type: 'number', format: 'float', example: 150000.00),
                                new OA\Property(property: 'currency', type: 'string', example: 'XAF'),
                                new OA\Property(property: 'pending_count', type: 'integer', example: 3)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Erreur lors du calcul des décaissements en attente...')
                    ]
                )
            )
        ]
    )]
    public function getPendingDisbursements(Request $request): JsonResponse
    {
        try {
            // Utilise le scope pending() sur le modèle Expense (ou query()->where('status', 'pending'))
            $query = Expense::where('status', 'pending');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_pending' => (float) $query->sum('amount'),
                    'currency' => 'XAF',
                    'pending_count' => (int) $query->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    //Récupération des contributions validées
    #[OA\Get(
    path: "/api/admin/finances/collected-contributions",
    summary: "Obtenir le total des cotisations perçues",
    tags: ["Finances"],
    security: [["sanctum" => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: "Succès",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "success"),
                    new OA\Property(
                        property: "data",
                        type: "object",
                        properties: [
                            new OA\Property(property: "total_collected", type: "number", example: 450000),
                            new OA\Property(property: "currency", type: "string", example: "XAF"),
                            new OA\Property(property: "contributions_count", type: "integer", example: 12)
                        ]
                    )
                ]
            )
        ),
        new OA\Response(response: 401, description: "Non authentifié")
    ]
)]
    public function getCollectedContributions(): JsonResponse
{
    // Remplace 'paid' par le statut exact utilisé dans ta BDD (ex: 'valide', 'paid')
    $query = Contribution::where('status', 'paid');

    return response()->json([
        'status' => 'success',
        'data' => [
            'total_collected' => (float) $query->sum('amount'),
            'currency' => 'XAF',
            'contributions_count' => (int) $query->count(),
        ]
    ], 200);
}
}