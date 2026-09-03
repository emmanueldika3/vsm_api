<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un administrateur ou un utilisateur par défaut pour 'created_by'
        $adminUser = User::first();
        $createdBy = $adminUser ? $adminUser->id : null;

        $events = [
            [
                'title' => 'Entraînement Hebdomadaire VSM',
                'description' => 'Séance d\'entraînement physique et tactique pour l\'équipe vétéran au Stade de PK11.',
                'location' => 'Stade Municipal de PK11, Douala',
                'event_date' => Carbon::now()->addDays(2)->setHour(16)->setMinute(0),
                'type' => 'entrainement',
                'status' => 'upcoming',
                'created_by' => $createdBy,
            ],
            [
                'title' => 'Match Amical : VSM PK11 vs Vétérans Bassa',
                'description' => 'Match amical inter-quartiers suivi d\'une séance de convivialité.',
                'location' => 'Stade de Japoma (Annexe), Douala',
                'event_date' => Carbon::now()->addDays(5)->setHour(15)->setMinute(30),
                'type' => 'match',
                'status' => 'upcoming',
                'created_by' => $createdBy,
            ],
            [
                'title' => 'Réunion Mensuelle du Bureau VSM',
                'description' => 'Évaluation des cotisations, organisation du prochain tournoi et bilans financiers.',
                'location' => 'Foyer VSM, PK11',
                'event_date' => Carbon::now()->addDays(10)->setHour(18)->setMinute(0),
                'type' => 'reunion',
                'status' => 'upcoming',
                'created_by' => $createdBy,
            ],
            [
                'title' => 'Match de Championnat : VSM vs Bonabéri Old Stars',
                'description' => '3ème journée du championnat Vétérans du Littoral.',
                'location' => 'Stade de PK11, Douala',
                'event_date' => Carbon::now()->addDays(15)->setHour(16)->setMinute(0),
                'type' => 'match',
                'status' => 'upcoming',
                'created_by' => $createdBy,
            ],
            [
                'title' => 'Dernier Match Amical (Passé)',
                'description' => 'Victoire 3-1 de VSM PK11.',
                'location' => 'Stade de PK11, Douala',
                'event_date' => Carbon::now()->subDays(7)->setHour(16)->setMinute(0),
                'type' => 'match',
                'status' => 'completed',
                'created_by' => $createdBy,
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        $this->command->info("Événements de test créés avec succès pour VSM PK11 !");
    }
}