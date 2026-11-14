<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Models\Country;
use Illuminate\Database\Seeder;

/** Pays de lancement. Les autres pays s'ajoutent depuis le back-office. */
class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::updateOrCreate(
            ['code' => 'CG'],
            ['name' => 'République du Congo', 'default_locale' => 'fr', 'currency' => 'XAF', 'is_active' => true],
        );
    }
}
