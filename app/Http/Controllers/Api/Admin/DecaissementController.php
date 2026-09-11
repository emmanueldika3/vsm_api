<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DecaissementController extends Controller
{
    #[OA\Post(
        path: "/api/admin/expenses/{id}/process",
        summary: "Ordonner ou Rejeter une demande de décaissement (Président)",
        description: "Permet au Président de valider (status: approved) ou de rejeter (status: rejected) une dépense en attente.",
        tags: ["Finances"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la dépense/décaissement à traiter",
                schema: new OA\Schema(type: "integer", example: 5)
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
                        example: "approved",
                        description: "Statut d'ordonnancement"
                    ),
                    new OA\Property(
                        property: "rejection_reason",
                        type: "string",
                        nullable: true,
                        example: "Facture non conforme aux justificatifs du club",
                        description: "Obligatoire si status = rejected"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Décaissement ordonné ou rejeté avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Ordre de décaissement validé avec succès."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 5),
                                new OA\Property(property: "title", type: "string", example: "Achat ballons T5"),
                                new OA\Property(property: "amount", type: "number", example: 75000),
                                new OA\Property(property: "status", type: "string", example: "approved"),
                                new OA\Property(property: "approved_by", type: "integer", example: 2),
                                new OA\Property(property: "approved_at", type: "string", format: "date-time", example: "2026-09-11T10:00:00.000000Z")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Demande déjà traitée ou validation échouée"
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié"
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé (Rôle Président requis)"
            ),
            new OA\Response(
                response: 404,
                description: "Dépense non trouvée"
            )
        ]
    )]
    public function processOrdonnancement(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $expense = Expense::findOrFail($id);

        // Vérification que la dépense est bien en attente
        if ($expense->status !== 'pending') {
            return response()->json([
                'message' => 'Cette demande a déjà été traitée.'
            ], 422);
        }

        $expense->status = $request->status;

        if ($request->status === 'approved') {
            $expense->approved_by = $request->user()->id;
            $expense->approved_at = now();
        } else {
            $expense->rejection_reason = $request->rejection_reason;
        }

        $expense->save();

        return response()->json([
            'status' => 'success',
            'message' => $request->status === 'approved' 
                ? 'Ordre de décaissement validé avec succès.' 
                : 'Demande de décaissement rejetée.',
            'data' => $expense
        ], 200);
    }
}