<?php

namespace Database\Seeders;

use App\Models\CreditBundle;
use Illuminate\Database\Seeder;

class CreditBundleSeeder extends Seeder
{
    /**
     * Bundle pricing in naira, stored as kobo.
     *
     * A credit is 500 naira at the entry tier and falls to 300 at volume. The
     * trial pack exists to sit under the point where someone hesitates to pay
     * a platform they have not earned from yet: it is the first purchase that
     * is hard to win, not the fifth.
     */
    public function run(): void
    {
        $bundles = [
            [
                'name' => 'Trial Pack',
                'credits' => 3,
                'price_kobo' => 150_000,     // 1,500 naira, 500 per credit
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter Pack',
                'credits' => 5,
                'price_kobo' => 250_000,     // 2,500 naira, 500 per credit
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Growth Pack',
                'credits' => 20,
                'price_kobo' => 800_000,     // 8,000 naira, 400 per credit
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Pro Pack',
                'credits' => 50,
                'price_kobo' => 1_750_000,   // 17,500 naira, 350 per credit
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Power Pack',
                'credits' => 150,
                'price_kobo' => 4_500_000,   // 45,000 naira, 300 per credit
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($bundles as $bundle) {
            CreditBundle::updateOrCreate(
                ['name' => $bundle['name']],
                $bundle,
            );
        }
    }
}
