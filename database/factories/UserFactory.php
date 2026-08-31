<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'first_name' => $this->faker->firstName(),
            'middle_name' => null,
            'last_name' => $this->faker->lastName(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'mobile' => $this->faker->numerify('##########'),
            'password' => Hash::make('password'),
            'status' => 1,
            'is_sub_user' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 0]);
    }
}
