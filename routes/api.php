<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ContributionController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\Admin\FinanceController;
use App\Http\Controllers\Api\Admin\DecaissementController;
use App\Http\Controllers\Api\Coach\MatchTacticController;
use App\Http\Controllers\Api\Coach\MemberAttendanceController;
/*
|--------------------------------------------------------------------------
| Routes Publiques (Accessibles sans authentification)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
//routes a proteger
// Événements publics
Route::get('/users/pending', [UserController::class, 'pending']);
Route::apiResource('users', UserController::class);

Route::post('/coach/events/{eventId}/tactics/publish', [MatchTacticController::class, 'publish']);
Route::get('/events/{eventId}/tactics', [MatchTacticController::class, 'show']);
Route::get('/events/upcoming', [EventController::class, 'upcoming']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::get('/events/{id}/presences', [EventController::class, 'getEventPresences']);
Route::get('/events/{id}/presents', [EventController::class, 'getPresentPlayers']);
Route::get('/members/{id}/presences-history', [MemberAttendanceController::class, 'attendanceHistory']);

// Annonces publiques
Route::get('/announcements/latest', [AnnouncementController::class, 'latest']);
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);

//event create update delete
//  Route::post('/events', [EventController::class, 'store']);
//         Route::put('/events/{id}', [EventController::class, 'update']);
//         Route::delete('/events/{id}', [EventController::class, 'destroy']);


/*
|--------------------------------------------------------------------------
| Routes Protégées (Nécessite authentification Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // --- AUTHENTIFICATION & PROFIL UTILISATEUR ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/user/photo', [UserController::class, 'updatePhoto']);

    // --- DASHBOARDS SPÉCIFIQUES ---
    Route::prefix('dashboard')->group(function () {
        Route::get('/player', [DashboardController::class, 'playerSummary']);
        Route::get('/coach', [DashboardController::class, 'coachSummary']);
        Route::get('/treasurer', [DashboardController::class, 'treasurerSummary']);
        Route::get('/president', [DashboardController::class, 'presidentSummary']);
        Route::get('/admin', [DashboardController::class, 'adminSummary']);
    });

    // --- GESTION DES PRÉSENCES & ÉVÉNEMENTS (Membres / Coach) ---
    Route::post('/events/{id}/presence', [EventController::class, 'updatePresence']);
    Route::post('/events/{id}/presences/{userId}', [EventController::class, 'updateMemberPresence']);

    // --- FINANCES & COTISATIONS (Membre connecté) ---
    Route::get('/contributions/my-status', [ContributionController::class, 'myStatus']);
    Route::get('/contributions/my-financial-summary', [ContributionController::class, 'myFinancialSummary']);

    // --- ESPACE ADMINISTRATION (`api/admin/*`) ---
    Route::prefix('admin')->group(function () {
        // Dashboard Admin
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        // Finances & Trésorerie
        Route::prefix('finances')->group(function () {
            Route::get('/cash-balance', [FinanceController::class, 'getCashBalance']);
            Route::get('/executed-disbursements', [FinanceController::class, 'getExecutedDisbursements']);
            Route::get('/pending-disbursements', [FinanceController::class, 'getPendingDisbursements']);
            Route::get('/collected-contributions', [FinanceController::class, 'getCollectedContributions']);
        });

        // Décaissements
        Route::prefix('decaissements')->group(function () {
            Route::get('/pending', [DecaissementController::class, 'getPendingDisbursements']);
            Route::get('/metrics/pending', [DecaissementController::class, 'getPendingMetrics']);
            Route::get('/metrics/executed', [DecaissementController::class, 'getExecutedMetrics']);
            Route::post('/{id}/ordonner', [DecaissementController::class, 'processOrdonnancement']);
        });
    });

    // Alias direct hors admin pour les décaissements
    Route::prefix('decaissements')->group(function () {
        Route::post('/{id}/ordonner', [DecaissementController::class, 'processOrdonnancement']);
    });

    // --- ACCÈS RESTREINT : BUREAU & ADMIN VSM (`can:admin-access`) ---
    Route::middleware(['api','auth:sanctum',])->group(function () {
        // Gestion des utilisateurs / membres
        // Route::get('/users/pending', [UserController::class, 'pending']);
        Route::post('/users/{id}/approve', [UserController::class, 'approve']);
        Route::post('/users/{id}/reject', [UserController::class, 'reject']);
        Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
        Route::post('/users/{id}/suspend', [UserController::class, 'suspend']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);
        // Route::apiResource('users', UserController::class);

        // Gestion globale des cotisations (Trésorier/Admin)
        Route::get('/contributions', [ContributionController::class, 'index']);
        Route::post('/contributions', [ContributionController::class, 'store']);
        Route::put('/contributions/{id}', [ContributionController::class, 'update']);
        Route::get('/contributions/members-status', [ContributionController::class, 'membersStatus']);
        Route::get('/contributions/metrics', [ContributionController::class, 'metrics']);
        Route::get('/contributions/member-financial-summary/{user}', [ContributionController::class, 'memberFinancialSummary']);

        // Gestion des événements (Création, Modification, Suppression)
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);

        // coach
        // Route::post('/coach/events/{eventId}/tactics/publish', [MatchTacticController::class, 'publish']);

        // Annonces (Création/Modification/Suppression)
        Route::apiResource('announcements', AnnouncementController::class)->except(['index', 'show']);
    });
});