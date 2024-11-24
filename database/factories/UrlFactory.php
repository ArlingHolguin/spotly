<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Url>
 */
class UrlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null, 
            'short_code' => $this->faker->unique()->regexify('[A-Za-z0-9]{6}'),
            'original_url' => $this->faker->url,
            'is_active' => $this->faker->boolean(80), 
            'clicks' => $this->faker->numberBetween(0, 1000),
            'expires_at' => null, 
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function anonymous()
    {
        return $this->state(fn () => [
            'user_id' => null,
            'expires_at' => Carbon::now()->addDays(30),
        ]);
    }
}
