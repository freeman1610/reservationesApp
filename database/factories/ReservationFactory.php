<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = Carbon::now()->addDays(rand(1, 7))->setHour(rand(9, 16));
        
        return [
            'user_id' => User::factory(),
            'space_id' => Space::factory(),
            'reservation_date' => $startTime->toDateString(),
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addHours(rand(1, 2)),
            'purpose' => fake()->sentence(),
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}
