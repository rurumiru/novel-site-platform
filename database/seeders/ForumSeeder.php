<?php

namespace Database\Seeders;

use App\Forum\Models\Category;
use App\Forum\Models\Section;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name' => 'Новеллы',
                'slug' => 'novels',
                'icon' => 'fa-solid fa-book-open',
                'color' => '#2f6df0',
                'description' => 'Обсуждения произведений и глав.',
                'sort_order' => 1,
                'sections' => [
                    ['name' => 'Обсуждения новелл',  'slug' => 'novel-talk',   'icon' => 'fa-solid fa-comments', 'create_policy' => 'auth'],
                    ['name' => 'Обсуждения глав',    'slug' => 'chapter-talk', 'icon' => 'fa-solid fa-bookmark', 'create_policy' => 'auth'],
                    ['name' => 'Рекомендации',       'slug' => 'recommendations', 'icon' => 'fa-solid fa-thumbs-up', 'create_policy' => 'auth'],
                ],
            ],
            [
                'name' => 'Сообщество',
                'slug' => 'community',
                'icon' => 'fa-solid fa-users',
                'color' => '#5b8def',
                'description' => 'Общение, знакомства, оффтоп.',
                'sort_order' => 2,
                'sections' => [
                    ['name' => 'Общий чат',     'slug' => 'general',  'icon' => 'fa-solid fa-comments', 'create_policy' => 'auth'],
                    ['name' => 'Творчество',    'slug' => 'creative', 'icon' => 'fa-solid fa-feather', 'create_policy' => 'auth'],
                    ['name' => 'Авторы и переводчики', 'slug' => 'authors', 'icon' => 'fa-solid fa-pen-nib', 'create_policy' => 'role:author'],
                ],
            ],
            [
                'name' => 'Помощь и сайт',
                'slug' => 'help',
                'icon' => 'fa-solid fa-circle-question',
                'color' => '#22c55e',
                'description' => 'Вопросы по сайту, баг-репорты, предложения.',
                'sort_order' => 3,
                'sections' => [
                    ['name' => 'Вопросы',     'slug' => 'questions', 'icon' => 'fa-solid fa-circle-question', 'create_policy' => 'auth'],
                    ['name' => 'Предложения', 'slug' => 'ideas',     'icon' => 'fa-solid fa-lightbulb',       'create_policy' => 'auth'],
                    ['name' => 'Баг-репорты', 'slug' => 'bugs',      'icon' => 'fa-solid fa-bug',             'create_policy' => 'auth'],
                ],
            ],
        ];

        foreach ($tree as $i => $cat) {
            $sections = $cat['sections'];
            unset($cat['sections']);
            $cat['is_active'] = true;

            $category = Category::firstOrCreate(['slug' => $cat['slug']], $cat);

            foreach ($sections as $j => $sec) {
                Section::firstOrCreate(
                    ['category_id' => $category->id, 'slug' => $sec['slug']],
                    array_merge($sec, [
                        'category_id' => $category->id,
                        'sort_order'  => $j + 1,
                        'is_active'   => true,
                    ]),
                );
            }
        }
    }
}
