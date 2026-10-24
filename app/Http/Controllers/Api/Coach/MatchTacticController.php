<?php

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\MatchTactic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class MatchTacticController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/coach/events/{eventId}/tactics/publish",
     *     summary="Publier ou mettre à jour le dispositif tactique d'un match",
     *     description="Enregistre la formation, le 11 type et calcule automatiquement les remplaçants via la table event_presences.",
     *     tags=["Coach - Tactique"],
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID de l'événement / match",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"formation", "starting_xi"},
     *             @OA\Property(property="formation", type="string", example="4-3-3", description="Schéma tactique choisi"),
     *             @OA\Property(
     *                 property="starting_xi",
     *                 type="array",
     *                 description="Liste des 11 titulaires positionnés",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="position", type="string", example="GK"),
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="number", type="string", example="1")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dispositif mis en ligne avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Dispositif tactique mis en ligne avec succès !"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation des données"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
    public function publish(Request $request, $eventId)
    {
        $validated = $request->validate([
            'formation' => 'required|string',
            'starting_xi' => 'required|array',
            'substitutes' => 'nullable|array',
        ]);

        $startingXi = $validated['starting_xi'];

        // 1. Extraire les IDs des joueurs positionnés dans le 11 de départ
        $startingXiIds = collect($startingXi)->pluck('id')->toArray();

        // 2. Récupérer tous les joueurs marqués "present" pour cet événement dans event_presences
        $presentUsers = DB::table('event_presences')
            ->join('users', 'users.id', '=', 'event_presences.user_id')
            ->where('event_presences.event_id', $eventId)
            ->where('event_presences.status', 'present')
            ->select('users.id', 'users.name', 'users.jersey_number')
            ->get();

        // 3. Filtrer les joueurs présents qui ne sont PAS dans le 11 de départ pour les mettre sur le banc
        $substitutes = [];
        foreach ($presentUsers as $user) {
            if (!in_array($user->id, $startingXiIds)) {
                $substitutes[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'number' => $user->jersey_number ?? '',
                ];
            }
        }

        // 4. Enregistrement en base de données avec la liste calculée des remplaçants
        $tactic = MatchTactic::updateOrCreate(
            ['event_id' => $eventId],
            [
                'formation' => $validated['formation'],
                'starting_xi' => $startingXi,
                'substitutes' => $substitutes,
                'is_published' => true,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Dispositif tactique mis en ligne avec succès !',
            'data' => $tactic
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{eventId}/tactics",
     *     summary="Récupérer le dispositif tactique d'un événement",
     *     tags=["Tactique"],
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="ID de l'événement / match",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dispositif tactique récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="formation", type="string", example="4-3-3"),
     *             @OA\Property(property="starting_xi", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="substitutes", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune tactique définie pour cet événement"
     *     )
     * )
     */
 public function show($eventId)
{
    $tactic = MatchTactic::where('event_id', $eventId)->first();

    if (!$tactic) {
        return response()->json(['message' => 'Aucune tactique définie pour cet événement.'], 404);
    }

    // 1. Récupérer l'utilisateur actuellement authentifié via l'token Sanctum
    $user = auth()->user();
    $myStatus = "Non identifié";

    if ($user) {
        // 2. Vérifier si l'utilisateur est dans le 11 de départ (starting_xi)
        $isStartingXi = false;
        foreach ($tactic->starting_xi as $player) {
            if (isset($player['id']) && (int)$player['id'] === (int)$user->id) {
                $isStartingXi = true;
                $position = $player['position'] ?? '';
                $myStatus = "Titulaire ($position)";
                break;
            }
        }

        // 3. S'il n'est pas titulaire, vérifier dans les remplaçants (substitutes)
        if (!$isStartingXi && is_array($tactic->substitutes)) {
            foreach ($tactic->substitutes as $sub) {
                if (isset($sub['id']) && (int)$sub['id'] === (int)$user->id) {
                    $myStatus = "Sur le banc des remplaçants";
                    break;
                }
            }
        }

        // 4. Si pas dans le 11 ni dans les remplaçants, vérifier s'il est présent à l'événement dans event_presences
        if ($myStatus === "Non identifié") {
            $presence = DB::table('event_presences')
                ->where('event_id', $eventId)
                ->where('user_id', $user->id)
                ->first();

            if ($presence && $presence->status === 'present') {
                $myStatus = "Présent (Non positionné dans le 11 initial)";
            }
        }
    }

    return response()->json([
        'formation' => $tactic->formation,
        'starting_xi' => $tactic->starting_xi,
        'substitutes' => $tactic->substitutes,
        'my_status' => $myStatus, // <--- Le statut calculé directement par le backend
        'connected_player' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'number' => $user->jersey_number ?? '',
        ] : null
    ]);
}  
}