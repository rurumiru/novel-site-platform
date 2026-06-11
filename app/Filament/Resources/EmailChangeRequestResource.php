<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailChangeRequestResource\Pages;
use App\Models\EmailChangeRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EmailChangeRequestResource extends Resource
{
    protected static ?string $model = EmailChangeRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Смена email';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel = 'Заявка на смену email';
    protected static ?string $pluralModelLabel = 'Заявки на смену email';

    public static function canAccess(): bool
    {
        return Schema::hasTable('email_change_requests');
    }

    public static function getNavigationBadge(): ?string
    {
        if (!Schema::hasTable('email_change_requests')) return null;
        $count = EmailChangeRequest::where('status', EmailChangeRequest::STATUS_AWAITING_ADMIN)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Заявка')->schema([
                Forms\Components\TextInput::make('user.name')->label('Пользователь')->disabled(),
                Forms\Components\TextInput::make('user.email')->label('Текущий email')->disabled(),
                Forms\Components\TextInput::make('new_email')->label('Новый email')->disabled(),
                Forms\Components\TextInput::make('status')->label('Статус')->disabled(),
                Forms\Components\DateTimePicker::make('code_verified_at')->label('Код подтверждён')->disabled(),
                Forms\Components\DateTimePicker::make('reviewed_at')->label('Решение принято')->disabled(),
                Forms\Components\Textarea::make('admin_note')->label('Комментарий администратора'),
            ])->columns(2),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Пользователь')->searchable(),
                Tables\Columns\TextColumn::make('user.email')->label('Текущий email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('new_email')->label('Новый email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('status')->label('Статус')->badge()->color(fn (string $state): string => match ($state) {
                    EmailChangeRequest::STATUS_PENDING_CODE   => 'gray',
                    EmailChangeRequest::STATUS_AWAITING_ADMIN => 'warning',
                    EmailChangeRequest::STATUS_APPROVED       => 'success',
                    EmailChangeRequest::STATUS_REJECTED       => 'danger',
                    default => 'gray',
                })->formatStateUsing(fn (string $state): string => match ($state) {
                    EmailChangeRequest::STATUS_PENDING_CODE   => 'Ждёт ввода кода',
                    EmailChangeRequest::STATUS_AWAITING_ADMIN => 'Ждёт админа',
                    EmailChangeRequest::STATUS_APPROVED       => 'Одобрено',
                    EmailChangeRequest::STATUS_REJECTED       => 'Отклонено',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('code_verified_at')->label('Код подтв.')->dateTime('d.m.Y H:i')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Подана')->dateTime('d.m.Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('reviewer.name')->label('Решение')->placeholder('—')->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Статус')->options([
                    EmailChangeRequest::STATUS_PENDING_CODE   => 'Ждёт ввода кода',
                    EmailChangeRequest::STATUS_AWAITING_ADMIN => 'Ждёт админа',
                    EmailChangeRequest::STATUS_APPROVED       => 'Одобрено',
                    EmailChangeRequest::STATUS_REJECTED       => 'Отклонено',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (EmailChangeRequest $r) => $r->status === EmailChangeRequest::STATUS_AWAITING_ADMIN)
                    ->requiresConfirmation()
                    ->modalHeading('Одобрить смену email?')
                    ->modalDescription(fn (EmailChangeRequest $r) => 'Email пользователя «' . $r->user?->name . '» будет изменён на ' . $r->new_email . '.')
                    ->action(function (EmailChangeRequest $r) {
                        $exists = User::where('email', $r->new_email)->where('id', '!=', $r->user_id)->exists();
                        if ($exists) {
                            Notification::make()->danger()->title('Email уже занят')->body('Этот адрес заняли пока заявка ждала. Отклоняем.')->send();
                            $r->update([
                                'status'      => EmailChangeRequest::STATUS_REJECTED,
                                'admin_note'  => 'Email уже занят другим пользователем на момент рассмотрения.',
                                'reviewed_by' => Auth::id(),
                                'reviewed_at' => now(),
                            ]);
                            return;
                        }

                        $user = $r->user;
                        if ($user) {
                            $user->email = $r->new_email;
                            $user->email_verified_at = $r->code_verified_at ?? now();
                            $user->save();
                        }

                        $r->update([
                            'status'      => EmailChangeRequest::STATUS_APPROVED,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()->success()->title('Email обновлён')->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (EmailChangeRequest $r) => $r->status === EmailChangeRequest::STATUS_AWAITING_ADMIN)
                    ->form([
                        Forms\Components\Textarea::make('admin_note')->label('Причина отклонения')->required(),
                    ])
                    ->action(function (EmailChangeRequest $r, array $data) {
                        $r->update([
                            'status'      => EmailChangeRequest::STATUS_REJECTED,
                            'admin_note'  => $data['admin_note'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->warning()->title('Заявка отклонена')->send();
                    }),
                Tables\Actions\ViewAction::make()->label('Подробнее'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailChangeRequests::route('/'),
            'view'  => Pages\ViewEmailChangeRequest::route('/{record}'),
        ];
    }
}
