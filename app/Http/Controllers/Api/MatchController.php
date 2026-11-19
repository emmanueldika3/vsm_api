<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\Season;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MatchController extends Controller
{
    #[OA\Get(
        path: "/matches/annual-summary",
        summary: "Liste de tous les matchs/entraînements de la saison active avec bilan",
        security: [["bearerAuth" => []]],
        tags: ["Matchs & Entraînements"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Bilan et liste des matchs de la saison active",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "season", type: "string", example: "Saison 2026-2027"),
                        new OA\Property(
                            property: "summary",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_played", type: "integer", example: 10),
                                new OA\Property(property: "won", type: "integer", example: 6),
                                new OA\Property(property: "lost", type: "integer", example: 2),
                                new OA\Property(property: "drawn", type: "integer", example: 2),
                            ]
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(type: "object")
                        )
                    ]
                )
            )
        ]
    )]
   public function annualSummary()
    {
        try {
            $activeSeason = Season::where('is_active', true)->first();
            $seasonName = $activeSeason ? $activeSeason->name : 'Saison en cours';

            $query = MatchModel::query();
            if ($activeSeason) {
                $query->where('season_id', $activeSeason->id);
            }
            
            $matches = $query->orderBy('match_date', 'desc')->get();

            $won = 0;
            $lost = 0;
            $drawn = 0;

            $formattedMatches = $matches->map(function($match) use (&$won, &$lost, &$drawn) {
                $type = $match->type ?? 'match'; 
                $vsmScore = (int) ($match->vsm_score ?? 0);
                $opponentScore = (int) ($match->opponent_score ?? 0);

                // Calcul systématique du résultat (Matchs officiels ET Entraînements du dimanche)
                if ($vsmScore > $opponentScore) {
                    $result = 'won';
                    $won++;
                } elseif ($vsmScore < $opponentScore) {
                    $result = 'lost';
                    $lost++;
                } else {
                    $result = 'drawn';
                    $drawn++;
                }

                // Gestion sécurisée de la date
                $matchDate = $match->match_date;
                if ($matchDate && is_object($matchDate) && method_exists($matchDate, 'toDateTimeString')) {
                    $matchDate = $matchDate->toDateTimeString();
                }

                return [
                    'id' => $match->id,
                    'type' => $type,
                    'opponent' => $match->opponent ?? ($type === 'training' ? 'Match interne VSM' : 'Adversaire'),
                    'match_date' => $matchDate ?? (string) $match->match_date,
                    'vsm_score' => $vsmScore,
                    'opponent_score' => $opponentScore,
                    'result' => $result,
                    'location' => $match->location ?? 'Stade Yoro (PK11)',
                ];
            });

            return response()->json([
                'status' => 'success',
                'season' => $seasonName,
                'summary' => [
                    // Compte toutes les rencontres (matchs + entraînements)
                    'total_played' => $matches->count(),
                    'won' => $won,
                    'lost' => $lost,
                    'drawn' => $drawn,
                ],
                'data' => $formattedMatches
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        // 1. Vérification stricte des rôles (Président ou Secrétaire uniquement)
        $user = $request->user();
        $role = strtolower(trim($user->role ?? ''));

        $allowedRoles = ['president', 'président', 'secretaire', 'secrétaire', 'secretary', 'admin'];
        $hasAccess = false;
        foreach ($allowedRoles as $allowed) {
            if (str_contains($role, $allowed)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. Seuls le Président et le Secrétaire peuvent enregistrer un score.'
            ], 403);
        }

        // 2. Validation des données reçues
        $validated = $request->validate([
            'type' => 'required|in:match,training',
            'opponent' => 'nullable|string|max:255',
            'match_date' => 'required|date',
            'vsm_score' => 'required|integer|min:0',
            'opponent_score' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            // 3. Rattachement automatique à la saison active
            $activeSeason = Season::where('is_active', true)->first();

            $match = MatchModel::create([
                'season_id' => $activeSeason ? $activeSeason->id : null,
                'type' => $validated['type'],
                'opponent' => $validated['type'] === 'training' 
                    ? ($validated['opponent'] ?? 'Match interne VSM') 
                    : ($validated['opponent'] ?? 'Adversaire'),
                'match_date' => $validated['match_date'],
                'vsm_score' => $validated['vsm_score'],
                'opponent_score' => $validated['opponent_score'],
                'location' => $validated['location'] ?? 'Stade Yoro (PK11)',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Score enregistré avec succès !',
                'data' => $match
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()
            ], 500);
        }
    }
}