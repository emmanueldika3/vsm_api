<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // --- DÉFINITION DE LA GATE AVEC 'president' ---
      Gate::define('admin-access', function ($user) {
    // Si $user est null, ça affichera 'AUCUN USER' dans tes logs Laravel
    if (!$user) {
        \Illuminate\Support\Facades\Log::error('GATE ERROR: Utilisateur non authentifié (null)');
        return false;
    }

    // Affiche le rôle exact reçu en base de données dans le fichier storage/logs/laravel.log
    \Illuminate\Support\Facades\Log::info('GATE CHECK - User ID: ' . $user->id . ' | Role reçu: ' . var_export($user->role, true));

    return in_array($user->role, ['admin', 'coach', 'president']);
});
    }
}