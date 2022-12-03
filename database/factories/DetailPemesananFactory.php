<?php

namespace Database\Factories;

use App\Models\HistoryPemesanan;
use App\Models\Menu;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DetailPemesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $statusList = ['belum dikirim','terkirim', 'diterima'];
        $jumlah = fake()->numberBetween(1,5);
        // $harga = fake()->numberBetween(1,10) * 10000;
        return [
            "pemesanan_id" => HistoryPemesanan::factory(),
            "menu_id" => Menu::factory(),
            "detail_jumlah" => $jumlah,
            'detail_total' => function (array $attributes) use ($jumlah) {
                return Menu::find($attributes['menu_id'])->menu_harga * $jumlah;
            },
            "detail_tanggal" => fake()->dateTimeBetween('-10 months'),
            "detail_status" => $statusList[rand(0,2)]
        ];
    }

    /**
     * Indicate that the model's role is customer
     *
     * @return static
     */
    public function forProvider(Users $provider)
    {
        $menus = $provider->Menu;
        $menuTerpilih = $menus[rand(0,$menus->count()-1)];

        return $this->state(fn (array $attributes) => [
            "menu_id" => $menuTerpilih->menu_id,
            "detail_jumlah" => $attributes['detail_jumlah'],
            'detail_total' => $menuTerpilih->menu_harga * $attributes['detail_jumlah'],
        ]);
    }
}
