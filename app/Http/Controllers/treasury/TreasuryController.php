<?php

namespace App\Http\Controllers\treasury;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Season;
use App\Models\Contribution;
use OpenApi\Attributes as OA;

class TreasuryController extends Controller
{
    /**
     * Récupère le résumé financier personnel du membre connecté pour la saison active.
     */
    #[OA\Get(
        path: "/api/contributions/my-financial-summary",
        summary: "Obtenir le résumé financier personnel du membre connecté",
        tags: ["Trésorerie"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Résumé financier récupéré avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "season", type: "string", example: "Saison 2026-2027"),
                        new OA\Property(property: "adhesion_fee", type: "number", format: "float", example: 10000.00),
                        new OA\Property(property: "renewal_fee", type: "number", format: "float", example: 5000.00),
                        new OA\Property(property: "annual_fee", type: "number", format: "float", example: 50000.00),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "member_name", type: "string", example: "Emmanuel Dika"),
                                new OA\Property(property: "global_status", type: "string", example: "En Règle"),
                                new OA\Property(
                                    property: "adhesion",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "expected", type: "number", format: "float", example: 10000.00),
                                        new OA\Property(property: "paid", type: "number", format: "float", example: 10000.00),
                                        new OA\Property(property: "remaining", type: "number", format: "float", example: 0.00),
                                        new OA\Property(property: "status", type: "string", example: "Payé")
                                    ]
                                ),
                                new OA\Property(
                                    property: "annual_fee",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "expected", type: "number", format: "float", example: 50000.00),
                                        new OA\Property(property: "paid", type: "number", format: "float", example: 17000.00),
                                        new OA\Property(property: "remaining", type: "number", format: "float", example: 33000.00),
                                        new OA\Property(property: "status", type: "string", example: "Partiel")
                                    ]
                                ),
                                new OA\Property(
                                    property: "events_participation",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "total_paid", type: "number", format: "float", example: 15000.00),
                                        new OA\Property(
                                            property: "recap",
                                            type: "array",
                                            items: new OA\Items(
                                                type: "object",
                                                properties: [
                                                    new OA\Property(property: "title", type: "string", example: "Match de Gala vs FC Lions"),
                                                    new OA\Property(property: "amount_paid", type: "number", format: "float", example: 5000.00),
                                                    new OA\Property(property: "date", type: "string", example: "2026-03-12")
                                                ]
                                            )
                                        )
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Aucune saison active trouvée")
        ]
    )]
    public function getMyFinancialSummary(Request $request)
    {
        $user =$request->user();

        // 1. Récupérer la saison sportive active
        $activeSeason = Season::where('is_active', true)->first();

        if (!$activeSeason) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucune saison active trouvée.'
            ], 404);
        }

        // 2. Récupérer toutes les contributions du membre pour cette saison
        $contributions = Contribution::where('user_id',$user->id)
            ->where('season_id', $activeSeason->id)
            ->get();

        // 3. Adhésion / Réinscription : Expected fixe de la table Season
        $adhesionExpected = (float)$activeSeason->adhesion_fee;
        $adhesionPaid = (float) $contributions->whereIn('type', ['adhesion', 'renewal'])->sum('amount_paid');$adhesionRemaining = max(0, $adhesionExpected -$adhesionPaid);

        $adhesionStatus = 'Impayé';
        if ($adhesionRemaining <= 0 && $adhesionExpected > 0) {$adhesionStatus = 'Payé';
        } elseif ($adhesionPaid > 0) {$adhesionStatus = 'Partiel';
        }

        // 4. Cotisation Annuelle : Expected fixe de la table Season
        $annualExpected = (float)$activeSeason->annual_fee;
        $annualPaid = (float) $contributions->where('type', 'annual')->sum('amount_paid');$annualRemaining = max(0, $annualExpected -$annualPaid);

        $annualStatus = 'Impayé';
        if ($annualRemaining <= 0 && $annualExpected > 0) {$annualStatus = 'Soldé';
        } elseif ($annualPaid > 0) {$annualStatus = 'Partiel';
        }

        // 5. Participations, Matchs, Sanctions & Autres (Cumul et récapitulatif)
        $eventContribs =$contributions->whereIn('type', ['match', 'sanction', 'other']);
        $eventTotalPaid = (float)$eventContribs->sum('amount_paid');

        $eventRecap = $eventContribs->map(function ($contrib) {
            return [
                'title' => $contrib->title ?? $contrib->description ?? ucfirst($contrib->type),
                'amount_paid' => (float) $contrib->amount_paid,
                'date' => $contrib->created_at ? $contrib->created_at->format('Y-m-d') : null,
            ];
        })->values();

        // 6. Statut global
        $isUpToDate = ($adhesionRemaining <= 0) && ($annualRemaining <= 0);
        $globalStatus =$isUpToDate ? 'En Règle' : 'Incomplet';

        // 7. Réponse JSON finale
        return response()->json([
            'status' => 'success',
            'season' => $activeSeason->name,
            'adhesion_fee' => (float) $activeSeason->adhesion_fee,
            'renewal_fee' => (float) $activeSeason->renewal_fee,
            'annual_fee' => (float) $activeSeason->annual_fee,
            'data' => [
                'member_name' => $user->name,
                'global_status' => $globalStatus,
                'adhesion' => [
                    'expected' => $adhesionExpected,
                    'paid' => $adhesionPaid,
                    'remaining' => $adhesionRemaining,
                    'status' => $adhesionStatus,
                ],
                'annual_fee' => [
                    'expected' => $annualExpected,
                    'paid' => $annualPaid,
                    'remaining' => $annualRemaining,
                    'status' => $annualStatus,
                ],
                'events_participation' => [
                    'total_paid' => $eventTotalPaid,
                    'recap' => $eventRecap,
                ],
            ]
        ], 200);
    }
}