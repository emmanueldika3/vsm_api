<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\Contribution;
use App\Models\Expense;

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
    public function getCashBalance(Request $request): JsonResponse
    {
        try {
            $totalIncomes = Contribution::sum('amount'); 
            
            // Seuls les décaissements EXÉCUTÉS réduisent le solde disponible
            $totalExpenses = Expense::executed()->sum('amount');

            $balance = $totalIncomes - $totalExpenses;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_balance' => (float) $balance,
                    'currency' => 'XAF',
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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
}