<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Exécuter le DemoSeeder uniquement en local ou si SEED_DEMO=true
        if (app()->environment('local') || filter_var(env('SEED_DEMO', false), FILTER_VALIDATE_BOOL)) {
            $this->call(DemoSeeder::class);
        } else {
            $this->command->warn('DemoSeeder ignoré (environnement non-local et SEED_DEMO!=true)');

            // Seeders de base pour les autres environnements
            $this->call([
                CategorySeeder::class,
                ProductSeeder::class,
                ArticleSeeder::class,
                LiveStreamSeeder::class,
            ]);
        }
    }
}
