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
        path: "/api/decaissements/{id}/ordonner",
        summary: "Ordonner un décaissement (Réservé au Président)",
        description: "Permet au Président de valider (approved) ou rejeter (rejected) une demande de dépense.",
        tags: ["Décaissements"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la dépense/décaissement",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["approved", "rejected"], example: "approved"),
                    new OA\Property(property: "rejection_reason", type: "string", nullable: true, example: "Budget insuffisant")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Action enregistrée avec succès"),
            new OA\Response(response: 422, description: "Demande déjà traitée"),
            new OA\Response(response: 403, description: "Accès refusé")
        ]
    )]
    public function ordonner(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $expense = Expense::findOrFail($id);

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
                ? 'Décaissement ordonné avec succès.' 
                : 'Demande de décaissement rejetée.',
            'data' => $expense
        ], 200);
    }
}