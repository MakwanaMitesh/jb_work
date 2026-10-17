<?php

namespace Database\Factories;

use App\Models\ContactEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactEnquiryFactory extends Factory
{
    protected $model = ContactEnquiry::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'mobile' => '+91' . fake()->numerify('##########'),
            'email' => fake()->safeEmail(),
            'city' => fake()->city(),
            'loan_requirement' => fake()->randomElement(['Business Loan', 'CMA Report', 'Working Capital CC Limit', 'DPR Preparation']),
            'message' => fake()->paragraph(),
            'status' => ContactEnquiry::STATUS_NEW,
        ];
    }
}
