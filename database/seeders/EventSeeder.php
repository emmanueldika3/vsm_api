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
        // 1. Prochain Entraînement Tactique (Créé par l'utilisateur ID 15)
        Event::create([
            'title' => 'Entraînement Tactique & Placement',
            'type' => 'training',
            'home_team' => 'VSM FC',
            'away_team' => 'Équipe B',
            'venue' => 'Stade PK11, Douala',
            'event_date_time' => Carbon::now()->addDays(2)->setHour(17)->setMinute(0)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Mise en place tactique, exercices de relance et duels sous pression.',
            'status' => 'upcoming',
            'created_by' => 15,
        ]);

        // 2. Prochain Match de Championnat (Créé par l'utilisateur ID 16)
        Event::create([
            'title' => 'VSM FC vs FC Akwa',
            'type' => 'match',
            'home_team' => 'VSM FC',
            'away_team' => 'FC Akwa',
            'venue' => 'Stade Municipal de Bonamoussadi, Douala',
            'event_date_time' => Carbon::now()->addDays(4)->setHour(15)->setMinute(30)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Match officiel de championnat vétéran. Rassemblement des joueurs 1h30 avant le coup d\'envoi.',
            'status' => 'upcoming',
            'created_by' => 16,
        ]);

        // 3. Séance de Récupération & Étirements (Créé par l'utilisateur ID 15)
        Event::create([
            'title' => 'Séance de Récupération Post-Match',
            'type' => 'training',
            'home_team' => 'VSM FC',
            'away_team' => 'Équipe A',
            'venue' => 'Parcours Vita, Douala',
            'event_date_time' => Carbon::now()->addDays(7)->setHour(7)->setMinute(30)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Footing léger, bains froids et étirements pour évacuer les toxines.',
            'status' => 'upcoming',
            'created_by' => 15,
        ]);

        // 4. Match Amical de Gala (Créé par l'utilisateur ID 17)
        Event::create([
            'title' => 'Gala Amical : VSM FC vs Lions Vétérans',
            'type' => 'match',
            'home_team' => 'VSM FC',
            'away_team' => 'Lions Vétérans',
            'venue' => 'Annexe Stade de la Réunification, Douala',
            'event_date_time' => Carbon::now()->addDays(12)->setHour(16)->setMinute(0)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Match de gala amical suivi d\'une collation fraternelle au siège du club.',
            'status' => 'upcoming',
            'created_by' => 17,
        ]);

        // 5. Réunion Mensuelle du Bureau (Créé par l'utilisateur ID 15)
        Event::create([
            'title' => 'Réunion Mensuelle du Bureau Exécutif',
            'type' => 'meeting',
            'home_team' => 'VSM FC',
            'away_team' => 'Bureau VSM',
            'venue' => 'Siège VSM FC, Akwa, Douala',
            'event_date_time' => Carbon::now()->addDays(15)->setHour(19)->setMinute(0)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Bilan financier de la saison, cotisations et préparation des prochaines échéances.',
            'status' => 'upcoming',
            'created_by' => 15,
        ]);

        // 6. Événement Passé (Créé par l'utilisateur ID 16)
        Event::create([
            'title' => 'Match Passé vs Leopard FC',
            'type' => 'match',
            'home_team' => 'VSM FC',
            'away_team' => 'Leopard FC',
            'venue' => 'Stade PK11, Douala',
            'event_date_time' => Carbon::now()->subDays(5)->setHour(15)->setMinute(30)->setSecond(0),
            'home_logo_url' => null,
            'away_logo_url' => null,
            'description' => 'Victoire 2-1 de VSM FC. Buteurs : Capitaine et Emmanuel.',
            'status' => 'completed',
            'created_by' => 16,
        ]);
    }
}