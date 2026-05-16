<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'food' => fake()->randomElement([
                '2 scrambled eggs with toast',
                'grilled chicken breast with rice',
                'large caesar salad',
                'peanut butter sandwich',
                'bowl of oatmeal with banana',
            ]),
            'calories' => fake()->numberBetween(150, 900),
            'protein'  => fake()->numberBetween(5, 40),
            'carbs'    => fake()->numberBetween(10, 80),
            'fat'      => fake()->numberBetween(2, 30),
        ];
    }
}
