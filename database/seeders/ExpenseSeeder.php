<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : null;

        $expenses = [
            // Décaissements EXÉCUTÉS (comptabilisés dans le solde de caisse)
            [
                'title' => 'Achat de 10 ballons de match',
                'description' => 'Achat chez le fournisseur officiel.',
                'amount' => 150000.00,
                'status' => 'executed',
                'approved_by' => $adminId,
                'executed_by' => $adminId,
                'approved_at' => Carbon::now()->subDays(5),
                'executed_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Frais de transport match amical',
                'description' => 'Paiement du bus.',
                'amount' => 85000.00,
                'status' => 'executed',
                'approved_by' => $adminId,
                'executed_by' => $adminId,
                'approved_at' => Carbon::now()->subDays(3),
                'executed_at' => Carbon::now()->subDays(2),
            ],

            // Ordre de décaissement APPROUVÉ (en attente de paiement par le trésorier)
            [
                'title' => 'Achat maillots d\'entraînement',
                'description' => 'Validation accordée par le bureau.',
                'amount' => 120000.00,
                'status' => 'approved',
                'approved_by' => $adminId,
                'executed_by' => null,
                'approved_at' => Carbon::now()->subDay(),
                'executed_at' => null,
            ],

            // Demande de dépense EN ATTENTE de validation
            [
                'title' => 'Pharmacie & Soins d\'urgence',
                'description' => 'Prise en charge de la boîte à pharmacie.',
                'amount' => 35000.00,
                'status' => 'pending',
                'approved_by' => null,
                'executed_by' => null,
                'approved_at' => null,
                'executed_at' => null,
            ],
        ];

        foreach ($expenses as $expense) {
            Expense::create($expense);
        }
    }
}