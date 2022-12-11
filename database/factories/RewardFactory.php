<?php

namespace Database\Factories;

use App\Models\Reward;
use App\Models\RewardType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reward>
 */
class RewardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reward::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => Str::title(implode(' ', faker()->words(rand(2, 3)))),
            'reward_type_id' => function ()  {
                return faker()->boolean(75) ? 1 : RewardType::whereIn('identifier',
                    ['points', 'tier-points', 'promo', 'prize', 'misc', 'comp'])->inRandomOrder()->first()->id;
            },
            'tier' => null,
            'amount' => rand(1, 20),
            'total_available' => rand(1, 1000),
            'cost' => rand(50, 100),
        ];
    }
}
