<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Review;
use App\Models\User;
use App\Models\Volume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = config('demo.admin_email', 'admin@demo.iiiba.ru');
        $adminPass  = config('demo.admin_password', 'demo12345');

        // Готовые демо-аккаунты под все роли. Все помечены is_demo=true (не удаляются
        // очисткой) и заранее подтверждены (email_verified_at) — вход работает сразу.
        $demoUsers = [
            ['email' => $adminEmail,                  'name' => 'Демо Админ',      'username' => 'demo_admin',   'password' => $adminPass,   'role' => 'owner'],
            ['email' => 'moderator@demo.iiiba.ru',    'name' => 'Демо Модератор',  'username' => 'demo_mod',     'password' => 'demo12345',  'role' => 'moderator'],
            ['email' => 'author@demo.iiiba.ru',       'name' => 'Демо Автор',      'username' => 'demo_author',  'password' => 'demo12345',  'role' => 'author'],
            ['email' => 'reader@demo.iiiba.ru',       'name' => 'Демо Читатель',   'username' => 'demo_reader',  'password' => 'demo12345',  'role' => null],
            ['email' => 'reader2@demo.iiiba.ru',      'name' => 'Демо Читатель 2', 'username' => 'demo_reader2', 'password' => 'demo12345',  'role' => null],
        ];

        $created = [];
        foreach ($demoUsers as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'              => $u['name'],
                    'username'          => $u['username'],
                    'password'          => Hash::make($u['password']),
                    'email_verified_at' => now(),
                    'is_demo'           => true,
                ]
            );
            if ($u['role']) {
                $this->assignRole($user, $u['role']);
            }
            $created[$u['email']] = $user;
        }

        $admin  = $created[$adminEmail];
        $author = $created['author@demo.iiiba.ru'];

        $novels = [
            [
                'title'       => 'Хроники Безмолвной Башни',
                'description' => 'Демонстрационная новелла. Молодой архивариус находит карту, ведущую к башне, которой нет ни на одной карте мира.',
                'status'      => 'ongoing',
            ],
            [
                'title'       => 'Сад Стеклянных Звёзд',
                'description' => 'Демонстрационная новелла. История о городе, где звёзды можно собирать руками — и о цене, которую за это платят.',
                'status'      => 'ongoing',
            ],
            [
                'title'       => 'Последний Переписчик',
                'description' => 'Демонстрационная новелла. Когда слова начинают исчезать из книг, остаётся лишь один человек, способный их вернуть.',
                'status'      => 'completed',
            ],
        ];

        foreach ($novels as $n) {
            $slug = Str::slug($n['title']);

            $novel = Novel::updateOrCreate(
                ['slug' => $slug],
                [
                    'title'            => $n['title'],
                    'description'      => $n['description'],
                    'status'           => $n['status'],
                    'is_published'     => true,
                    'is_adult'         => false,
                    'is_restricted'    => false,
                    'hide_from_guests' => false,
                    'user_id'          => $author->id,
                    'is_demo'          => true,
                ]
            );

            $volumeId = null;
            if (Schema::hasTable('volumes')) {
                $volume = Volume::updateOrCreate(
                    ['novel_id' => $novel->id, 'title' => 'Том 1'],
                    ['sort_order' => 1, 'is_demo' => true]
                );
                $volumeId = $volume->id;
            }

            for ($i = 1; $i <= 4; $i++) {
                Chapter::updateOrCreate(
                    ['novel_id' => $novel->id, 'slug' => 'glava-' . $i],
                    [
                        'volume_id'    => $volumeId,
                        'title'        => 'Глава ' . $i,
                        'content'      => $this->chapterBody($n['title'], $i),
                        'sort_order'   => $i,
                        'is_published' => true,
                        'published_at' => now()->subDays(4 - $i),
                        'is_demo'      => true,
                    ]
                );
            }
        }

        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'is_demo')) {
            $reviews = [
                ['Добро пожаловать на демо-стенд', 'notice',
                 '<p>Это демонстрационная версия платформы. Регистрируйтесь, пишите обзоры и комментарии, создавайте новеллы — всё работает.</p>'
                 . '<p><strong>Любой добавленный посетителями контент автоматически удаляется каждые 15 минут</strong>, а демо-данные сохраняются.</p>'],
                ['Отличное медитативное фэнтези — рекомендую', 'review',
                 '<p>«Хроники Безмолвной Башни» затягивают с первой главы. Атмосфера, язык, неспешный ритм — то, что нужно для вечернего чтения.</p>'],
            ];
            foreach ($reviews as $i => [$title, $cat, $body]) {
                Review::updateOrCreate(
                    ['title' => $title],
                    [
                        'user_id'          => $admin->id,
                        'category'         => $cat,
                        'body'             => $body,
                        'is_pinned'        => $cat === 'notice',
                        'is_published'     => true,
                        'last_activity_at' => now()->subHours($i),
                        'is_demo'          => true,
                    ]
                );
            }
        }

        $this->command?->info('DemoSeeder: создано ' . count($demoUsers) . ' демо-аккаунтов, ' . count($novels) . ' новелл с главами и демо-обзоры.');
    }

    private function assignRole(User $user, string $role): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }
        try {
            if (\Spatie\Permission\Models\Role::where('name', $role)->exists() && !$user->hasRole($role)) {
                $user->assignRole($role);
            }
        } catch (\Throwable $e) {
            // роли ещё не настроены — пропускаем
        }
    }

    private function chapterBody(string $novelTitle, int $i): string
    {
        return "<p>Это демонстрационная глава №{$i} новеллы «{$novelTitle}».</p>"
             . "<p>Текст приведён исключительно для показа интерфейса чтения. "
             . "Любой контент, добавленный посетителями, автоматически удаляется со стенда.</p>";
    }
}
