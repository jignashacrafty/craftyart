<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestReviewsSeeder extends Seeder
{
    /**
     * Seed the application's database with test reviews.
     */
    public function run(): void
    {
        $this->call([
            ReviewSeeder::class,
        ]);
    }
}
