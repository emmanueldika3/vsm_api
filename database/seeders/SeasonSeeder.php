<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifie si des saisons existent déjà pour éviter les doublons
        if (DB::table('seasons')->count() === 0) {
            DB::table('seasons')->insert([
                [
                    'name' => 'Saison 2025/2026',
                    'start_date' => '2025-09-01',
                    'end_date' => '2026-06-30',
                    'is_active' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Saison 2026/2027',
                    'start_date' => '2026-09-01',
                    'end_date' => '2027-06-30',
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
            
            $this->command->info('SeasonSeeder exécuté avec succès !');
        } else {
            $this->command->info('La table seasons contient déjà des données.');
        }
    }
}