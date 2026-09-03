<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contribution;
use App\Models\User;
use Carbon\Carbon;

class ContributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les identifiants de tous les membres
        $userIds = User::pluck('id')->toArray();

        // Si aucun membre n'existe, on stoppe pour éviter les erreurs de clé étrangère
        if (empty($userIds)) {
            $this->command->warn("Aucun membre trouvé dans la table 'users'. Veuillez créer des utilisateurs avant de lancer ce seeder.");
            return;
        }

        $types = ['cotisation', 'mensualite', 'amende', 'don'];
        $statuses = ['paid', 'pending', 'late'];

        // Insertion de 20 cotisations de test
        for ($i = 0; $i < 20; $i++) {
            $status = $statuses[array_rand($statuses)];
            $amount = rand(2, 20) * 1000; // Montants entre 2 000 XAF et 20 000 XAF
            $createdAt = Carbon::now()->subDays(rand(1, 60));

            Contribution::create([
                'user_id' => $userIds[array_rand($userIds)],
                'amount' => $amount,
                'type' => $types[array_rand($types)],
                'status' => $status,
                'paid_at' => $status === 'paid' ? Carbon::now()->subDays(rand(1, 30)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info("20 cotisations de test ont été insérées avec succès !");
    }
}