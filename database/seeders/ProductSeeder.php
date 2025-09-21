<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('Aucune catégorie trouvée. Veuillez d\'abord exécuter CategorySeeder.');
            return;
        }

        $products = [
            'Robe d\'été fleurie',
            'T-shirt coton bio',
            'Jean slim taille haute',
            'Baskets blanches',
            'Sac à main cuir',
            'Montre connectée',
            'Parfum floral',
            'Crème hydratante',
            'Casque audio',
            'Chargeur sans fil',
            'Veste en jean',
            'Pull en laine',
            'Pantalon de sport',
            'Chaussures de running',
            'Bijoux fantaisie',
            'Écharpe en soie',
            'Lunettes de soleil',
            'Ceinture en cuir',
            'Portefeuille',
            'Trousse de maquillage'
        ];

        foreach ($products as $index => $productName) {
            $category = $categories->random();

            Product::create([
                'name' => $productName,
                'slug' => Str::slug($productName),
                'description' => "Description détaillée de {$productName}. Produit de qualité premium avec des matériaux soigneusement sélectionnés.",
                'short_description' => "Magnifique {$productName} de haute qualité.",
                'price' => rand(1999, 29999) / 100, // Prix entre 19.99 et 299.99
                'sale_price' => rand(0, 1) ? null : rand(999, 19999) / 100, // 50% de chance d'avoir un prix de vente
                'sku' => 'SKU-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'stock_quantity' => rand(0, 100),
                'image' => "https://picsum.photos/400/400?random=" . ($index + 1),
                'images' => [
                    "https://picsum.photos/400/400?random=" . ($index + 1),
                    "https://picsum.photos/400/400?random=" . ($index + 101),
                    "https://picsum.photos/400/400?random=" . ($index + 201)
                ],
                'category_id' => $category->id,
                'is_featured' => rand(0, 1),
                'is_active' => true,
                'weight' => rand(100, 2000) / 100, // Poids entre 1g et 20kg
                'dimensions' => [
                    'length' => rand(10, 50),
                    'width' => rand(10, 50),
                    'height' => rand(1, 20)
                ],
                'meta_title' => $productName . ' - KAMRI Marketplace',
                'meta_description' => "Achetez {$productName} sur KAMRI Marketplace. Livraison gratuite dès 35€."
            ]);
        }

        // Créer des produits supplémentaires pour avoir plus de variété
        for ($i = 21; $i <= 50; $i++) {
            $category = $categories->random();
            $productName = "Produit " . $i; // Produit 21, 22, 23, etc.

            Product::create([
                'name' => $productName,
                'slug' => Str::slug($productName),
                'description' => "Description de {$productName}. Excellent rapport qualité-prix.",
                'short_description' => "Découvrez notre {$productName}.",
                'price' => rand(999, 49999) / 100,
                'sale_price' => rand(0, 1) ? null : rand(499, 39999) / 100,
                'sku' => 'SKU-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'stock_quantity' => rand(0, 50),
                'image' => "https://picsum.photos/400/400?random={$i}",
                'images' => [
                    "https://picsum.photos/400/400?random={$i}",
                    "https://picsum.photos/400/400?random=" . ($i + 100)
                ],
                'category_id' => $category->id,
                'is_featured' => rand(0, 3) === 0, // 25% de chance d'être en vedette
                'is_active' => true,
                'weight' => rand(50, 1000) / 100,
                'meta_title' => $productName . ' - KAMRI',
                'meta_description' => "Découvrez {$productName} sur KAMRI."
            ]);
        }
    }
}
