<?php

namespace Database\Factories;

use App\Models\Motive;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResourceComplaint>
 */
class ResourceComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(1, User::count()),
            'resource_id' => $this->faker->numberBetween(1, Resource::count()),
            'motive_id' => $this->faker->numberBetween(1, Motive::count()),
        ];
    }
}