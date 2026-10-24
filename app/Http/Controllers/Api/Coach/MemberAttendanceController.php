<?php
namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class MemberAttendanceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/members/{id}/presences-history",
     *     summary="Récupère l'historique et les statistiques de présence d'un membre",
     *     description="Retourne le taux de présence, le nombre de participations, d'absences et l'historique détaillé des événements pour un membre spécifique.",
     *     tags={"Présences Membres"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du membre",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="attendance_rate", type="number", format="float", example=85.5),
     *                 @OA\Property(property="present_count", type="integer", example=12),
     *                 @OA\Property(property="absent_count", type="integer", example=2),
     *                 @OA\Property(
     *                     property="history",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="title", type="string", example="Match vs Les Tigres"),
     *                         @OA\Property(property="date", type="string", example="2026-10-21"),
     *                         @OA\Property(property="status", type="string", example="present"),
     *                         @OA\Property(property="type", type="string", example="Match")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
    public function attendanceHistory($id): JsonResponse
    {
        try {
            // Recherche du membre ou 404 si inexistant
            $member = User::with(['presences.event'])->find($id);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Membre non trouvé'
                ], 404);
            }

            $presences = $member->presences ?? collect();
            
            $totalEvents = $presences->count();
            $presentCount = $presences->where('status', 'present')->count();
            $absentCount = $presences->where('status', 'absent')->count();

            $attendanceRate = $totalEvents > 0 ? ($presentCount / $totalEvents) * 100 : 0.0;

            $history = $presences->map(function ($presence) {
                $event = $presence->event;
                
                $dateString = '';
                if ($event && isset($event->date)) {
                    $dateString = is_string($event->date) ? $event->date : $event->date->format('Y-m-d');
                }

                return [
                    'title' => $event ? ($event->title ?? 'Événement sans titre') : 'Événement inconnu',
                    'date' => $dateString,
                    'status' => $presence->status ?? 'pending',
                    'type' => $event ? ($event->type ?? 'Match / Entraînement') : 'Activité',
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance_rate' => round($attendanceRate, 1),
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                    'history' => $history,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur interne: ' . $e->getMessage()
            ], 500);
        }
    }
}