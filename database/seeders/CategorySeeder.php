<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'FEMMES',
                'slug' => 'femmes',
                'image' => '/images/categories/women.jpg',
                'is_hot' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'CURVY',
                'slug' => 'curvy',
                'image' => '/images/categories/curvy.jpg',
                'is_hot' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'HOMME',
                'slug' => 'homme',
                'image' => '/images/categories/men.jpg',
                'is_hot' => false,
                'sort_order' => 3
            ],
            [
                'name' => 'ENFANT & BÉBÉ',
                'slug' => 'enfant-bebe',
                'image' => '/images/categories/kids.jpg',
                'is_hot' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'BEAUTÉ & BIEN-ÊTRE',
                'slug' => 'beaute-bien-etre',
                'image' => '/images/categories/beauty.jpg',
                'is_hot' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'SPORTS',
                'slug' => 'sports',
                'image' => '/images/categories/sports.jpg',
                'is_hot' => true,
                'sort_order' => 6
            ],
            [
                'name' => 'ROBES',
                'slug' => 'robes',
                'image' => '/images/categories/dresses.jpg',
                'is_hot' => false,
                'sort_order' => 7
            ],
            [
                'name' => 'MAISON',
                'slug' => 'maison',
                'image' => '/images/categories/home.jpg',
                'is_hot' => true,
                'sort_order' => 8
            ],
            [
                'name' => 'ÉLECTRONIQUE & AUTO',
                'slug' => 'electronique-auto',
                'image' => '/images/categories/electronics.jpg',
                'is_hot' => false,
                'sort_order' => 9
            ],
            [
                'name' => 'SOUS-VÊTEMENTS & PYJAMAS',
                'slug' => 'sous-vetements-pyjamas',
                'image' => '/images/categories/underwear.jpg',
                'is_hot' => true,
                'sort_order' => 10
            ],
            [
                'name' => 'MAILLOTS DE BAIN',
                'slug' => 'maillots-de-bain',
                'image' => '/images/categories/swimwear.jpg',
                'is_hot' => false,
                'sort_order' => 11
            ],
            [
                'name' => 'ACCESSOIRES, BIJOUX, SACS & CHAUSSURES',
                'slug' => 'accessoires-bijoux-sacs-chaussures',
                'image' => '/images/categories/accessories.jpg',
                'is_hot' => false,
                'sort_order' => 12
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
