<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        ShippingMethod::updateOrCreate(
            ['code' => 'standard'],
            [
                'label' => 'Livraison Standard',
                'price' => 4.99,
                'eta' => '2-3 jours ouvrables',
                'active' => true,
                'countries' => ['BE','FR','NL','DE','LU']
            ]
        );

        ShippingMethod::updateOrCreate(
            ['code' => 'express'],
            [
                'label' => 'Livraison Express',
                'price' => 9.99,
                'eta' => '24–48h',
                'active' => true,
                'countries' => ['BE','FR','NL','DE','LU']
            ]
        );
    }
}
