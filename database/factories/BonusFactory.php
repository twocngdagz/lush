<?php

namespace Database\Factories;

use App\Models\Bonus;
use App\Models\EarningMethodType;
use App\Models\Promotion;
use App\Models\PromotionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bonus>
 */
class BonusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bonus::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => function () {
                return Promotion::factory()->create([
                    'promotion_type_id' => PromotionType::where('identifier', '=', 'bonus')->first()->id,
                ])->id;
            },
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ];
    }

    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'promotion_id' => function () {
                    return Promotion::factory()->active()->create([
                        'promotion_type_id' => PromotionType::where('identifier', '=', 'bonus')->first()->id,
                    ])->id;
                }
            ];
        });
    }

    public function configure(): BonusFactory
    {
        return $this->afterCreating(function (Bonus $bonus) {
            $bonus->promotion->earningMethods()->create([
                'earning_method_type_id' => EarningMethodType::inRandomOrder()->first()->id,
                'earning_criteria_step_value' => rand(1, 10)
            ]);
        });
    }
}
