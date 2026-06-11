<?php

namespace Database\Seeders;

use App\Models\PlusPlan;
use Illuminate\Database\Seeder;

class PlusPlanSeeder extends Seeder {
    public function run(): void {
        $plans = [
            [
                'key'         => 'monthly',
                'name'        => 'Ежемесячная подписка',
                'short_label' => 'самая выгодная',
                'days'        => 30,
                'price'       => null,
                'is_recurring'=> true,
                'discount_percent' => 12,
                'sort_order'  => 1,
                'features'    => [
                    'Доступ ко всем Plus-новеллам без ограничений',
                    'Автоматическое продление каждые 30 дней',
                    'Хорошие скидки в зависимости от срока подписки',
                    'Поддержка TTS-озвучки (в приложении)',
                ],
            ],
            [
                'key'         => 'days30',
                'name'        => '30 дней',
                'short_label' => 'разовая оплата',
                'days'        => 30,
                'price'       => null,
                'is_recurring'=> false,
                'discount_percent' => 0,
                'sort_order'  => 2,
                'features'    => [
                    'Доступ ко всем Plus-новеллам на 30 дней',
                    'Без автопродления',
                    'Подходит для разовой оплаты по счёту',
                ],
            ],
            [
                'key'         => 'days180',
                'name'        => '180 дней',
                'short_label' => 'максимальная выгода',
                'days'        => 180,
                'price'       => null,
                'is_recurring'=> false,
                'discount_percent' => 25,
                'sort_order'  => 3,
                'features'    => [
                    'Доступ ко всем Plus-новеллам на 180 дней (6 месяцев)',
                    'Скидка по сравнению с покупкой 6×30 дней',
                    'Идеально для долгосрочного чтения',
                ],
            ],
        ];

        foreach ($plans as $p) {
            PlusPlan::updateOrCreate(['key' => $p['key']], $p);
        }

        $this->command?->info('PlusPlanSeeder: добавлены/обновлены 3 тарифа (цены = null, заполнить позже в Filament).');
    }
}
