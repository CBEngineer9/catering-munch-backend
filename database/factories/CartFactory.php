<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $jumlah = fake()->numberBetween(1,5);
        return [
            "menu_id" => Menu::factory(),
            "cart_jumlah" => $jumlah,
            'cart_total' => function (array $attributes) use ($jumlah) {
                return Menu::find($attributes['menu_id'])->menu_harga * $jumlah;
            },
            "cart_tanggal" => fake()->dateTimeBetween('-10 months'),
        ];
    }

    /**
     * Get menu from provider
     *
     * @return static
     */
    public function forProvider(Users $provider)
    {
        $menus = $provider->Menu;
        $menuTerpilih = $menus[rand(0,$menus->count()-1)];

        return $this->state(fn (array $attributes) => [
            "menu_id" => $menuTerpilih->menu_id,
            "cart_jumlah" => $attributes['cart_jumlah'],
            'cart_total' => $menuTerpilih->menu_harga * $attributes['cart_jumlah'],
        ]);
    }
}
