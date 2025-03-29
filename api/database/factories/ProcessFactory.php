<?php

namespace Database\Factories;

use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcessFactory extends Factory
{
    protected $model = Process::class;

    public function definition()
    {
        return [
            'title'   => $this->faker->word(),
            'content' => $this->faker->sentence(),
            'value_per_minute' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}