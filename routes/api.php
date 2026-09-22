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
use App\Models\Contribution;

/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Routes Protégées (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    
    // --- AUTHENTIFICATION & PROFIL ---
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

        // Ordonnancement des Décaissements (`api/admin/decaissements/*`)
        Route::prefix('decaissements')->group(function () {
            Route::get('/pending', [DecaissementController::class, 'getPendingDisbursements']);
            Route::get('/metrics/pending', [DecaissementController::class, 'getPendingMetrics']);
            Route::get('/metrics/executed', [DecaissementController::class, 'getExecutedMetrics']);
            Route::post('/{id}/ordonner', [DecaissementController::class, 'processOrdonnancement']);
        });
    });

    // --- ALIAS DIRECT HORS ADMIN (`api/decaissements/*`) ---
    Route::prefix('decaissements')->group(function () {
        Route::post('/{id}/ordonner', [DecaissementController::class, 'processOrdonnancement']);
    });

    // --- GESTION DES MEMBRES / USERS ---
    // Les routes spécifiques sont placées AVANT l'apiResource pour éviter les conflits d'ID
    Route::get('/users/pending', [UserController::class, 'pending']);
    Route::post('/users/{id}/approve', [UserController::class, 'approve']);
    Route::post('/users/{id}/reject', [UserController::class, 'reject']);

    // Resource CRUD pour les membres (index, show, update, destroy)
    Route::apiResource('users', UserController::class);
    Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::post('/users/{id}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{id}/activate', [UserController::class, 'activate']);

    // --- TRÉSORERIE & COTISATIONS ---
    Route::get('/contributions/my-status', [ContributionController::class, 'myStatus']);
    Route::get('/contributions/metrics', [ContributionController::class, 'metrics']);
    Route::get('/contributions/members-status', [ContributionController::class, 'membersStatus']);
    Route::get('/contributions', [ContributionController::class, 'index']);

    // --- ÉVÉNEMENTS, MATCHS & ENTRAÎNEMENTS ---
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events/{id}/presence', [EventController::class, 'updatePresence']);

    // --- ANNONCES & COMMUNIQUÉS ---
    Route::apiResource('announcements', AnnouncementController::class);

    // --- ACCÈS RESTREINT : BUREAU & ADMIN VSM ---
    Route::middleware('can:admin-access')->group(function () {
        // Cotisations
        Route::post('/contributions', [ContributionController::class, 'store']);
        Route::put('/contributions/{id}', [ContributionController::class, 'update']);

        // Programme des matchs / convocations
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);

        // Annonces
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
    });
});