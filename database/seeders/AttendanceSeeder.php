<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $eventIds = Event::pluck('id')->toArray();

        if (empty($userIds) || empty($eventIds)) {
            $this->command->warn("Assurez-vous d'avoir au moins un utilisateur et un événement avant d'exécuter AttendanceSeeder.");
            return;
        }

        $statuses = ['present', 'absent', 'excused', 'late'];
        $excuses = [
            'Blessure à la cheville',
            'Voyage professionnel',
            'Empêchement familial',
            'Maladie (grippe)',
            'Retard lié au travail'
        ];

        // Pour chaque événement, on génère un enregistrement de présence pour plusieurs membres
        foreach ($eventIds as $eventId) {
            // Sélectionne un échantillon aléatoire de membres pour cet événement
            $selectedUsers = array_rand(array_flip($userIds), min(count($userIds), rand(3, 10)));
            if (!is_array($selectedUsers)) {
                $selectedUsers = [$selectedUsers];
            }

            foreach ($selectedUsers as $userId) {
                $status = $statuses[array_rand($statuses)];

                Attendance::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'event_id' => $eventId,
                    ],
                    [
                        'status' => $status,
                        'note' => in_array($status, ['excused', 'late']) ? $excuses[array_rand($excuses)] : null,
                    ]
                );
            }
        }

        $this->command->info("Présences de test insérées avec succès !");
    }
}