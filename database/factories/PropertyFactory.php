<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'account_id' => 1,
            'name' => fake()->company,
            'ext_property_id' => fake()->unique()->numberBetween(1, 100),
            'property_code' => fake()->unique()->countryCode,
        ];
    }
}
