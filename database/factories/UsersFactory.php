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
            "users_alamat" => fake()->address(),
            "users_telepon" => fake()->phoneNumber(),
            "users_role" => $roleList[rand(0,1)],
        ];
    }

    /**
     * Indicate that the model's role is provider
     *
     * @return static
     */
    public function provider()
    {
        return $this->state(fn (array $attributes) => [
            'users_role' => 'provider',
        ]);
    }

    /**
     * Indicate that the model's role is customer
     *
     * @return static
     */
    public function customer()
    {
        return $this->state(fn (array $attributes) => [
            'users_role' => 'customer',
        ]);
    }
}
