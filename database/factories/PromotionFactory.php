<?php

namespace Database\Factories;

use App\Enums\PromotionStatus;
use App\Models\Promotion;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Promotion::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => 1,
            'name' => Str::title(implode(' ', fake()->words(rand(2, 3)))),
            'available_all_players' => true,
            'has_criteria' => false,
            'deactivated_at' => null
        ];
    }

    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => PromotionStatus::ACTIVE
            ];
        })->afterCreating(function (Promotion $promotion) {
            $promotion->deactivated_at = null;
            $promotion->save();
        });
    }

    public function inactive(): Factory
    {
        return $this->state(function (array $atttributes) {
            return [
                'active' => PromotionStatus::INACTIVE
            ];
        })->afterCreating(function (Promotion $promotion) {
            $promotion->deactivated_at = null;
            $promotion->save();
        });
    }

    public function configure(): PromotionFactory
    {
        return $this->afterCreating(callback: function (Promotion $promotion) {
            $properties = Property::take(rand(1, 4))->pluck('id')->toArray() ?? PropertyFactory::factory()->count(rand(1,4))->create()->pluck('id')->toArray();
            $promotion->properties()->attach($properties);
        });
    }
}
