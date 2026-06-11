<?php

namespace Database\Seeders;

use App\Models\CreditBundle;
use Illuminate\Database\Seeder;

class CreditBundleSeeder extends Seeder
{
    public function run(): void
    {
        $bundles = [
            [
                'name' => 'Starter Pack',
                'credits' => 5,
                'price_kobo' => 750_000,   // ₦7,500
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth Pack',
                'credits' => 15,
                'price_kobo' => 1_800_000, // ₦18,000
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro Pack',
                'credits' => 30,
                'price_kobo' => 3_000_000, // ₦30,000
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Agency Pack',
                'credits' => 60,
                'price_kobo' => 5_400_000, // ₦54,000 (10% discount)
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($bundles as $bundle) {
            CreditBundle::firstOrCreate(
                ['name' => $bundle['name']],
                $bundle
            );
        }
    }
}
