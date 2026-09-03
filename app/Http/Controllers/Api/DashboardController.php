<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: "/dashboard/player",
        summary: "Données du Dashboard Joueur",
        security: [["bearerAuth" => []]],
        tags: ["Dashboards"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Prochains matchs et état des cotisations du joueur",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "next_event",
                                    type: "object",
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "title", type: "string", example: "Match amical vs Bonamoussadi"),
                                        new OA\Property(property: "type", type: "string", example: "match"),
                                        new OA\Property(property: "event_date", type: "string", example: "2026-08-25 07:00:00"),
                                        new OA\Property(property: "location", type: "string", example: "Stade Camrail")
                                    ]
                                ),
                                new OA\Property(property: "attendance_rate", type: "string", example: "85%"),
                                new OA\Property(property: "contribution_status", type: "string", example: "Up to date"),
                                new OA\Property(property: "balance_due", type: "integer", example: 0)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function playerSummary(Request $request): JsonResponse
    {
        $user = $request->user();

        $nextEvent = Event::where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->first();

        $totalEvents = Event::where('event_date', '<=', now())->count();
        
        $attendedEvents = Attendance::where('user_id', $user?->id)
            ->whereHas('event', function ($query) {
                $query->where('event_date', '<=', now());
            })
            ->where('status', 'present')
            ->count();

        $attendanceRate = $totalEvents > 0 
            ? round(($attendedEvents / $totalEvents) * 100) . '%' 
            : '100%';

        $monthlyFee = 10000;
        $totalPaidThisMonth = Contribution::where('user_id', $user?->id)
            ->where('payment_type', 'monthly_fee')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $balanceDue = max(0, $monthlyFee - $totalPaidThisMonth);
        $contributionStatus = $balanceDue === 0 ? 'Up to date' : 'Late';

        return response()->json([
            'status' => 'success',
            'data' => [
                'next_event' => $nextEvent ? [
                    'id' => $nextEvent->id,
                    'title' => $nextEvent->title,
                    'type' => $nextEvent->type,
                    'event_date' => is_string($nextEvent->event_date) ? $nextEvent->event_date : $nextEvent->event_date?->toDateTimeString(),
                    'location' => $nextEvent->location,
                ] : null,
                'attendance_rate' => $attendanceRate,
                'contribution_status' => $contributionStatus,
                'balance_due' => (int) $balanceDue,
            ]
        ]);
    }

    #[OA\Get(
        path: "/dashboard/coach",
        summary: "Données du Dashboard Coach",
        security: [["bearerAuth" => []]],
        tags: ["Dashboards"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Effectif présent et convocations",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_players", type: "integer", example: 24),
                                new OA\Property(property: "confirmed_presences", type: "integer", example: 18),
                                new OA\Property(property: "next_session", type: "string", example: "Entraînement - 2026-08-23 06:00")
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function coachSummary(): JsonResponse
    {
        $totalPlayers = User::where(function ($query) {
            $query->where('role', 'player')
                  ->orWhereNull('role');
        })->count();

        $nextSession = Event::where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->first();

        $confirmedPresences = 0;
        if ($nextSession) {
            $confirmedPresences = Attendance::where('event_id', $nextSession->id)
                ->where('status', 'present')
                ->count();
        }

        $nextSessionFormatted = 'Aucune séance programmée';
        if ($nextSession) {
            $dateStr = is_string($nextSession->event_date) ? $nextSession->event_date : $nextSession->event_date?->format('Y-m-d H:i');
            $nextSessionFormatted = "{$nextSession->title} - {$dateStr}";
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_players' => $totalPlayers,
                'confirmed_presences' => $confirmedPresences,
                'next_session' => $nextSessionFormatted,
            ]
        ]);
    }

    #[OA\Get(
        path: "/dashboard/treasurer",
        summary: "Données du Dashboard Trésorier",
        security: [["bearerAuth" => []]],
        tags: ["Dashboards"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Bilan financier du club",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_collected", type: "integer", example: 450000),
                                new OA\Property(property: "pending_payments", type: "integer", example: 120000),
                                new OA\Property(property: "active_contributors", type: "integer", example: 20)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function treasurerSummary(): JsonResponse
    {
        $totalCollected = Contribution::sum('amount');

        $activeContributorsCount = Contribution::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->distinct()
            ->count('user_id');

        $totalMembers = User::count();
        $expectedMonthlyTotal = $totalMembers * 10000;

        $collectedThisMonth = Contribution::where('payment_type', 'monthly_fee')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $pendingPayments = max(0, $expectedMonthlyTotal - $collectedThisMonth);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_collected' => (int) $totalCollected,
                'pending_payments' => (int) $pendingPayments,
                'active_contributors' => $activeContributorsCount,
            ]
        ]);
    }

    #[OA\Get(
        path: "/dashboard/admin",
        summary: "Données du Dashboard Admin / Président",
        security: [["bearerAuth" => []]],
        tags: ["Dashboards"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vue globale du club",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_members", type: "integer", example: 30),
                                new OA\Property(property: "active_events", type: "integer", example: 2),
                                new OA\Property(property: "fund_balance", type: "integer", example: 450000)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function adminSummary(): JsonResponse
    {
        $totalMembers = User::count();
        $activeEvents = Event::where('event_date', '>=', now())->count();
        $fundBalance = Contribution::sum('amount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_members' => $totalMembers,
                'active_events' => $activeEvents,
                'fund_balance' => (int) $fundBalance,
            ]
        ]);
    }
}