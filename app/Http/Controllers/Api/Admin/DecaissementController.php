<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Décaissements",
    description: "Gestion et suivi des décaissements et ordonnancements de dépenses"
)]
class DecaissementController extends Controller
{
    #[OA\Get(
        path: "/api/admin/decaissements/pending",
        summary: "Obtenir la liste des décaissements en attente",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des décaissements en attente récupérée avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "title", type: "string", example: "Achat de 10 ballons de match"),
                                    new OA\Property(property: "description", type: "string", example: "Achat chez le fournisseur officiel."),
                                    new OA\Property(property: "amount", type: "number", format: "float", example: 150000.00),
                                    new OA\Property(property: "status", type: "string", example: "pending"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-09-09T01:22:21Z")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function getPendingDisbursements(): JsonResponse
    {
        $expenses = Expense::where('status', 'pending')->get();

        return response()->json([
            'status' => 'success',
            'data' => $expenses
        ], 200);
    }

    #[OA\Get(
        path: "/api/admin/decaissements/metrics/pending",
        summary: "Statistiques des décaissements en attente",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Métriques des décaissements en attente récupérées avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "count", type: "integer", example: 1),
                                new OA\Property(property: "total_amount", type: "number", format: "float", example: 50000.00)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function getPendingMetrics(): JsonResponse
    {
        $query = Expense::where('status', 'pending');

        return response()->json([
            'status' => 'success',
            'data' => [
                'count' => $query->count(),
                'total_amount' => (float) $query->sum('amount')
            ]
        ], 200);
    }

    #[OA\Get(
        path: "/api/admin/decaissements/metrics/executed",
        summary: "Statistiques des décaissements ordonnés / exécutés",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Métriques des décaissements exécutés et ordonnés récupérées avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "count", type: "integer", example: 3),
                                new OA\Property(property: "total_amount", type: "number", format: "float", example: 390000.00)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function getExecutedMetrics(): JsonResponse
    {
        $query = Expense::whereIn('status', ['approved', 'executed']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'count' => $query->count(),
                'total_amount' => (float) $query->sum('amount')
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/api/admin/decaissements/{id}/ordonner",
        summary: "Ordonner un décaissement (Approuver ou Rejeter)",
        description: "Met à jour le statut en 'approved' ou 'rejected', enregistre l'utilisateur ordonnateur et met à jour les dates.",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la dépense à ordonner",
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
                        enum: ["approved", "rejected"], 
                        example: "approved"
                    ),
                    new OA\Property(
                        property: "rejection_reason", 
                        type: "string", 
                        nullable: true, 
                        example: "Motif obligatoire uniquement en cas de rejet"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Statut mis à jour avec succès en BDD",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Décaissement approuvé avec succès."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "status", type: "string", example: "approved"),
                                new OA\Property(property: "approved_by", type: "integer", example: 1),
                                new OA\Property(property: "approved_at", type: "string", format: "date-time", example: "2026-09-15T13:08:17Z")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422, 
                description: "Erreur de validation ou demande déjà traitée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Cette demande a déjà été traitée.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Dépense introuvable")
        ]
    )]
    public function processOrdonnancement(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cette demande a déjà été traitée.'
            ], 422);
        }

        // Mise à jour du statut selon le choix ("approved" ou "rejected")
        $expense->status = $request->status;

        if ($request->status === 'approved') {
            $expense->approved_by = $request->user()->id;
            $expense->approved_at = now();
            $expense->rejection_reason = null;
        } else {
            $expense->rejection_reason = $request->rejection_reason;
            $expense->approved_by = null;
            $expense->approved_at = null;
        }

        // Enregistrement effectif en base de données
        $expense->save();

        return response()->json([
            'status' => 'success',
            'message' => $request->status === 'approved' 
                ? 'Décaissement approuvé avec succès.' 
                : 'Demande de décaissement rejetée avec succès.',
            'data' => $expense
        ], 200);
    }
}