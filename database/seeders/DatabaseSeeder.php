<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Seeders fondamentaux (Création des utilisateurs / membres)
            // UserSeeder::class,
            
            // 2. Seeders des opérations financières
            ContributionSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}