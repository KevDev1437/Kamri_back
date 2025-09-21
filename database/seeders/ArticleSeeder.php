<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Lookbook Été 2025',
                'excerpt' => 'Découvrez les dernières tendances mode pour cet été.',
                'content' => 'L\'été 2025 s\'annonce coloré et vibrant. Les tendances mettent l\'accent sur les matières naturelles, les couleurs vives et les coupes fluides. Découvrez notre sélection des pièces incontournables de la saison.',
                'author' => 'Sophie Martin',
                'published_at' => Carbon::now()->subDays(5),
                'tags' => ['mode', 'été', 'tendances', 'lookbook']
            ],
            [
                'title' => 'Guide Mode Automne',
                'excerpt' => 'Préparez votre garde-robe pour l\'automne.',
                'content' => 'L\'automne approche et il est temps de renouveler votre garde-robe. Découvrez nos conseils pour créer des looks parfaits pour cette saison de transition.',
                'author' => 'Marie Dubois',
                'published_at' => Carbon::now()->subDays(10),
                'tags' => ['mode', 'automne', 'garde-robe', 'conseils']
            ],
            [
                'title' => 'Accessoires Indispensables',
                'excerpt' => 'Les accessoires qui font la différence.',
                'content' => 'Un bon accessoire peut transformer complètement une tenue. Découvrez notre sélection d\'accessoires indispensables pour sublimer vos looks.',
                'author' => 'Julie Leroy',
                'published_at' => Carbon::now()->subDays(15),
                'tags' => ['accessoires', 'bijoux', 'sacs', 'style']
            ],
            [
                'title' => 'Beauté Naturelle',
                'excerpt' => 'Adoptez une routine beauté naturelle.',
                'content' => 'La beauté naturelle est plus que jamais d\'actualité. Découvrez nos conseils pour une routine beauté respectueuse de votre peau et de l\'environnement.',
                'author' => 'Emma Rousseau',
                'published_at' => Carbon::now()->subDays(20),
                'tags' => ['beauté', 'naturel', 'soins', 'écologie']
            ]
        ];

        foreach ($articles as $articleData) {
            Article::create([
                'title' => $articleData['title'],
                'slug' => Str::slug($articleData['title']),
                'excerpt' => $articleData['excerpt'],
                'content' => $articleData['content'],
                'image' => 'https://picsum.photos/600/400?random=' . rand(1, 1000),
                'author' => $articleData['author'],
                'published_at' => $articleData['published_at'],
                'is_published' => true,
                'meta_title' => $articleData['title'] . ' - KAMRI Magazine',
                'meta_description' => $articleData['excerpt'],
                'tags' => $articleData['tags']
            ]);
        }
    }
}
