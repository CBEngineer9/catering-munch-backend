<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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


        Users::factory()->create([
            'users_username' => "admin",
            "users_nama" => "admin",
            "users_email" => "admin@admin.com",
            "users_password" => Hash::make("admin"),
            "users_alamat" => fake()->address(),
            "users_telepon" => fake()->phoneNumber(),
            "users_role" => "admin",
        ]);
        Users::factory()->count(10)->create();

    }
}
