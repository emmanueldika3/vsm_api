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
        path: "/api/admin/decaissements",
        summary: "Obtenir la liste de tous les décaissements",
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
    public function index(Request $request): JsonResponse
    {
        $query = Expense::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $expenses = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $expenses
        ], 200);
    }

    #[OA\Post(
        path: "/api/admin/decaissements",
        summary: "Créer une nouvelle demande de décaissement (Trésorier)",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "amount"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Achat de matériel"),
                    new OA\Property(property: "description", type: "string", example: "Détails de la dépense"),
                    new OA\Property(property: "amount", type: "number", format: "float", example: 25000.00)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Demande créée avec succès"),
            new OA\Response(response: 422, description: "Erreur de validation")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $expense = Expense::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Demande de décaissement créée et transmise avec succès.',
            'data' => $expense
        ], 201);
    }

    #[OA\Get(
        path: "/api/admin/decaissements/pending",
        summary: "Obtenir la liste des décaissements en attente",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Succès")
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
            new OA\Response(response: 200, description: "Succès")
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
        summary: "Statistiques des décaissements exécutés",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Succès")
        ]
    )]
    public function getExecutedMetrics(): JsonResponse
    {
        $query = Expense::where('status', 'executed');

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
        summary: "Ordonner un décaissement (Exécuter ou Rejeter)",
        description: "Met à jour le statut en 'executed' ou 'rejected', enregistre l'utilisateur et les dates.",
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
                        enum: ["executed", "rejected"], 
                        example: "executed"
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
            new OA\Response(response: 200, description: "Statut mis à jour avec succès"),
            new OA\Response(response: 422, description: "Erreur de validation ou déjà traité"),
            new OA\Response(response: 404, description: "Dépense introuvable")
        ]
    )]
   public function processOrdonnancement(Request $request, $id): JsonResponse
    {
        $request->validate([
            // On autorise 'approved' en plus de 'executed' et 'rejected'
            'status' => 'required|in:executed,approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $expense = Expense::findOrFail($id);

        if (!in_array($expense->status, ['pending', 'rejected'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cette demande ne peut plus être traitée.'
            ], 422);
        }

        // Si l'application envoie 'approved', on l'enregistre comme 'executed' en base
        $inputStatus = $request->status;
        $dbStatus = ($inputStatus === 'approved') ? 'executed' : $inputStatus;

        $expense->status = $dbStatus;

        if ($dbStatus === 'executed') {
            $expense->executed_by = $request->user()?->id;
            $expense->executed_at = now();
            $expense->rejection_reason = null;
        } else {
            $expense->rejection_reason = $request->rejection_reason;
            $expense->executed_by = null;
            $expense->executed_at = null;
        }

        $expense->save();

        return response()->json([
            'status' => 'success',
            'message' => $dbStatus === 'executed' 
                ? 'Décaissement exécuté avec succès.' 
                : 'Demande de décaissement rejetée avec succès.',
            'data' => $expense
        ], 200);
    }

    #[OA\Put(
        path: "/api/admin/decaissements/{id}",
        summary: "Modifier une demande de décaissement (Uniquement si 'pending')",
        tags: ["Décaissements"],
        security: [["sanctum" => []]]
    )]
    public function update(Request $request, $id): JsonResponse
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de modifier une dépense déjà traitée ou exécutée.'
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $expense->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Demande de décaissement mise à jour avec succès.',
            'data' => $expense
        ], 200);
    }

    #[OA\Delete(
        path: "/api/admin/decaissements/{id}",
        summary: "Supprimer une demande de décaissement (Uniquement si 'pending')",
        tags: ["Décaissements"],
        security: [["sanctum" => []]]
    )]
    public function destroy($id): JsonResponse
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer une dépense déjà traitée ou exécutée.'
            ], 422);
        }

        $expense->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Demande de décaissement supprimée avec succès.'
        ], 200);
    }
}
