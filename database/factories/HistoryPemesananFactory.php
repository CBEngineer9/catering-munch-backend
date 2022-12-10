<?php

namespace Database\Factories;

use App\Models\HistoryPemesanan;
use App\Models\Menu;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistoryPemesanan>
 */
class HistoryPemesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $statusList = ["menunggu","ditolak","diterima","selesai"];
        return [
            "users_provider" => Users::factory()->customer(),
            "users_customer" => Users::factory()->provider()->has(Menu::factory()->count(3)),
            "pemesanan_jumlah" => 0,
            "pemesanan_total" => 0,
            "pemesanan_status" => $statusList[rand(0,3)],
            "pemesanan_rating" => fake()->numberBetween(1,5),
        ];
    }


    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (HistoryPemesanan $historyPemesanan) {
            //
        })->afterCreating(function (HistoryPemesanan $historyPemesanan) {
            $total = $historyPemesanan->DetailPemesanan->sum('detail_total');
            $jumlah = $historyPemesanan->DetailPemesanan->count('detail_id');
            $historyPemesanan->pemesanan_total = $total;
            $historyPemesanan->pemesanan_jumlah = $jumlah;
            $historyPemesanan->save();
        });
    }
}
