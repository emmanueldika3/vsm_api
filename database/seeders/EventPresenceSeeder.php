<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventPresence;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventPresenceSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère tous les utilisateurs et tous les événements
        $users = User::all();
        $events = Event::all();

        if ($users->isEmpty() || $events->isEmpty()) {
            $this->command->info('Veuillez d\'abord exécuter les seeders pour les utilisateurs et les événements.');
            return;
        }

        $statuses = ['present', 'present', 'present', 'absent', 'uncertain']; // Plus de chances d'être 'present'

        foreach ($events as $event) {
            foreach ($users as $user) {
                // Attribue un statut aléatoire pour chaque couple (événement, utilisateur)
                $randomStatus = $statuses[array_rand($statuses)];

                EventPresence::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'status' => $randomStatus,
                    ]
                );
            }
        }

        $this->command->info('Les présences aux événements ont été générées avec succès !');
    }
}