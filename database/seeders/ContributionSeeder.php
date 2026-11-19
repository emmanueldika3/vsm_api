<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\Contribution;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class ContributionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupérer ou créer la saison active
        $activeSeason = Season::firstOrCreate(
            ['is_active' => true],
            [
                'name' => 'Saison 2026-2027',
                'adhesion_fee' => 15000.00,
                'renewal_fee' => 10000.00,
                'annual_fee' => 50000.00,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
            ]
        );

        // 2. Récupérer les IDs réels des utilisateurs entre 1 et 34 s'ils existent
        $userIds = User::whereBetween('id', [1, 34])->pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->warn("Aucun utilisateur trouvé entre l'ID 1 et 34 !");
            return;
        }

        $paymentMethods = ['cash', 'mobile_money'];

        // 3. Pour chaque utilisateur trouvé, générer ses obligations de la saison active
        foreach ($userIds as $userId) {
            $createdAt = Carbon::now()->subDays(rand(1, 60));

            // A. Création de la contribution d'Adhésion / Réinscription
            $adhesionAmount = $activeSeason->renewal_fee;
            $adhesionContrib = Contribution::create([
                'user_id' => $userId,
                'season_id' => $activeSeason->id,
                'type' => 'renewal',
                'title' => 'Frais de Réinscription ' . $activeSeason->name,
                'amount_expected' => $adhesionAmount,
                'amount_paid' => 0.00, // Calculé automatiquement par les transactions
                'status' => 'unpaid',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Simuler un paiement (payé ou non)
            if (rand(0, 1) === 1) {
                Transaction::create([
                    'contribution_id' => $adhesionContrib->id,
                    'user_id' => $userId,
                    'amount' => $adhesionAmount,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference' => 'REF-ADH-' . rand(1000, 9999),
                    'executed_by' => $userId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            // B. Création de la contribution Annuelle
            $annualAmount = $activeSeason->annual_fee;
            $annualContrib = Contribution::create([
                'user_id' => $userId,
                'season_id' => $activeSeason->id,
                'type' => 'annual',
                'title' => 'Cotisation Annuelle ' . $activeSeason->name,
                'amount_expected' => $annualAmount,
                'amount_paid' => 0.00, // Calculé automatiquement par les transactions
                'status' => 'unpaid',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Simuler des paiements en tranches aléatoires
            $randomChoice = rand(1, 3);
            if ($randomChoice === 2) {
                // Paiement partiel (tranche de 20 000 FCFA)
                Transaction::create([
                    'contribution_id' => $annualContrib->id,
                    'user_id' => $userId,
                    'amount' => 20000.00,
                    'payment_method' => 'mobile_money',
                    'reference' => 'MOMO-TRQ-' . rand(1000, 9999),
                    'executed_by' => $userId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            } elseif ($randomChoice === 3) {
                // Paiement total
                Transaction::create([
                    'contribution_id' => $annualContrib->id,
                    'user_id' => $userId,
                    'amount' => $annualAmount,
                    'payment_method' => 'cash',
                    'reference' => 'CASH-TOT-' . rand(1000, 9999),
                    'executed_by' => $userId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->command->info("Saison active, contributions et tranches générées avec succès pour les membres 1 à 34 !");
    }
}