<?php

namespace Database\Factories;

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
            'menu_foto' => fake()->file("/tmp","/img",true),
            'menu_harga' => fake()->numberBetween(1,10) * 10000,
            'menu_status' => $statusList[rand(0,1)],
            'user_id'
        ];
    }
}
