<?php
namespace App\Filament\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class ManageSite extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Настройки сайта';
    protected static ?string $title = 'Глобальные настройки';
    protected static string $view = 'filament.pages.manage-site';
    public ?array $data = [];

    public function mount(): void {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $jsonFields = ['navigation_menu', 'social_links', 'homepage_layout', 'download_formats'];
        foreach ($jsonFields as $field) {
            if (isset($settings[$field])) $settings[$field] = json_decode($settings[$field], true);
        }
        if (empty($settings['homepage_layout'])) {
            $settings['homepage_layout'] = [
                ['type' => 'block', 'title' => 'Популярное', 'sort' => 'views', 'period' => 'month', 'layout' => 'grid', 'rows' => 1],
                ['type' => 'updates', 'title' => 'Обновления'],
            ];
        }
        if (empty($settings['download_formats'])) {
            $settings['download_formats'] = ['txt', 'fb2', 'epub'];
        }
        foreach (['show_home_banner', 'downloads_enabled', 'downloads_require_auth', 'guest_chapter_limit_enabled', 'require_age_confirmation', 'hide_adult_for_guests'] as $boolKey) {
            if (!isset($settings[$boolKey])) {
                $settings[$boolKey] = true;
            } else {
                $settings[$boolKey] = filter_var($settings[$boolKey], FILTER_VALIDATE_BOOLEAN);
            }
        }
        $this->form->fill($settings);
    }

    public function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\Tabs::make('Settings')->tabs([
                Forms\Components\Tabs\Tab::make('Главная')->schema([
                    Forms\Components\Repeater::make('homepage_layout')
                        ->label('Блоки главной')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->label('Тип')
                                ->options([
                                    'block' => 'Подборка',
                                    'updates' => 'Обновления',
                                    'top_rated' => 'Лучшие по рейтингу',
                                    'random' => 'Случайные',
                                    'schedule' => 'Расписание выхода',
                                    'continue' => 'Продолжить чтение (для залогиненных)',
                                    'favorites' => 'Обновления избранного (для залогиненных)',
                                    'banner_custom' => 'Баннер (HTML)',
                                ])
                                ->required()
                                ->live(),
                            
                            Forms\Components\TextInput::make('title')->label('Заголовок')->hidden(fn (Forms\Get $get) => $get('type') === 'banner_custom'),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('sort')->label('Сортировка')->options(['views' => 'Просмотры', 'rating' => 'Рейтинг', 'newest' => 'Новинки'])->default('views')->visible(fn (Forms\Get $get) => $get('type') === 'block'),
                                Forms\Components\Select::make('period')->label('Период')->options(['day' => 'День', 'month' => 'Месяц', 'all' => 'Все время'])->default('month')->visible(fn (Forms\Get $get) => $get('type') === 'block'),
                                Forms\Components\Select::make('layout')->label('Вид')->options(['grid' => 'Сетка', 'list' => 'Список', 'swiper' => 'Свайпер'])->default('grid')->visible(fn (Forms\Get $get) => $get('type') === 'block'),
                                Forms\Components\Select::make('rows')->label('Рядов')->options([1 => '1 ряд', 2 => '2 ряда'])->default(1)->visible(fn (Forms\Get $get) => $get('type') === 'block' && $get('layout') !== 'swiper'),
                            ]),

                            Forms\Components\Textarea::make('custom_content')->label('HTML код')->visible(fn (Forms\Get $get) => $get('type') === 'banner_custom'),
                        ])->reorderable()->collapsible()
                ]),
                Forms\Components\Tabs\Tab::make('Основное')->schema([
                    Forms\Components\TextInput::make('site_name')->label('Название сайта')->required(),
                    Forms\Components\Textarea::make('footer_text')->label('Текст футера'),
                    Forms\Components\TextInput::make('profile_banner_height')->label('Высота баннера профиля')->numeric()->default(250)->suffix('px'),
                    Forms\Components\Toggle::make('show_home_banner')
                        ->label('Показывать главный баннер на главной странице')
                        ->default(true)
                        ->helperText('Отключите, чтобы скрыть слайдер баннеров на главной.'),
                ]),
                Forms\Components\Tabs\Tab::make('Модерация')->schema([
                    Forms\Components\TextInput::make('moderation_password')
                        ->label('Пароль модерации')
                        ->password()
                        ->revealable()
                        ->helperText('Пароль для входа в панель модератора (для роли moderator). super_admin заходит без пароля.'),
                ]),
                Forms\Components\Tabs\Tab::make('Доступ')->schema([
                    Forms\Components\Toggle::make('guest_chapter_limit_enabled')
                        ->label('Ограничить главы для гостей')
                        ->helperText('Если включено, незарегистрированные пользователи видят только первые N глав.')
                        ->default(false)
                        ->live(),
                    Forms\Components\TextInput::make('guest_chapter_limit')
                        ->label('Макс. глав для гостей')
                        ->numeric()
                        ->default(3)
                        ->minValue(0)
                        ->maxValue(100)
                        ->helperText('Сколько первых глав могут читать незарегистрированные пользователи.')
                        ->visible(fn (Forms\Get $get) => $get('guest_chapter_limit_enabled')),
                    Forms\Components\Toggle::make('require_age_confirmation')
                        ->label('Требовать подтверждение возраста')
                        ->helperText('Если включено, 18+ контент будет доступен только пользователям с заполненной датой рождения и полом в профиле.')
                        ->default(true),
                    Forms\Components\TextInput::make('min_age_for_adult')
                        ->label('Минимальный возраст для 18+ контента')
                        ->numeric()
                        ->default(18)
                        ->minValue(16)
                        ->maxValue(21)
                        ->helperText('Возраст, необходимый для доступа к контенту с пометкой 18+.'),
                    Forms\Components\Toggle::make('hide_adult_for_guests')
                        ->label('Скрывать 18+ новеллы от гостей')
                        ->helperText('Новеллы с пометкой 18+ не будут видны незарегистрированным пользователям.')
                        ->default(true),
                ]),
                Forms\Components\Tabs\Tab::make('Скачивание')->schema([
                    Forms\Components\Toggle::make('downloads_enabled')
                        ->label('Разрешить скачивание файлов')
                        ->helperText('Глобальное отключение — кнопки TXT/FB2/EPUB пропадут со страниц новелл.')
                        ->default(true),
                    Forms\Components\Toggle::make('downloads_require_auth')
                        ->label('Требовать авторизацию для скачивания')
                        ->helperText('Если выключено, гости смогут скачивать файлы без входа.')
                        ->default(true),
                    Forms\Components\CheckboxList::make('download_formats')
                        ->label('Доступные форматы')
                        ->options([
                            'txt'  => 'TXT (текст)',
                            'fb2'  => 'FB2 (FictionBook)',
                            'epub' => 'EPUB (электронная книга)',
                        ])
                        ->default(['txt', 'fb2', 'epub'])
                        ->columns(3)
                        ->helperText('Только отмеченные форматы будут доступны для скачивания. Платные/закрытые главы никогда не включаются в файл.'),
                ]),
            ])->columnSpanFull()
        ])->statePath('data');
    }

    public function save(): void {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            if (is_array($value)) $value = json_encode($value);
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Notification::make()->title('Сохранено')->success()->send();
    }
    protected function getFormActions(): array { return [Action::make('save')->label('Сохранить')->submit('save')]; }
}
