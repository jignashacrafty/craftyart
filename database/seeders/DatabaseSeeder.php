<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // Subscription Plans Seeder - Pehla plans create kare che
        $this->call(SubscriptionPlansSeeder::class);

        // Purchase History & User Subscriptions Seeder
        // crafty_revenue database ma purchase_history ane user_subscriptions tables ma data add kare che
        $this->call(PurchaseHistoryAndSubscriptionsSeeder::class);
    }
}
