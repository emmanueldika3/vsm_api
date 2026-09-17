<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\User;
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

    #[OA\Get(
        path: "/contributions/members-status",
        summary: "État des cotisations et impayés par membre (Trésorier/Admin)",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        parameters: [
            new OA\Parameter(
                name: "month",
                in: "query",
                required: false,
                description: "Mois de référence (ex: 2026-09)",
                schema: new OA\Schema(type: "string", example: "2026-09")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des membres avec leur statut de paiement et arriérés",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Emmanuel Dika"),
                                    new OA\Property(property: "total_expected", type: "number", example: 10000),
                                    new OA\Property(property: "total_paid", type: "number", example: 10000),
                                    new OA\Property(property: "balance_due", type: "number", example: 0),
                                    new OA\Property(property: "payment_status", type: "string", example: "up_to_date")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
   public function membersStatus(Request $request)
    {
        $monthlyFee = 10000; // Montant théorique attendu par mois
        $targetYear = now()->year;
        $targetMonth = now()->month;

        if ($request->has('month') && !empty($request->month)) {
            $parts = explode('-', $request->month);
            if (count($parts) === 2) {
                $targetYear = (int) $parts[0];
                $targetMonth = (int) $parts[1];
            }
        }

        $users = User::all()->map(function ($user) use ($monthlyFee, $targetYear, $targetMonth) {
            $totalPaid = Contribution::where('user_id', $user->id)
                ->where('payment_type', 'monthly_fee')
                ->whereYear('created_at', $targetYear)
                ->whereMonth('created_at', $targetMonth)
                ->sum('amount');

            $balanceDue = max(0, $monthlyFee - $totalPaid);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_expected' => (int) $monthlyFee,
                'total_paid' => (int) $totalPaid,
                'balance_due' => (int) $balanceDue,
                'payment_status' => $balanceDue === 0 ? 'up_to_date' : 'late',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $users,
            'summary' => [
                'up_to_date_count' => $users->where('payment_status', 'up_to_date')->count(),
                'late_count' => $users->where('payment_status', 'late')->count(),
            ]
        ]);
    }

    #[OA\Get(
        path: "/contributions/metrics",
        summary: "Taux de recouvrement et indicateurs financiers globaux",
        security: [["bearerAuth" => []]],
        tags: ["Trésorerie"],
        parameters: [
            new OA\Parameter(
                name: "period",
                in: "query",
                required: false,
                description: "Période d'évaluation (month ou year)",
                schema: new OA\Schema(type: "string", enum: ["month", "year"], example: "month")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Indicateurs de performance de la collecte",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_expected", type: "number", example: 200000),
                                new OA\Property(property: "total_collected", type: "number", example: 150000),
                                new OA\Property(property: "total_unpaid", type: "number", example: 50000),
                                new OA\Property(property: "recovery_rate", type: "number", example: 75.0)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function metrics(Request $request)
    {
        $period = $request->query('period', 'month');
        $monthlyFee = 10000;
        $totalMembersCount = User::count();

        $totalExpected = $period === 'year' 
            ? ($monthlyFee * 12 * $totalMembersCount) 
            : ($monthlyFee * $totalMembersCount);

        $query = Contribution::where('payment_type', 'monthly_fee');
        if ($period === 'year') {
            $query->whereYear('created_at', now()->year);
        } else {
            $query->whereYear('created_at', now()->year)
                  ->whereMonth('created_at', now()->month);
        }

        $totalCollected = (float) $query->sum('amount');
        $totalUnpaid = max(0, $totalExpected - $totalCollected);
        $recoveryRate = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => $period,
                'total_expected' => (int) $totalExpected,
                'total_collected' => (int) $totalCollected,
                'total_unpaid' => (int) $totalUnpaid,
                'recovery_rate' => $recoveryRate,
            ]
        ]);
    }
}