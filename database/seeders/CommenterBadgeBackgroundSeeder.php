<?php

namespace Database\Seeders;

use App\Models\CommenterBadge;
use App\Models\CommenterBackground;
use Illuminate\Database\Seeder;

class CommenterBadgeBackgroundSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['title' => 'Новичок', 'icon' => 'fa-seedling', 'color' => 'slate', 'sort_order' => 1],
            ['title' => 'Читатель', 'icon' => 'fa-book-open', 'color' => 'blue', 'sort_order' => 2],
            ['title' => 'Активный', 'icon' => 'fa-fire', 'color' => 'orange', 'sort_order' => 3],
            ['title' => 'Критик', 'icon' => 'fa-comment-dots', 'color' => 'indigo', 'sort_order' => 4],
            ['title' => 'Эксперт', 'icon' => 'fa-star', 'color' => 'amber', 'sort_order' => 5],
            ['title' => 'Легенда', 'icon' => 'fa-crown', 'color' => 'purple', 'sort_order' => 6],
        ];
        foreach ($badges as $b) {
            CommenterBadge::updateOrCreate(
                ['title' => $b['title']],
                ['icon' => $b['icon'], 'color' => $b['color'], 'sort_order' => $b['sort_order']]
            );
        }

        $backgrounds = [
            ['title' => 'Без фона', 'css_class' => '', 'sort_order' => 0],
            ['title' => 'Мягкий', 'css_class' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800', 'sort_order' => 1],
            ['title' => 'Тёплый', 'css_class' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800', 'sort_order' => 2],
            ['title' => 'Мятный', 'css_class' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800', 'sort_order' => 3],
            ['title' => 'Лавандовый', 'css_class' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800', 'sort_order' => 4],
            ['title' => 'Розовый', 'css_class' => 'bg-pink-50 dark:bg-pink-900/20 border-pink-200 dark:border-pink-800', 'sort_order' => 5],
        ];
        foreach ($backgrounds as $bg) {
            CommenterBackground::updateOrCreate(
                ['title' => $bg['title']],
                ['css_class' => $bg['css_class'], 'sort_order' => $bg['sort_order']]
            );
        }
    }
}
