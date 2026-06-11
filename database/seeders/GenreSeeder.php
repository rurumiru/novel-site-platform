<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Genre;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder {
    public function run(): void {
        $genres = [
            'Фэнтези', 'Романтика', 'Приключения', 'Драма', 'Комедия', 'Трагедия',
            'Детектив', 'Триллер', 'Ужасы', 'Мистика', 'Научная фантастика', 'Постапокалипсис',
            'Исекай', 'Сёнэн', 'Сёдзё', 'Сёнэн-ай', 'Юри', 'Яой', 'Харем', 'Роман',
            'Исторический роман', 'Боевые искусства', 'Меха', 'Повседневность', 'Школа',
            'Городское фэнтези', 'Тёмное фэнтези', 'ЛитРПГ', 'Слайс оф лайф',
        ];
        foreach ($genres as $name) {
            Genre::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
