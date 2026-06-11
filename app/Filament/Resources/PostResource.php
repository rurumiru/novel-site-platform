<?php
namespace App\Filament\Resources;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Schema;

class PostResource extends Resource {
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Блог';
    protected static ?string $navigationGroup = 'Сайт';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel = 'Запись';
    protected static ?string $pluralModelLabel = 'Блог';

    public static function form(Forms\Form $form): Forms\Form {
        $schema = [
            Forms\Components\TextInput::make('title')->required()->label('Заголовок'),
            Forms\Components\FileUpload::make('image')->image()->disk('s3')->directory('blog')->visibility('public'),
            Forms\Components\MarkdownEditor::make('content')
                ->fileAttachmentsDisk('s3')
                ->fileAttachmentsDirectory('blog_media')
                ->fileAttachmentsVisibility('public')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_published')->default(true),
        ];
        if (Schema::hasColumn('posts', 'is_recruitment')) {
            $schema[] = Forms\Components\Toggle::make('is_recruitment')
                ->label('Поиск модераторов/работников')
                ->helperText('Отметьте, если это объявление о наборе модераторов или администраторов.')
                ->default(false);
        }
        return $form->schema($schema);
    }

    public static function table(Tables\Table $table): Tables\Table {
        $columns = [
            Tables\Columns\ImageColumn::make('image')->disk('s3'),
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('views')->label('Просмотры'),
            Tables\Columns\ToggleColumn::make('is_published'),
        ];
        if (Schema::hasColumn('posts', 'is_recruitment')) {
            $columns[] = Tables\Columns\IconColumn::make('is_recruitment')
                ->label('Набор')
                ->boolean()
                ->trueIcon('heroicon-o-user-group')
                ->falseIcon('heroicon-o-minus');
        }
        $columns[] = Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y');
        return $table->columns($columns)->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
