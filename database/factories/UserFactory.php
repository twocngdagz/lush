<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => faker()->name,
            'email' => faker()->safeEmail,
            'password' => Hash::make(Str::random(10)),
            'remember_token' => Str::random(10),
            'account_id' => function () {
                return Account::first();
            },
            'is_locked' => false,
            'password_changed_at' => now(),
            'property_id' => function () {
                $ids = Property::pluck('id');
                if ($ids->isNotEmpty()) {
                    return $ids->first();
                }

                return Property::factory()->create()->id;
            }
        ];
    }

    public function superAdmin(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterCreating(function (User $user) {
            $user->assign('super-admin');
        });
    }

    public function configure()
    {
        $this->afterCreating(function (User $user) {
            $ids = Property::pluck('id');
            if ($ids->isNotEmpty()) {
                $user->properties()->attach($ids);
            } else {
                $property = Property::factory()->create();
                $user->properties()->attach($property->id);
            }
        });
    }

}
