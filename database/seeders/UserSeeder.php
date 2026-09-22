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

        // 1. Admin / Président (Joue au milieu sur le terrain)
        User::create([
            'name' => 'Emmanuel Dika',
            'phone' => '690000000',
            'email' => 'admin@vsm.com',
            'password' => $defaultPassword,
            'role' => 'president',
            'position' => 'Milieu',
            'jersey_number' => 10,
            'status' => 'active',
            
        ]);

        // 2. Joueur principal
        User::create([
            'name' => 'Yvan Moussongo',
            'phone' => '690000001',
            'email' => 'player@vsm.com',
            'password' => $defaultPassword,
            'role' => 'player',
            'position' => 'Milieu',
            'jersey_number' => 8,
            'status' => 'active',
            
        ]);

        // 3. Trésorier (Joue en défense)
        User::create([
            'name' => 'Jean-Paul Nsoga',
            'phone' => '690000002',
            'email' => 'tresorier@vsm.com',
            'password' => $defaultPassword,
            'role' => 'treasurer',
            'position' => 'Défenseur',
            'jersey_number' => 5,
            'status' => 'active',
           
        ]);

        // 4. Coach (Staff pure - Pas de poste terrain ni de maillot)
        User::create([
            'name' => 'Nog Guy',
            'phone' => '690000003',
            'email' => 'coach@vsm.com',
            'password' => $defaultPassword,
            'role' => 'coach',
            'position' => null,
            'jersey_number' => null,
            'status' => 'active',
            
        ]);

        // 5. Membres Actifs
        $players = [
            ['name' => "Samuel Eto'o", 'position' => 'Attaquant', 'jersey_number' => 9, 'phone' => '+237690000004'],
            ['name' => 'Rigobert Song', 'position' => 'Défenseur', 'jersey_number' => 4, 'phone' => '+237690000005'],
            ['name' => 'Geremi Njitap', 'position' => 'Milieu', 'jersey_number' => 11, 'phone' => '+237690000006'],
            ['name' => 'Carlos Kameni', 'position' => 'Gardien', 'jersey_number' => 1, 'phone' => '+237690000007'],
        ];

        foreach ($players as $player) {
            $emailName = Str::slug($player['name'], '');
            User::create([
                'name' => $player['name'],
                'phone' => $player['phone'],
                'email' => "{$emailName}@vsm.com",
                'password' => $defaultPassword,
                'role' => 'player',
                'position' => $player['position'],
                'jersey_number' => $player['jersey_number'],
                'status' => 'active',
                
            ]);
        }

        // 6. Demandes d'adhésion en attente (status = pending, is_active = false)
        $pending = [
            ['name' => 'Patrick Mboma', 'position' => 'Attaquant', 'jersey_number' => 19, 'phone' => '+237690000008'],
            ['name' => 'Stephane Mbia', 'position' => 'Milieu', 'jersey_number' => 17, 'phone' => '+237690000009'],
        ];

        foreach ($pending as $p) {
            $emailName = Str::slug($p['name'], '');
            User::create([
                'name' => $p['name'],
                'phone' => $p['phone'],
                'email' => "{$emailName}@vsm.com",
                'password' => $defaultPassword,
                'role' => 'player',
                'position' => $p['position'],
                'jersey_number' => $p['jersey_number'],
                'status' => 'pending',
                
            ]);
        }
    }
}