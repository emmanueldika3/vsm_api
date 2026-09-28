<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // 1. Supprimer les anciennes colonnes inutiles
            if (Schema::hasColumn('events', 'event_date')) {
                $table->dropColumn('event_date');
            }
            if (Schema::hasColumn('events', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('events', 'type')) {
                $table->dropColumn('type');
            }

            // 2. Ajouter les nouvelles colonnes nécessaires au modèle Flutter
            $table->string('home_team')->after('title');
            $table->string('away_team')->after('home_team');
            $table->string('venue')->after('away_team');
            $table->dateTime('event_date_time')->after('venue');
            $table->string('home_logo_url')->nullable()->after('event_date_time');
            $table->string('away_logo_url')->nullable()->after('home_logo_url');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Rollback : remettre les anciennes colonnes et supprimer les nouvelles
            $table->dropColumn([
                'home_team',
                'away_team',
                'venue',
                'event_date_time',
                'home_logo_url',
                'away_logo_url',
            ]);

            $table->dateTime('event_date')->nullable();
            $table->string('location')->nullable();
            $table->string('type', 50)->default('match');
        });
    }
};