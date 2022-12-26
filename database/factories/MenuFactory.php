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
            'menu_foto' => "menu/sampleFood.jpeg",
            'menu_harga' => fake()->numberBetween(1,10) * 10000,
            'menu_status' => $statusList[rand(0,1)],
            'users_id' => rand(0,Users::count()-1),
        ];
    }

    /**
     * Create with price
     *
     * @param int $harga
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withHarga($harga)
    {
        return $this->state(function (array $attributes) use ($harga) {
            return [
                'menu_harga' => $harga,
            ];
        });
    }
}
