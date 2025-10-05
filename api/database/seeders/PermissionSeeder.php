<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'edit_orders',
                'description' => 'Permite editar pedidos (type: order)'
            ],
            [
                'name' => 'edit_pre_orders',
                'description' => 'Permite editar orçamentos (type: pre_order)'
            ],
            [
                'name' => 'delete_orders',
                'description' => 'Permite excluir pedidos e orçamentos'
            ],
            [
                'name' => 'delete_pre_orders',
                'description' => 'Permite excluir orçamentos'
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }
    }
}

