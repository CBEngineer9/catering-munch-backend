<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\HistoryPemesanan;
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
            Users::factory()->create([
                "users_nama" => "admin",
                "users_email" => "admin@admin.com",
                "users_password" => Hash::make("admin"),
                "users_alamat" => fake()->address(),
                "users_telepon" => fake()->phoneNumber(),
                "users_role" => "admin",
            ]);
        }
        $customers = Users::factory()->count(20)->customer();
        $providers = Users::factory()->count(20)->provider()->has(Menu::factory()->count(3));

        $customers->create();
        $providers->create();

        // HistoryPemesanan::factory()->count(5)->for(Users::factory()->customer(),'UsersCustomer')->for(Users::factory()->provider()->has(Menu::factory()->count(3)), 'UsersProvider')->create();
    }
}
