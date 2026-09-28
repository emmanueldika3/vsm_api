<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Prochain Entraînement
        Event::create([
            'title' => 'Entraînement Tactique',
            'type' => 'training',
            'home_team' => 'VSM FC',
            'away_team' => 'Equipe B',
            'venue' => 'Stade PK11, Douala',
            'event_date_time' => Carbon::now()->addDays(2)->setHour(17)->setMinute(0)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Préparation physique et mise en place tactique avant le match de championnat.',
            'status' => 'upcoming',
            'created_by' => 1,
        ]);

        // 2. Prochain Match Amical
        Event::create([
            'title' => 'Match Amical vs FC Akwa',
            'type' => 'match',
            'home_team' => 'VSM FC',
            'away_team' => 'FC Akwa',
            'venue' => 'Stade Municipal de Bonamoussadi, Douala',
            'event_date_time' => Carbon::now()->addDays(5)->setHour(15)->setMinute(30)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Match amical de préparation. Présence obligatoire à 14h30.',
            'status' => 'upcoming',
            'created_by' => 1,
        ]);

        // 3. Séance de Récupération
        Event::create([
            'title' => 'Séance de Récupération',
            'type' => 'training',
            'home_team' => 'VSM FC',
            'away_team' => 'Equipe A',
            'venue' => 'Parcours Vita, Douala',
            'event_date_time' => Carbon::now()->addDays(8)->setHour(7)->setMinute(30)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Décrassage et étirements suite au match du week-end.',
            'status' => 'upcoming',
            'created_by' => 1,
        ]);

        // 4. Événement Terminé (Historique)
        Event::create([
            'title' => 'Entraînement Physique',
            'type' => 'training',
            'home_team' => 'VSM FC',
            'away_team' => 'Equipe A',
            'venue' => 'Stade PK11, Douala',
            'event_date_time' => Carbon::now()->subDays(3)->setHour(18)->setMinute(0)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Séance axée sur l\'endurance et le renforcement musculaire.',
            'status' => 'completed',
            'created_by' => 1,
        ]);
    }
}