<?php

namespace Database\Factories;

use App\Models\HistoryTopup;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistoryTopup>
 */
class HistoryTopupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "topup_nominal" => fake()->numberBetween(1,100) * 1000,
            "topup_tanggal" => fake()->dateTimeBetween("-10 days"),
            "topup_response" => 200,
            "users_id" => Users::factory()->customer(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (HistoryTopup $historyTopup) {
            //
        })->afterCreating(function (HistoryTopup $historyTopup) {
            $users = $historyTopup->Users;
            $users->users_saldo += $historyTopup->topup_nominal;
            $users->save();
        });
    }
}
