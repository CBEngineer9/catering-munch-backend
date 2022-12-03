<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\DetailPemesanan;
use App\Models\HistoryPemesanan;
use App\Models\HistoryTopup;
use App\Models\Menu;
use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Generate admin if not exist
        if (Users::where("users_email","admin@admin.com")->count() == 0) {
            Users::create([
                "users_nama" => "admin",
                "users_email" => "admin@admin.com",
                "users_password" => Hash::make("admin"),
                "users_alamat" => fake()->address(),
                "users_telepon" => fake()->phoneNumber(),
                "users_role" => "admin",
            ]);
        }
        if (Users::where("users_email","kevin@kevin.com")->count() == 0) {
            Users::create([
                "users_nama" => "kevin",
                "users_email" => "kevin@kevin.com",
                "users_password" => Hash::make("123"),
                "users_alamat" => fake()->address(),
                "users_telepon" => fake()->phoneNumber(),
                "users_role" => "customer",
            ]);
        }
        if (Users::where("users_email","provider@provider.com")->count() == 0) {
            Users::create([
                "users_nama" => "dummy",
                "users_email" => "provider@provider.com",
                "users_password" => Hash::make("123"),
                "users_alamat" => fake()->address(),
                "users_telepon" => fake()->phoneNumber(),
                "users_role" => "provider",
            ]);
        }
        $customers = Users::factory()->count(20)->customer()->has(HistoryTopup::factory())->create();
        $providers = Users::factory()->count(20)->provider()->has(Menu::factory()->count(3))->create();

        for ($i=0; $i < 5; $i++) {
            $provider = $providers[rand(0,count($providers)-1)];
            HistoryPemesanan::factory()->count(5)
                ->for($customers[rand(0,count($customers)-1)], 'UsersCustomer')
                ->for($provider, 'UsersProvider')
                ->has(DetailPemesanan::factory()->forProvider($provider)->count(5))
                ->create();

        }

    }
}
