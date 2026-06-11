<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder {
    public function run(): void {
        $admin = User::role(['owner', 'super_admin', 'deputy_admin'])->first()
              ?? User::role('moderator')->first()
              ?? User::first();

        if (!$admin) {
            $this->command?->warn('ReviewSeeder: нет пользователей в БД, пропускаю.');
            return;
        }

        $notices = [
            [
                'title' => 'Обзор / Руководство по правилам работы с разделом «Обзоры»',
                'body'  => '<p>Добро пожаловать в раздел «Обзоры работ»! Здесь читатели делятся своими впечатлениями, рекомендациями и обзорами на новеллы.</p>'
                        . '<h3>Правила публикации</h3>'
                        . '<ul><li>Уважайте других авторов и читателей.</li>'
                        . '<li>Не публикуйте спойлеры без предупреждения <code>&lt;blockquote&gt;Спойлер&lt;/blockquote&gt;</code>.</li>'
                        . '<li>Для продвижения своей работы используйте категорию «Продвижение работ».</li>'
                        . '<li>Запрещены реклама, оскорбления, плагиат.</li></ul>'
                        . '<p>Хороших обзоров!</p>',
            ],
            [
                'title' => 'Отличное руководство по выбору обзора',
                'body'  => '<p>Не знаете, что читать? Полистайте обзоры — там реальные впечатления реальных читателей.</p>'
                        . '<p>Лучшие обзоры закрепляются администраторами и попадают в превью на главной.</p>',
            ],
        ];

        foreach ($notices as $i => $n) {
            Review::firstOrCreate(
                ['title' => $n['title']],
                array_merge($n, [
                    'user_id'          => $admin->id,
                    'category'         => 'notice',
                    'is_pinned'        => true,
                    'is_published'     => true,
                    'last_activity_at' => now()->subDays($i),
                ])
            );
        }

        $samples = [
            ['Лучший роман в моей жизни — «Управляющий бамбуковым лесом»', 'review',
             '<p>Сел читать после рекомендации друга и провалился на пять часов. Главный герой — не «избранный из ниоткуда», а трудяга, и это подкупает.</p><p>Описания природы — отдельная радость. Если ищете медитативное фэнтези — это оно.</p>'],
            ['Если вы собираетесь вырезать мозг, то правильным ответом будет бесстыдство', 'review',
             '<p>Жёсткий, ехидный, с чёрным юмором — но за фасадом стёба прячется неожиданно тонкая драма. Финал бьёт под дых.</p>'],
            ['Прочтите сборник рассказов (я подумал, что его будет легче выбросить)', 'review',
             '<p>Думал — пролистаю за вечер. Зачитался до утра. Каждый рассказ как отдельный фильм.</p>'],
            ['Художник Amta Drift Port', 'promotion',
             '<p>Друзья, открываю новую серию в стиле sci-fi noir. 15 глав уже выложено, дальше — еженедельные обновления.</p><p>Буду благодарен за обратную связь!</p>'],
            ['«Сверхновая работает», массовка любовной комедии — слишком красивые', 'review',
             '<p>Лёгкое чтиво на пару вечеров. Не шедевр, но отдыхать после тяжёлого дня — самое то.</p>'],
        ];

        $user = User::where('id', '!=', $admin->id)->inRandomOrder()->first() ?? $admin;

        foreach ($samples as $i => [$title, $cat, $body]) {
            Review::firstOrCreate(
                ['title' => $title],
                [
                    'user_id'          => $user->id,
                    'category'         => $cat,
                    'title'            => $title,
                    'body'             => $body,
                    'is_pinned'        => false,
                    'is_published'     => true,
                    'views_count'      => rand(20, 200),
                    'recommends_count' => rand(0, 12),
                    'last_activity_at' => now()->subDays($i + 1)->subHours(rand(1, 23)),
                ]
            );
        }

        $this->command?->info('ReviewSeeder: добавлены закрепы и тестовые обзоры.');
    }
}
