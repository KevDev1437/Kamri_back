<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LiveStream;
use Carbon\Carbon;

class LiveStreamSeeder extends Seeder
{
    public function run(): void
    {
        LiveStream::create([
            'title' => 'Shopping Live - Nouvelle Collection',
            'description' => 'Découvrez en direct notre nouvelle collection automne-hiver avec des offres exclusives !',
            'stream_url' => 'https://via.placeholder.com/800x450/FF6B6B/FFFFFF?text=LIVE+SHOPPING',
            'thumbnail' => 'https://via.placeholder.com/400x225/FF6B6B/FFFFFF?text=LIVE',
            'is_active' => true,
            'started_at' => Carbon::now()->subHours(2),
            'viewer_count' => rand(50, 500),
            'scheduled_at' => Carbon::now()->subHours(2)
        ]);

        // Stream programmé pour plus tard
        LiveStream::create([
            'title' => 'Live Beauté - Conseils et Astuces',
            'description' => 'Rejoignez-nous pour une session beauté avec nos experts.',
            'stream_url' => 'https://via.placeholder.com/800x450/4ECDC4/FFFFFF?text=PROCHAINEMENT',
            'thumbnail' => 'https://via.placeholder.com/400x225/4ECDC4/FFFFFF?text=BIENTOT',
            'is_active' => false,
            'viewer_count' => 0,
            'scheduled_at' => Carbon::now()->addHours(3)
        ]);
    }
}
