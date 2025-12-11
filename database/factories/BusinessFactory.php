<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'registration_number' => $this->faker->bothify('REG-#####'),
            'industry_type' => $this->faker->randomElement(\App\Enums\IndustryType::cases()),
            'company_size' => $this->faker->randomElement(\App\Enums\CompanySize::cases()),
            'estimated_yearly_turnover' => $this->faker->randomElement(\App\Enums\YearlyTurnover::cases()),
            'email_verified_at' => now(),
        ];
    }
}
