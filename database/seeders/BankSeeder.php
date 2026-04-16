<?php
// database/seeders/BankSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['name' => 'Banco do Brasil',       'code' => '001'],
            ['name' => 'Bradesco',               'code' => '237'],
            ['name' => 'Caixa Econômica Federal','code' => '104'],
            ['name' => 'Itaú',                   'code' => '341'],
            ['name' => 'Santander',              'code' => '033'],
            ['name' => 'Nubank',                 'code' => '260'],
            ['name' => 'Inter',                  'code' => '077'],
            ['name' => 'C6 Bank',                'code' => '336'],
            ['name' => 'BTG Pactual',            'code' => '208'],
            ['name' => 'XP Investimentos',       'code' => '102'],
            ['name' => 'Sicoob',                 'code' => '756'],
            ['name' => 'Sicredi',                'code' => '748'],
            ['name' => 'Safra',                  'code' => '422'],
            ['name' => 'Original',               'code' => '212'],
            ['name' => 'PicPay',                 'code' => '380'],
            ['name' => 'Mercado Pago',           'code' => '323'],
            ['name' => 'PagBank',                'code' => '290'],
            ['name' => 'Neon',                   'code' => '735'],
            ['name' => 'Outro',                  'code' => null],
        ];

        foreach ($banks as $bank) {
            DB::table('banks')->updateOrInsert(
                ['code' => $bank['code']],
                array_merge($bank, [
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}