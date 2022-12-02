<?php

namespace Database\Factories;

use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $statusList = ['tersedia','tidak tersedia'];
        return [
            'menu_nama' => fake()->word(),
            // 'menu_foto' => fake()->file("/tmp","/img",true), // TODO
            'menu_foto' => fake()->word() . ".png",
            'menu_harga' => fake()->numberBetween(1,10) * 10000,
            'menu_status' => $statusList[rand(0,1)],
            'users_id' => rand(0,Users::count()),
        ];
    }
}
