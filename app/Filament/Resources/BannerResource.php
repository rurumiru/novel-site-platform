<?php
namespace App\Filament\Resources;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource {
    protected static ?string $model = Banner::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Баннеры на главной';
    protected static ?string $navigationGroup = 'Сайт';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel = 'Баннер';
    protected static ?string $pluralModelLabel = 'Баннеры';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Изображения')
                ->description('Десктоп — широкий формат 1920×640. Мобайл — квадратный 800×900 (опционально, иначе используется десктопный с центром).')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Десктоп (1920×640)')
                        ->image()->imageEditor()
                        ->disk('s3')->directory('banners')->visibility('public')
                        ->required()
                        ->columnSpan(1),
                    Forms\Components\FileUpload::make('image_path_mobile')
                        ->label('Мобайл (800×900, необязательно)')
                        ->image()->imageEditor()
                        ->disk('s3')->directory('banners')->visibility('public')
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Контент')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('badge')
                        ->label('Бейдж сверху')
                        ->placeholder('Новинка · Май')
                        ->maxLength(40)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('cta_text')
                        ->label('Текст кнопки')
                        ->placeholder('Читать сейчас')
                        ->maxLength(60)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('title')
                        ->label('Заголовок')
                        ->maxLength(120)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('subtitle')
                        ->label('Подзаголовок (одна строка)')
                        ->maxLength(120)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Описание (1–2 предложения)')
                        ->rows(2)
                        ->maxLength(280)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('link')
                        ->label('Ссылка')
                        ->placeholder('/novel/123 или https://...')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Оформление')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('theme')
                        ->label('Тема текста')
                        ->options(['dark' => 'Тёмный фон (светлый текст)', 'light' => 'Светлый фон (тёмный текст)'])
                        ->default('dark')
                        ->native(false),
                    Forms\Components\Select::make('align')
                        ->label('Выравнивание контента')
                        ->options(['left' => 'Слева', 'center' => 'По центру', 'right' => 'Справа'])
                        ->default('left')
                        ->native(false),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true)
                        ->inline(false),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Порядок (меньше = раньше)')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image_path')->disk('s3')->label('Превью')->width(140)->height(56),
            Tables\Columns\TextColumn::make('title')->label('Заголовок')->limit(40)->searchable(),
            Tables\Columns\TextColumn::make('subtitle')->label('Подзаголовок')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('cta_text')->label('Кнопка')->placeholder('—'),
            Tables\Columns\IconColumn::make('is_active')->boolean()->label('Активен'),
            Tables\Columns\TextColumn::make('sort_order')->label('Порядок')->sortable(),
        ])
        ->reorderable('sort_order')
        ->defaultSort('sort_order')
        ->filters([
            Tables\Filters\TernaryFilter::make('is_active')->label('Активен'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
