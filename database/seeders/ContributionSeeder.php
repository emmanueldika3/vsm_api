<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contribution;
use App\Models\User;
use Carbon\Carbon;

class ContributionSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère dynamiquement les IDs réels des utilisateurs entre 15 et 39 s'ils existent
        $userIds = User::whereBetween('id', [15, 39])->pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->warn("Aucun utilisateur trouvé entre l'ID 15 et 39 !");
            return;
        }

        $types = ['cotisation', 'mensualite', 'amende', 'don', 'match'];
        $statuses = ['paid', 'pending', 'late'];

        for ($i = 0; $i < 20; $i++) {
            $status = $statuses[array_rand($statuses)];
            $amount = rand(2, 20) * 1000;
            $createdAt = Carbon::now()->subDays(rand(1, 60));

            Contribution::create([
                'user_id' => $userIds[array_rand($userIds)], // Tire un ID existant parmi les vrais
                'amount' => $amount,
                'type' => $types[array_rand($types)],
                'status' => $status,
                'paid_at' => $status === 'paid' ? Carbon::now()->subDays(rand(1, 30)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info("Cotisations insérées avec succès avec de vrais user_id !");
    }
}