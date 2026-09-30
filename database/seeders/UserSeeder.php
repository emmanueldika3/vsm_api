<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('1234');

        // 1. Admin / Président
        User::updateOrCreate(
            ['phone' => '690000000'],
            [
                'name' => 'Emmanuel Dika',
                'email' => 'admin@vsm.com',
                'password' => $defaultPassword,
                'role' => 'president',
                'position' => 'Milieu',
                'jersey_number' => 10,
                'status' => 'active',
            ]
        );

        // 2. Joueur principal
        User::updateOrCreate(
            ['phone' => '690000001'],
            [
                'name' => 'Yvan Moussongo',
                'email' => 'player@vsm.com',
                'password' => $defaultPassword,
                'role' => 'player',
                'position' => 'Milieu',
                'jersey_number' => 8,
                'status' => 'active',
            ]
        );

        // 3. Trésorier
        User::updateOrCreate(
            ['phone' => '690000002'],
            [
                'name' => 'Jean-Paul Nsoga',
                'email' => 'tresorier@vsm.com',
                'password' => $defaultPassword,
                'role' => 'treasurer',
                'position' => 'Défenseur',
                'jersey_number' => 5,
                'status' => 'active',
            ]
        );

        // 4. Coach
        User::updateOrCreate(
            ['phone' => '690000003'],
            [
                'name' => 'Nog Guy',
                'email' => 'coach@vsm.com',
                'password' => $defaultPassword,
                'role' => 'coach',
                'position' => null,
                'jersey_number' => null,
                'status' => 'active',
            ]
        );

        // 5 à 7. Joueurs spécifiques déjà définis
        $specificPlayers = [
            ['name' => 'ntamack Guy', 'phone' => '690000013', 'email' => 'ntamackh@vsm.com', 'position' => 'Attaquant', 'jersey_number' => 20],
            ['name' => 'basseck bathelemy', 'phone' => '690000014', 'email' => 'bamsseck@vsm.com', 'position' => 'Milieu', 'jersey_number' => 22],
            ['name' => 'voundi samuel', 'phone' => '690000015', 'email' => 'voundi@vsm.com', 'position' => 'Attaquant', 'jersey_number' => 25],
            ['name' => "Samuel Eto'o", 'phone' => '+237690000004', 'email' => 'samueletoo@vsm.com', 'position' => 'Attaquant', 'jersey_number' => 9],
            ['name' => 'Rigobert Song', 'phone' => '+237690000005', 'email' => 'rigobertsong@vsm.com', 'position' => 'Défenseur', 'jersey_number' => 4],
            ['name' => 'Geremi Njitap', 'phone' => '+237690000006', 'email' => 'gereminjitap@vsm.com', 'position' => 'Milieu', 'jersey_number' => 11],
            ['name' => 'Carlos Kameni', 'phone' => '+237690000007', 'email' => 'carloskameni@vsm.com', 'position' => 'Gardien', 'jersey_number' => 1],
            ['name' => 'Patrick Mboma', 'phone' => '+237690000008', 'email' => 'patrickmboma@vsm.com', 'position' => 'Attaquant', 'jersey_number' => 19, 'status' => 'pending'],
            ['name' => 'Stephane Mbia', 'phone' => '+237690000009', 'email' => 'stephanembia@vsm.com', 'position' => 'Milieu', 'jersey_number' => 17, 'status' => 'pending'],
        ];

        foreach ($specificPlayers as $p) {
            User::updateOrCreate(
                ['phone' => $p['phone']],
                [
                    'name' => $p['name'],
                    'email' => $p['email'],
                    'password' => $defaultPassword,
                    'role' => 'player',
                    'position' => $p['position'],
                    'jersey_number' => $p['jersey_number'],
                    'status' => $p['status'] ?? 'active',
                ]
            );
        }

        // 6. Génération automatique pour atteindre exactement 35 utilisateurs au total
        $currentCount = User::count();
        $targetCount = 35;
        $positions = ['Gardien', 'Défenseur', 'Milieu', 'Attaquant'];

        for ($i = $currentCount + 1; $i <= $targetCount; $i++) {
            $phone = '6900000' . str_pad($i, 2, '0', STR_PAD_LEFT);
            User::updateOrCreate(
                ['phone' => $phone],
                [
                    'name' => "Joueur Test $i",
                    'email' => "joueurtest{$i}@vsm.com",
                    'password' => $defaultPassword,
                    'role' => 'player',
                    'position' => $positions[array_rand($positions)],
                    'jersey_number' => $i + 30, // Pour éviter les doublons de numéros de maillot
                    'status' => 'active',
                ]
            );
        }
    }
}