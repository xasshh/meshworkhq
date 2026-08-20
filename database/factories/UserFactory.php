<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'client',
        ]);
    }

    public function professional(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'professional',
            'credits' => 10,
        ]);
    }

    /**
     * A professional whose profile clears the 70% completeness gate.
     *
     * The matcher will not alert a profile below that threshold, so any test
     * that expects a professional to be matched needs this rather than the
     * bare professional() state, which fills nothing beyond name and email.
     * Pass skill_tags to create() to override the default skill.
     */
    public function alertReady(): static
    {
        return $this->professional()->state(fn (array $attributes) => [
            'professional_title' => fake()->jobTitle(),
            'phone' => '0803'.fake()->numerify('#######'),
            'bio' => fake()->sentence(12),
            'portfolio_url' => 'https://'.fake()->domainName(),
            'skill_tags' => ['design'],
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
