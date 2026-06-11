<?php

namespace Database\Factories;

use App\Enums\BriefStatus;
use App\Models\Brief;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brief>
 */
class BriefFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => User::factory()->client(),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraphs(2, true),
            'budget_min' => fake()->randomElement([100_000, 200_000, 500_000]),
            'budget_max' => fake()->randomElement([1_000_000, 2_000_000, 5_000_000]),
            'skill_tags' => fake()->randomElements(['Branding', 'Copywriting', 'SEO', 'Web Development', 'Photography'], 3),
            'is_remote' => fake()->boolean(70),
            'location' => null,
            'status' => BriefStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BriefStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function receivingPitches(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BriefStatus::ReceivingPitches,
            'published_at' => now()->subHour(),
            'expires_at' => now()->addDays(29),
        ]);
    }
}
