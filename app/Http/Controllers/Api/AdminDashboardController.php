<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Administration",
    description: "Endpoints réservés aux administrateurs"
)]
class AdminDashboardController extends Controller
{
    #[OA\Get(
        path: "/api/admin/dashboard",
        summary: "Consulter le tableau de bord administration",
        security: [["bearerAuth" => []]],
        tags: ["Administration"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Statistiques d'administration récupérées avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "members_overview",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "total_members", type: "integer", example: 35),
                                        new OA\Property(property: "active_members", type: "integer", example: 30),
                                        new OA\Property(property: "new_this_month", type: "integer", example: 3)
                                    ]
                                ),
                                new OA\Property(
                                    property: "financial_overview",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "total_collected", type: "integer", example: 1250000),
                                        new OA\Property(property: "collected_this_month", type: "integer", example: 150000),
                                        new OA\Property(property: "pending_this_month", type: "integer", example: 50000)
                                    ]
                                ),
                                new OA\Property(
                                    property: "events_overview",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "upcoming_events", type: "integer", example: 3),
                                        new OA\Property(property: "total_events_this_year", type: "integer", example: 24),
                                        new OA\Property(property: "average_attendance_rate", type: "string", example: "82%")
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non autorisé"),
            new OA\Response(response: 403, description: "Accès interdit - Rôle Admin requis")
        ]
    )]
    public function index(): JsonResponse
    {
        // 1. Statistiques des Membres
        $totalMembers = User::count();
        $activeMembers = User::where('is_active', true)->count();
        $newThisMonth = User::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // 2. Aperçu Financier
        $totalCollected = Contribution::sum('amount');
        $collectedThisMonth = Contribution::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        // Estimation : Cotisation mensuelle de 10 000 FCFA par membre actif
        $expectedMonthly = $activeMembers * 10000;
        $pendingThisMonth = max(0, $expectedMonthly - $collectedThisMonth);

        // 3. Aperçu des Événements & Présences
        $upcomingEvents = Event::where('event_date', '>=', now())->count();
        $totalEventsThisYear = Event::whereYear('event_date', now()->year)->count();

        $pastEventsCount = Event::where('event_date', '<', now())->count();
        $totalPresencesCount = Attendance::where('status', 'present')->count();

        $averageAttendanceRate = '100%';
        if ($pastEventsCount > 0 && $activeMembers > 0) {
            $maxPossiblePresences = $pastEventsCount * $activeMembers;
            $rate = round(($totalPresencesCount / $maxPossiblePresences) * 100);
            $averageAttendanceRate = min(100, $rate) . '%';
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'members_overview' => [
                    'total_members' => $totalMembers,
                    'active_members' => $activeMembers,
                    'new_this_month' => $newThisMonth,
                ],
                'financial_overview' => [
                    'total_collected' => (int) $totalCollected,
                    'collected_this_month' => (int) $collectedThisMonth,
                    'pending_this_month' => (int) $pendingThisMonth,
                ],
                'events_overview' => [
                    'upcoming_events' => $upcomingEvents,
                    'total_events_this_year' => $totalEventsThisYear,
                    'average_attendance_rate' => $averageAttendanceRate,
                ]
            ]
        ]);
    }
}