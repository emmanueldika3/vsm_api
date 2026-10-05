<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Season;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ContributionController extends Controller
{
    #[OA\Get(
        path: "/contributions/my-financial-summary",
        summary: "Statut des cotisations du membre connecté (Dynamique selon la saison active)",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Résumé financier détaillé du membre connecté",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "season", type: "string", example: "Saison 2026-2027"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "member_name", type: "string", example: "Emmanuel Dika"),
                                new OA\Property(property: "global_status", type: "string", example: "En Règle"),
                                new OA\Property(
                                    property: "financial_summary",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "expected", type: "number", example: 27000),
                                        new OA\Property(property: "paid", type: "number", example: 27000),
                                        new OA\Property(property: "remaining", type: "number", example: 0),
                                        new OA\Property(property: "status", type: "string", example: "Solde Apuré")
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Saison active introuvable")
        ]
    )]
    public function myFinancialSummary(Request $request)
    {
        $user = $request->user();

        $activeSeason = Season::where('is_active', true)->first();
        
        $adhesionExpected = $activeSeason ? $activeSeason->adhesion_fee : 3000;
        $annualExpected = $activeSeason ? $activeSeason->annual_fee : 24000;
        $totalExpected = $adhesionExpected + $annualExpected;
        $seasonName = $activeSeason ? $activeSeason->name : 'Saison en cours';

        // Liaison avec la table contributions via user_id
        $totalPaid = Contribution::where('user_id', $user->id)->sum('amount');

        return response()->json([
            'status' => 'success',
            'season' => $seasonName,
            'data' => [
                'member_name' => $user->name,
                'global_status' => $totalPaid >= $totalExpected ? 'En Règle' : 'Incomplet',
                'financial_summary' => [
                    'expected' => (float) $totalExpected,
                    'paid' => (float) $totalPaid,
                    'remaining' => max(0, $totalExpected - $totalPaid),
                    'status' => $totalPaid >= $totalExpected ? 'Solde Apuré' : 'Partiel / Dû'
                ]
            ]
        ]);
    }

    #[OA\Get(
        path: "/contributions/member-financial-summary/{user_id}",
        summary: "Bilan financier global d'un membre (Dynamique selon la saison active)",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        parameters: [
            new OA\Parameter(
                name: "user_id",
                in: "path",
                required: true,
                description: "ID du membre",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Résumé financier détaillé du membre",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "season", type: "string", example: "Saison 2026-2027"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "member_name", type: "string", example: "Emmanuel Dika"),
                                new OA\Property(property: "global_status", type: "string", example: "En Règle"),
                                new OA\Property(
                                    property: "financial_summary",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "expected", type: "number", example: 27000),
                                        new OA\Property(property: "paid", type: "number", example: 27000),
                                        new OA\Property(property: "remaining", type: "number", example: 0),
                                        new OA\Property(property: "status", type: "string", example: "Solde Apuré")
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Membre ou saison active introuvable")
        ]
    )]
    public function memberFinancialSummary($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Membre introuvable.'], 404);
        }

        $activeSeason = Season::where('is_active', true)->first();
        
        $adhesionExpected = $activeSeason ? $activeSeason->adhesion_fee : 3000;
        $annualExpected = $activeSeason ? $activeSeason->annual_fee : 24000;
        $totalExpected = $adhesionExpected + $annualExpected;
        $seasonName = $activeSeason ? $activeSeason->name : 'Saison en cours';

        $totalPaid = Contribution::where('user_id', $user->id)->sum('amount');

        return response()->json([
            'status' => 'success',
            'season' => $seasonName,
            'data' => [
                'member_name' => $user->name,
                'global_status' => $totalPaid >= $totalExpected ? 'En Règle' : 'Incomplet',
                'financial_summary' => [
                    'expected' => (float) $totalExpected,
                    'paid' => (float) $totalPaid,
                    'remaining' => max(0, $totalExpected - $totalPaid),
                    'status' => $totalPaid >= $totalExpected ? 'Solde Apuré' : 'Partiel / Dû'
                ]
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
                'payment_method' => $item->payment_method ?? 'N/A',
                'notes' => $item->notes ?? null,
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
                required: ["user_id", "amount", "payment_method"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 1),
                    new OA\Property(property: "amount", type: "number", example: 10000),
                    new OA\Property(property: "payment_method", type: "string", example: "orange_money"),
                    new OA\Property(property: "notes", type: "string", example: "Cotisation de la saison")
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
            'amount' => 'required|numeric|min:500',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $contribution = Contribution::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement de cotisation enregistré avec succès.',
            'data' => $contribution
        ], 201);
    }
}