<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Popular tabela states
        DB::table('states')->insert([
            ['name' => 'São Paulo', 'abbreviation' => 'SP', 'icms' => 18.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rio de Janeiro', 'abbreviation' => 'RJ', 'icms' => 20.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Minas Gerais', 'abbreviation' => 'MG', 'icms' => 18.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rio Grande do Sul', 'abbreviation' => 'RS', 'icms' => 17.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paraná', 'abbreviation' => 'PR', 'icms' => 18.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela users
        DB::table('users')->insert([
            ['name' => 'Admin Teste', 'email' => 'admin@teste.com', 'password' => Hash::make('123456'), 'admin' => true, 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'João Silva', 'email' => 'joao@teste.com', 'password' => Hash::make('123456'), 'admin' => false, 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maria Santos', 'email' => 'maria@teste.com', 'password' => Hash::make('123456'), 'admin' => false, 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pedro Costa', 'email' => 'pedro@teste.com', 'password' => Hash::make('123456'), 'admin' => false, 'enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ana Oliveira', 'email' => 'ana@teste.com', 'password' => Hash::make('123456'), 'admin' => false, 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela customers
        DB::table('customers')->insert([
            ['name' => 'Empresa ABC Ltda', 'email' => 'contato@abc.com.br', 'phone' => '(11) 98765-4321', 'cnpj' => '12.345.678/0001-90', 'cpf' => null, 'address' => 'Rua das Flores, 123 - São Paulo/SP', 'state_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Metalúrgica XYZ', 'email' => 'vendas@xyz.com.br', 'phone' => '(21) 91234-5678', 'cnpj' => '98.765.432/0001-10', 'cpf' => null, 'address' => 'Av. Industrial, 456 - Rio de Janeiro/RJ', 'state_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carlos Ferreira', 'email' => 'carlos@email.com', 'phone' => '(31) 99876-5432', 'cnpj' => null, 'cpf' => '123.456.789-01', 'address' => 'Rua dos Mineiros, 789 - Belo Horizonte/MG', 'state_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Indústria Sul Ltda', 'email' => 'comercial@sul.com.br', 'phone' => '(51) 93456-7890', 'cnpj' => '11.222.333/0001-44', 'cpf' => null, 'address' => 'Av. Gaúcha, 321 - Porto Alegre/RS', 'state_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'José Construções', 'email' => 'jose@construcoes.com', 'phone' => '(41) 94567-8901', 'cnpj' => '55.666.777/0001-88', 'cpf' => null, 'address' => 'Rua Paranaense, 654 - Curitiba/PR', 'state_id' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela materials
        DB::table('materials')->insert([
            ['name' => 'Aço Carbono', 'specific_weight' => 7.850000, 'price_kg' => 5.50, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aço Inoxidável 304', 'specific_weight' => 8.000000, 'price_kg' => 15.75, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alumínio 6061', 'specific_weight' => 2.700000, 'price_kg' => 12.30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cobre', 'specific_weight' => 8.960000, 'price_kg' => 28.90, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bronze', 'specific_weight' => 8.730000, 'price_kg' => 22.15, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela sheets
        DB::table('sheets')->insert([
            ['material_id' => 1, 'thickness' => 3.00, 'name' => 'Chapa Aço Carbono 3mm', 'width' => 1000.00, 'length' => 2000.00, 'created_at' => now(), 'updated_at' => now()],
            ['material_id' => 1, 'thickness' => 6.00, 'name' => 'Chapa Aço Carbono 6mm', 'width' => 1500.00, 'length' => 3000.00, 'created_at' => now(), 'updated_at' => now()],
            ['material_id' => 2, 'thickness' => 2.00, 'name' => 'Chapa Inox 304 2mm', 'width' => 1200.00, 'length' => 2400.00, 'created_at' => now(), 'updated_at' => now()],
            ['material_id' => 3, 'thickness' => 5.00, 'name' => 'Chapa Alumínio 5mm', 'width' => 1000.00, 'length' => 2000.00, 'created_at' => now(), 'updated_at' => now()],
            ['material_id' => 4, 'thickness' => 1.50, 'name' => 'Chapa Cobre 1.5mm', 'width' => 800.00, 'length' => 1600.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela bars
        DB::table('bars')->insert([
            ['name' => 'Barra Redonda Aço 20mm', 'weight' => 2.47, 'length' => 6000.00, 'price_kg' => 6.20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Barra Quadrada Aço 25mm', 'weight' => 3.85, 'length' => 6000.00, 'price_kg' => 6.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Barra Chata Aço 50x10mm', 'weight' => 3.93, 'length' => 6000.00, 'price_kg' => 5.80, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Barra Redonda Inox 15mm', 'weight' => 1.85, 'length' => 3000.00, 'price_kg' => 18.50, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Barra Alumínio 30mm', 'weight' => 1.20, 'length' => 4000.00, 'price_kg' => 14.25, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela components
        DB::table('components')->insert([
            ['name' => 'Parafuso Allen M8x20', 'specification' => 'Parafuso sextavado interno DIN 912', 'unit_value' => 0.85, 'supplier' => 'Parafusos Brasil Ltda', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Porca Sextavada M8', 'specification' => 'Porca sextavada DIN 934', 'unit_value' => 0.25, 'supplier' => 'Fixadores SA', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Arruela Lisa M8', 'specification' => 'Arruela lisa DIN 125', 'unit_value' => 0.10, 'supplier' => 'Arruelas & Cia', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Solda Eletrodo E6013', 'specification' => 'Eletrodo revestido 3.25mm', 'unit_value' => 12.50, 'supplier' => 'Soldas Premium', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tinta Primer Anticorrosiva', 'specification' => 'Primer epóxi 1L', 'unit_value' => 28.90, 'supplier' => 'Tintas Industriais', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela processes
        DB::table('processes')->insert([
            ['title' => 'Corte a Plasma', 'content' => 'Corte de chapas metálicas utilizando plasma', 'value_per_minute' => 2.50, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Soldagem MIG', 'content' => 'Soldagem com arame sólido em atmosfera inerte', 'value_per_minute' => 4.20, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Dobra em Prensa', 'content' => 'Dobra de chapas em prensa hidráulica', 'value_per_minute' => 3.80, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Furação', 'content' => 'Furação com broca helicoidal', 'value_per_minute' => 1.75, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Acabamento', 'content' => 'Lixamento e acabamento superficial', 'value_per_minute' => 2.10, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela groups
        DB::table('groups')->insert([
            ['name' => 'Grupo Administradores', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grupo Vendas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grupo Produção', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grupo Qualidade', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grupo Logística', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela group_user (relacionamento many-to-many)
        DB::table('group_user')->insert([
            ['group_id' => 1, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 2, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 2, 'user_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 3, 'user_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 4, 'user_id' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela orders
        DB::table('orders')->insert([
            ['final_value' => 15000.00, 'customer_id' => 1, 'type' => 'order', 'delivery_type' => 'CIF', 'delivery_value' => 500.00, 'markup' => 1.250, 'delivery_date' => '2025-08-15 10:00:00', 'estimated_delivery_date' => '15 dias úteis', 'payment_obs' => 'Pagamento em 30 dias', 'created_at' => now(), 'updated_at' => now()],
            ['final_value' => null, 'customer_id' => 2, 'type' => 'pre_order', 'delivery_type' => 'FOB', 'delivery_value' => null, 'markup' => 1.300, 'delivery_date' => null, 'estimated_delivery_date' => '20 dias úteis', 'payment_obs' => 'À vista com desconto', 'created_at' => now(), 'updated_at' => now()],
            ['final_value' => 8500.00, 'customer_id' => 3, 'type' => 'order', 'delivery_type' => 'CIF', 'delivery_value' => 350.00, 'markup' => 1.200, 'delivery_date' => '2025-08-20 14:30:00', 'estimated_delivery_date' => '10 dias úteis', 'payment_obs' => 'Pagamento parcelado', 'created_at' => now(), 'updated_at' => now()],
            ['final_value' => null, 'customer_id' => 4, 'type' => 'pre_order', 'delivery_type' => 'FOB', 'delivery_value' => null, 'markup' => 1.180, 'delivery_date' => null, 'estimated_delivery_date' => '25 dias úteis', 'payment_obs' => 'Aguardando aprovação', 'created_at' => now(), 'updated_at' => now()],
            ['final_value' => 22000.00, 'customer_id' => 5, 'type' => 'order', 'delivery_type' => 'CIF', 'delivery_value' => 800.00, 'markup' => 1.400, 'delivery_date' => '2025-09-01 08:00:00', 'estimated_delivery_date' => '30 dias úteis', 'payment_obs' => 'Pagamento via boleto', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Popular tabela mercosur_common_nomenclatures
        DB::table('mercosur_common_nomenclatures')->insert([
            ['code' => '7208.10.00', 'ipi' => 5.00, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '7208.25.00', 'ipi' => 5.00, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '7219.11.00', 'ipi' => 8.00, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '7601.10.10', 'ipi' => 3.00, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '7403.11.00', 'ipi' => 7.50, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
