<?php
namespace App\Filament\Resources;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class SubscriptionResource extends Resource {
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Подписки';
    protected static ?string $navigationGroup = 'Финансы';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel = 'Подписка';
    protected static ?string $pluralModelLabel = 'Подписки';

    protected static bool $shouldRegisterNavigation = false;
    public static function canViewAny(): bool { return false; }

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->label('Пользователь'),
            Forms\Components\Select::make('type')
                ->options([
                    'plus'          => '⭐ Plus (глобальная)',
                    'single'        => '📖 Новелла',
                    'author_bundle' => '📚 Пакет автора',
                ])
                ->default('single')
                ->required()
                ->live()
                ->label('Тип'),
            Forms\Components\Select::make('plus_plan_id')
                ->relationship('plusPlan', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->visible(fn (Forms\Get $get) => $get('type') === 'plus')
                ->label('Тариф Plus'),
            Forms\Components\Select::make('novel_id')
                ->relationship('novel', 'title')
                ->searchable()
                ->preload()
                ->nullable()
                ->visible(fn (Forms\Get $get) => $get('type') === 'single')
                ->label('Новелла'),
            Forms\Components\Select::make('author_id')
                ->relationship('author', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->visible(fn (Forms\Get $get) => $get('type') === 'author_bundle')
                ->label('Автор (пакет)'),
            Forms\Components\TextInput::make('amount_paid')->numeric()->prefix('₽')->default(0)->required()->label('Сумма'),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Ожидает',
                    'active' => 'Активна',
                    'expired' => 'Истекла',
                    'rejected' => 'Отклонена',
                ])
                ->default('active')
                ->required()
                ->label('Статус'),
            Forms\Components\DateTimePicker::make('expires_at')
                ->nullable()
                ->label('Истекает')
                ->helperText('Пусто = бессрочная'),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('uid')->label('ID')->copyable()->searchable(),
            Tables\Columns\TextColumn::make('user.name')->label('Пользователь')->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('plusPlan.name')->label('Тариф Plus')->toggleable(),
            Tables\Columns\TextColumn::make('novel.title')->label('Новелла')->searchable()->limit(30)->toggleable(),
            Tables\Columns\TextColumn::make('author.name')->label('Автор (пакет)')->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('type')
                ->label('Тип')
                ->badge()
                ->formatStateUsing(fn ($state) => match($state) {
                    'plus'          => '⭐ Plus',
                    'single'        => '📖 Новелла',
                    'author_bundle' => '📚 Пакет автора',
                    default         => $state ?? '—',
                })
                ->color(fn ($state) => match($state) {
                    'plus'          => 'success',
                    'author_bundle' => 'warning',
                    default         => 'info',
                }),
            Tables\Columns\TextColumn::make('amount_paid')->money('rub')->label('Сумма')->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'pending' => 'warning',
                    'rejected' => 'danger',
                    'expired' => 'gray',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'active' => 'Активна',
                    'pending' => 'Ожидает',
                    'rejected' => 'Отклонена',
                    'expired' => 'Истекла',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('expires_at')->dateTime('d.m.Y')->label('Истекает')->toggleable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i')->label('Дата заявки')->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->actions([
            Action::make('approve')
                ->label('Подтвердить')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Subscription $record) => $record->status === 'pending')
                ->action(function (Subscription $record) {
                    if ($record->type === 'plus' && $record->plusPlan) {
                        $days = (int) $record->plusPlan->days;
                        $user = $record->user;
                        $base = ($user->premium_until && $user->premium_until->isFuture())
                            ? $user->premium_until
                            : now();
                        $until = $base->copy()->addDays($days);

                        $user->update([
                            'is_premium'    => true,
                            'premium_until' => $until,
                        ]);

                        $record->update([
                            'status'     => 'active',
                            'expires_at' => $until,
                        ]);

                        Notification::make()
                            ->title('Plus активирован до ' . $until->format('d.m.Y'))
                            ->success()->send();
                    } else {
                        $record->update(['status' => 'active']);
                        Notification::make()->title('Подписка активирована')->success()->send();
                    }
                }),

            Action::make('reject')
                ->label('Отклонить')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn (Subscription $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function (Subscription $record) {
                    $record->update(['status' => 'rejected']);
                    Notification::make()->title('Заявка отклонена')->danger()->send();
                }),

            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
