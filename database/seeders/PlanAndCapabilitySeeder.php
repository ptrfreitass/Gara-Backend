<?php

namespace Database\Seeders;

use App\Models\Capability;
use App\Models\PlanCapability;
use App\Enums\PlanType;
use Illuminate\Database\Seeder;

class PlanAndCapabilitySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Definir as capabilities do sistema
        $capabilities = [
            ['name' => 'finance.view',   'description' => 'Ver extrato e saldo'],
            ['name' => 'finance.create', 'description' => 'Lançar transações'],
            ['name' => 'finance.edit',   'description' => 'Editar transações'],
            ['name' => 'finance.delete', 'description' => 'Deletar transações'],
            ['name' => 'finance.export', 'description' => 'Exportar relatórios'],
        ];

        foreach ($capabilities as $cap) {
            Capability::firstOrCreate(['name' => $cap['name']], $cap);
        }

        // 2. Carregar todas de uma vez (evita N queries no loop)
        $capabilityMap = Capability::pluck('id', 'name'); // ['finance.view' => 1, ...]

        // 3. Mapear capabilities por plano
        $plans = [
            PlanType::Free->value    => ['finance.view', 'finance.create', 'finance.edit', 'finance.delete'],
            PlanType::Plus->value    => ['finance.view', 'finance.create', 'finance.edit', 'finance.delete'],
            PlanType::Premium->value => ['finance.view', 'finance.create', 'finance.edit', 'finance.delete', 'finance.export'],
        ];

        foreach ($plans as $planType => $capNames) {
            foreach ($capNames as $capName) {
                PlanCapability::firstOrCreate([
                    'plan_type'     => $planType,
                    'capability_id' => $capabilityMap[$capName],
                ]);
            }
        }
    }
}