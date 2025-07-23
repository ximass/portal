<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Acre', 'abbreviation' => 'AC', 'icms' => 19.00],
            ['name' => 'Alagoas', 'abbreviation' => 'AL', 'icms' => 20.00],
            ['name' => 'Amapá', 'abbreviation' => 'AP', 'icms' => 18.00],
            ['name' => 'Amazonas', 'abbreviation' => 'AM', 'icms' => 20.00],
            ['name' => 'Bahia', 'abbreviation' => 'BA', 'icms' => 20.50],
            ['name' => 'Ceará', 'abbreviation' => 'CE', 'icms' => 20.00],
            ['name' => 'Distrito Federal', 'abbreviation' => 'DF', 'icms' => 20.00],
            ['name' => 'Espírito Santo', 'abbreviation' => 'ES', 'icms' => 17.00],
            ['name' => 'Goiás', 'abbreviation' => 'GO', 'icms' => 19.00],
            ['name' => 'Maranhão', 'abbreviation' => 'MA', 'icms' => 22.00],
            ['name' => 'Mato Grosso', 'abbreviation' => 'MT', 'icms' => 17.00],
            ['name' => 'Mato Grosso do Sul', 'abbreviation' => 'MS', 'icms' => 17.00],
            ['name' => 'Minas Gerais', 'abbreviation' => 'MG', 'icms' => 18.00],
            ['name' => 'Pará', 'abbreviation' => 'PA', 'icms' => 19.00],
            ['name' => 'Paraíba', 'abbreviation' => 'PB', 'icms' => 19.00],
            ['name' => 'Paraná', 'abbreviation' => 'PR', 'icms' => 19.50],
            ['name' => 'Pernambuco', 'abbreviation' => 'PE', 'icms' => 20.50],
            ['name' => 'Piauí', 'abbreviation' => 'PI', 'icms' => 21.00],
            ['name' => 'Rio de Janeiro', 'abbreviation' => 'RJ', 'icms' => 22.00],
            ['name' => 'Rio Grande do Norte', 'abbreviation' => 'RN', 'icms' => 18.00],
            ['name' => 'Rio Grande do Sul', 'abbreviation' => 'RS', 'icms' => 17.00],
            ['name' => 'Rondônia', 'abbreviation' => 'RO', 'icms' => 19.50],
            ['name' => 'Roraima', 'abbreviation' => 'RR', 'icms' => 20.00],
            ['name' => 'Santa Catarina', 'abbreviation' => 'SC', 'icms' => 17.00],
            ['name' => 'São Paulo', 'abbreviation' => 'SP', 'icms' => 18.00],
            ['name' => 'Sergipe', 'abbreviation' => 'SE', 'icms' => 19.00],
            ['name' => 'Tocantins', 'abbreviation' => 'TO', 'icms' => 20.00],
        ];

        foreach ($states as $state) {
            State::create($state);
        }
    }
}
