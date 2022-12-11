<?php

namespace Database\Factories;

use App\Models\Bonus;
use App\Models\BonusRewardPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BonusRewardPeriod>
 */
class BonusRewardPeriodFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BonusRewardPeriod::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'bonus_id' => function () {
                return Bonus::factory()->create()->id;
            },
            'name' => Str::title(implode(' ', faker()->words(rand(2, 3)))),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'earning_starts_at' => now(),
            'earning_ends_at' => now()->addMonth(),
        ];
    }

    public function withRewards(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterCreating(function (BonusRewardPeriod $bonusRewardPeriod) {
            foreach (range(1, rand(1, 3)) as $reward) {
                $reward = Reward::factory()->create([
                    'name' => 'Reward #' . $reward,
                    'promotion_id' => $bonusRewardPeriod->bonus->promotion->id,
                ]);
                $bonusRewardPeriod->rewards()->save($reward);
            }
        });
    }
}
