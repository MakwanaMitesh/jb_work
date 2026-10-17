<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoughLeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'mobile_number' => '+91' . fake()->numerify('##########'),
            'alternate_mobile_number' => null,
            'email' => fake()->safeEmail(),
            'city_id' => null,
            'loan_requirement' => fake()->randomElement(['Business Loan', 'CC Limit', 'Term Loan']),
            'description' => fake()->sentence(),
            'source' => fake()->randomElement(['Walk-in', 'Referral', 'Website']),
            'assigned_employee_id' => null,
            'status' => 'new',
        ];
    }
}
