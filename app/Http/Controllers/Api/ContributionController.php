<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ContributionController extends Controller
{
    #[OA\Get(
        path: "/contributions/my-status",
        summary: "Statut des cotisations du membre connecté",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Historique et statut individuel du joueur",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "monthly_fee", type: "integer", example: 10000),
                                new OA\Property(property: "is_up_to_date", type: "boolean", example: true),
                                new OA\Property(property: "last_payment_date", type: "string", nullable: true, example: "2026-08-01"),
                                new OA\Property(property: "balance_due", type: "integer", example: 0)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function myStatus(Request $request)
    {
        $user = $request->user();
        $monthlyFee = 10000; // FCFA

        // Calcul du total payé ce mois-ci pour la cotisation mensuelle
        $totalPaidThisMonth = Contribution::where('user_id', $user->id)
            ->where('payment_type', 'monthly_fee')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $lastPayment = Contribution::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        $balanceDue = max(0, $monthlyFee - $totalPaidThisMonth);

        return response()->json([
            'status' => 'success',
            'data' => [
                'monthly_fee' => $monthlyFee,
                'is_up_to_date' => $balanceDue === 0,
                'last_payment_date' => $lastPayment ? $lastPayment->created_at->toDateString() : null,
                'balance_due' => (int) $balanceDue,
            ]
        ]);
    }

    #[OA\Get(
        path: "/contributions",
        summary: "Liste globale des cotisations (Trésorier/Admin)",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        parameters: [
            new OA\Parameter(
                name: "month",
                in: "query",
                required: false,
                description: "Filtrer par mois (ex: 2026-08)",
                schema: new OA\Schema(type: "string", example: "2026-08")
            ),
            new OA\Parameter(
                name: "user_id",
                in: "query",
                required: false,
                description: "Filtrer par membre spécifique",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Bilan global des cotisations et transactions",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_amount", type: "integer", example: 450000),
                                new OA\Property(
                                    property: "records",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "user_name", type: "string", example: "Emmanuel Dika"),
                                            new OA\Property(property: "amount", type: "integer", example: 10000),
                                            new OA\Property(property: "payment_type", type: "string", example: "monthly_fee"),
                                            new OA\Property(property: "payment_method", type: "string", example: "orange_money"),
                                            new OA\Property(property: "created_at", type: "string", example: "2026-08-01 10:30:00")
                                        ]
                                    )
                                )
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = Contribution::with('user:id,name');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('month') && !empty($request->month)) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $query->whereYear('created_at', $parts[0])
                      ->whereMonth('created_at', $parts[1]);
            }
        }

        $records = $query->latest()->get();
        $totalAmount = $records->sum('amount');

        $formattedRecords = $records->map(function ($item) {
            return [
                'id' => $item->id,
                'user_name' => $item->user->name ?? 'Membre inconnu',
                'amount' => (int) $item->amount,
                'payment_type' => $item->payment_type,
                'payment_method' => $item->payment_method,
                'notes' => $item->notes,
                'created_at' => $item->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_amount' => (int) $totalAmount,
                'records' => $formattedRecords
            ]
        ]);
    }

    #[OA\Post(
        path: "/contributions",
        summary: "Enregistrer un paiement de cotisation (Trésorier/Admin)",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id", "amount", "payment_type", "payment_method"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 1),
                    new OA\Property(property: "amount", type: "integer", example: 10000),
                    new OA\Property(property: "payment_type", type: "string", enum: ["monthly_fee", "event_fee", "donation"], example: "monthly_fee"),
                    new OA\Property(property: "payment_method", type: "string", enum: ["cash", "orange_money", "mtn_momo"], example: "orange_money"),
                    new OA\Property(property: "notes", type: "string", example: "Cotisation du mois d'Août 2026")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Paiement enregistré avec succès"),
            new OA\Response(response: 422, description: "Erreur de validation des données")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:1000',
            'payment_type' => 'required|string|in:monthly_fee,event_fee,donation',
            'payment_method' => 'required|string|in:cash,orange_money,mtn_momo',
            'notes' => 'nullable|string',
        ]);

        $contribution = Contribution::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement de cotisation enregistré avec succès.',
            'data' => $contribution
        ], 201);
    }

    #[OA\Put(
        path: "/contributions/{id}",
        summary: "Mettre à jour un enregistrement de cotisation",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la transaction",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "amount", type: "integer", example: 15000),
                    new OA\Property(property: "payment_method", type: "string", example: "cash"),
                    new OA\Property(property: "notes", type: "string", example: "Ajustement du montant")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Transaction mise à jour"),
            new OA\Response(response: 404, description: "Transaction introuvable")
        ]
    )]
    public function update(Request $request, $id)
    {
        $contribution = Contribution::find($id);

        if (!$contribution) {
            return response()->json([
                'status' => 'error',
                'message' => 'Enregistrement de cotisation introuvable.'
            ], 404);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:1000',
            'payment_type' => 'nullable|string|in:monthly_fee,event_fee,donation',
            'payment_method' => 'nullable|string|in:cash,orange_money,mtn_momo',
            'notes' => 'nullable|string',
        ]);

        $contribution->update(array_filter($validated));

        return response()->json([
            'status' => 'success',
            'message' => 'Enregistrement de cotisation mis à jour avec succès.',
            'data' => $contribution
        ]);
    }
}