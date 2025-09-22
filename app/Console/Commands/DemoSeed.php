<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DemoSeeder;

class DemoSeed extends Command
{
    protected $signature = 'demo:seed
                            {--products=200 : Nombre de produits à créer}
                            {--users=30 : Nombre d\'utilisateurs à créer}
                            {--orders=40 : Nombre de commandes à créer}
                            {--with-reviews : Inclure les avis}
                            {--force : Forcer l\'exécution même en production}';

    protected $description = 'Génère des données de démo pour l\'environnement de développement';

    public function handle()
    {
        // Vérification de sécurité
        if (!app()->environment('local') &&
            !filter_var(env('SEED_DEMO', false), FILTER_VALIDATE_BOOL) &&
            !$this->option('force')) {
            $this->warn('❌ Commande bloquée (non-local et SEED_DEMO!=true)');
            $this->line('💡 Utilisez --force pour forcer l\'exécution ou définissez SEED_DEMO=true');
            return self::FAILURE;
        }

        if ($this->option('force') && !app()->environment('local')) {
            $this->warn('⚠️  Exécution forcée en production !');
            if (!$this->confirm('Êtes-vous sûr de vouloir continuer ?')) {
                $this->info('❌ Opération annulée');
                return self::FAILURE;
            }
        }

        $this->info('🌱 Génération des données de démo...');
        $this->line('');

        // Afficher les options
        $this->line('📋 Options sélectionnées :');
        $this->line('   • Produits : ' . $this->option('products'));
        $this->line('   • Utilisateurs : ' . $this->option('users'));
        $this->line('   • Commandes : ' . $this->option('orders'));
        $this->line('   • Avis : ' . ($this->option('with-reviews') ? 'Oui' : 'Non'));
        $this->line('');

        // Exécuter le seeder
        try {
            $this->call('db:seed', [
                '--class' => DemoSeeder::class,
                '--force' => true
            ]);

            $this->line('');
            $this->info('✅ Données de démo générées avec succès !');
            $this->line('');
            $this->displayQuickCheck();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la génération des données :');
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function displayQuickCheck()
    {
        $this->info('🔍 Vérification rapide :');

        try {
            $this->line('   • Catégories : ' . \App\Models\Category::count());
            $this->line('   • Produits : ' . \App\Models\Product::count());
            $this->line('   • Utilisateurs : ' . \App\Models\User::count());
            $this->line('   • Adresses : ' . \App\Models\Address::count());

            if (class_exists(\App\Models\Order::class)) {
                $this->line('   • Commandes : ' . \App\Models\Order::count());
            }

            if (class_exists(\App\Models\Review::class)) {
                $this->line('   • Avis : ' . \App\Models\Review::count());
            }

            if (class_exists(\App\Models\Coupon::class)) {
                $this->line('   • Coupons : ' . \App\Models\Coupon::count());
            }
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Impossible de vérifier les compteurs');
        }

        $this->line('');
        $this->info('🔑 Compte admin : admin@kamri.test / password');
        $this->info('🎫 Codes promo : WELCOME10, SAVE20, FREESHIP');
        $this->line('');
        $this->info('🚀 Vous pouvez maintenant tester votre application !');
    }
}
