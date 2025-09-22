<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Coupon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Vérification de sécurité : ne jamais exécuter en production
        if (!app()->environment('local') && !filter_var(env('SEED_DEMO', false), FILTER_VALIDATE_BOOL)) {
            $this->command->warn('DemoSeeder: ignoré (non-local et SEED_DEMO!=true)');
            return;
        }

        $this->command->info('🌱 Génération des données de démo...');

        DB::transaction(function () {
            // 1) Catégories de base
            $this->command->info('📁 Création des catégories...');
            $categories = collect([
                'Mode & Accessoires',
                'Maison & Jardin',
                'High-tech & Électronique',
                'Sport & Fitness',
                'Beauté & Santé',
                'Jouets & Enfants',
                'Auto & Moto',
                'Livres & Médias'
            ])->map(function ($name) {
                return Category::firstOrCreate(['slug' => Str::slug($name)], [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'image' => 'https://picsum.photos/seed/' . Str::slug($name) . '/400/300',
                    'is_hot' => fake()->boolean(30),
                    'parent_id' => null,
                    'description' => fake()->paragraph(2),
                    'sort_order' => fake()->numberBetween(1, 100),
                ]);
            });

            // 2) Produits
            $this->command->info('🛍️ Création des produits...');
            $products = Product::factory(200)->create();

            // Assigner des catégories aux produits
            foreach ($products as $product) {
                $product->category_id = $categories->random()->id;
                $product->save();
            }

            // 3) Utilisateur admin
            $this->command->info('👤 Création de l\'utilisateur admin...');
            $admin = User::firstOrCreate(
                ['email' => 'admin@kamri.test'],
                [
                    'name' => 'Admin Demo',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            // 4) Utilisateurs de démo
            $this->command->info('👥 Création des utilisateurs...');
            $users = User::factory(30)->create();

            // 5) Adresses pour les utilisateurs
            $this->command->info('🏠 Création des adresses...');
            foreach ($users as $user) {
                $addressCount = fake()->numberBetween(1, 2);
                $addresses = Address::factory()->count($addressCount)->create(['user_id' => $user->id]);

                // Définir une adresse par défaut pour la livraison
                if ($addresses->isNotEmpty()) {
                    $addresses->first()->update(['is_default_shipping' => true]);
                }
            }

            // 6) Commandes avec lignes
            $this->command->info('📦 Création des commandes...');
            if (class_exists(Order::class) && class_exists(OrderItem::class)) {
                $orderUsers = $users->random(min(20, $users->count()));
                $productPool = Product::inStock()->take(100)->get()->count() > 0
                    ? Product::inStock()->take(100)->get()
                    : Product::take(100)->get();

                foreach ($orderUsers as $user) {
                    $order = Order::factory()->create(['user_id' => $user->id]);
                    $linesCount = fake()->numberBetween(1, 4);
                    $subtotal = 0;

                    for ($i = 0; $i < $linesCount; $i++) {
                        $product = $productPool->random();
                        $qty = fake()->numberBetween(1, 3);
                        $unitPrice = $product->sale_price ?? $product->price;
                        $lineTotal = $unitPrice * $qty;
                        $subtotal += $lineTotal;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_image' => $product->image,
                            'unit_price' => $unitPrice,
                            'qty' => $qty,
                            'subtotal' => $lineTotal,
                            'options' => fake()->boolean(30) ? [
                                'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
                                'color' => fake()->randomElement(['Rouge', 'Bleu', 'Vert', 'Noir', 'Blanc']),
                            ] : null,
                            'options_hash' => '',
                        ]);
                    }

                    // Calculer les totaux
                    $shippingPrice = $order->shipping_price;
                    $tax = round($subtotal * 0.21, 2); // TVA 21%
                    $total = round($subtotal + $shippingPrice + $tax, 2);

                    $order->update([
                        'subtotal' => round($subtotal, 2),
                        'tax' => $tax,
                        'total' => $total,
                    ]);
                }
            }

            // 7) Avis (si la table existe)
            $this->command->info('⭐ Création des avis...');
            if (class_exists(Review::class)) {
                Review::factory(300)->create();
            }

            // 8) Coupons de démo (si la table existe)
            $this->command->info('🎫 Création des coupons...');
            if (class_exists(Coupon::class)) {
                $this->createDemoCoupons($categories);
            }
        });

        $this->command->info('✅ Données de démo générées avec succès !');
        $this->displaySummary();
    }

    private function createDemoCoupons($categories)
    {
        // Coupon pourcentage - tous les produits
        Coupon::firstOrCreate(['code' => 'WELCOME10'], [
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10.00,
            'active' => true,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->addDays(30),
            'min_subtotal' => 30.00,
            'max_redemptions' => 1000,
            'per_user_limit' => 1,
            'applies_to' => 'all',
            'notes' => 'Coupon de bienvenue 10%',
        ]);

        // Coupon fixe - catégorie High-tech
        $highTechCategory = $categories->where('name', 'like', '%High-tech%')->first();
        if ($highTechCategory) {
            $coupon = Coupon::firstOrCreate(['code' => 'SAVE20'], [
                'code' => 'SAVE20',
                'type' => 'fixed',
                'value' => 20.00,
                'active' => true,
                'starts_at' => now()->subDays(15),
                'ends_at' => now()->addDays(15),
                'min_subtotal' => 50.00,
                'max_redemptions' => 500,
                'per_user_limit' => 2,
                'applies_to' => 'categories',
                'notes' => '20€ de réduction sur la High-tech',
            ]);

            $coupon->categories()->sync([$highTechCategory->id]);
        }

        // Coupon livraison gratuite
        Coupon::firstOrCreate(['code' => 'FREESHIP'], [
            'code' => 'FREESHIP',
            'type' => 'free_shipping',
            'value' => null,
            'active' => true,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(7),
            'min_subtotal' => 25.00,
            'max_redemptions' => 2000,
            'per_user_limit' => 3,
            'applies_to' => 'all',
            'notes' => 'Livraison gratuite',
        ]);
    }

    private function displaySummary()
    {
        $this->command->info('📊 Résumé des données générées :');
        $this->command->line('   • Catégories : ' . Category::count());
        $this->command->line('   • Produits : ' . Product::count());
        $this->command->line('   • Utilisateurs : ' . User::count());
        $this->command->line('   • Adresses : ' . Address::count());

        if (class_exists(Order::class)) {
            $this->command->line('   • Commandes : ' . Order::count());
        }

        if (class_exists(OrderItem::class)) {
            $this->command->line('   • Lignes de commande : ' . OrderItem::count());
        }

        if (class_exists(Review::class)) {
            $this->command->line('   • Avis : ' . Review::count());
        }

        if (class_exists(Coupon::class)) {
            $this->command->line('   • Coupons : ' . Coupon::count());
        }

        $this->command->info('');
        $this->command->info('🔑 Compte admin : admin@kamri.test / password');
        $this->command->info('🎫 Codes promo : WELCOME10, SAVE20, FREESHIP');
    }
}
