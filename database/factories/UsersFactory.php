<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users>
 */
class UsersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $roleList = ["customer","provider"];
        return [
            "users_nama" => fake()->name(),
            "users_email" => fake()->email(),
            "users_password" => Hash::make("123"),
            "users_alamat" => "",
            "users_telepon" => fake()->phoneNumber(),
            "users_role" => $roleList[rand(0,1)],
        ];
    }
}
