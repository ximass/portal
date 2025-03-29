<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'name'    => $this->faker->name(),
            'email'   => $this->faker->unique()->safeEmail(),
            'phone'   => $this->faker->phoneNumber(),
            'cnpj'    => $this->faker->numerify('########0001##'),
            'cpf'     => $this->faker->numerify('###########'),
            'address' => $this->faker->address(),
        ];
    }
}