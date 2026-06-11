<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model {
    protected $fillable = ['user_id', 'category', 'subject', 'body', 'status'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany {
        return $this->hasMany(SupportReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function getCategoryLabelAttribute(): string {
        return match($this->category) {
            'reader' => 'Читатель',
            'author' => 'Автор',
            'bug'    => 'Ошибка',
            default  => 'Другое',
        };
    }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'open'     => 'Открыта',
            'answered' => 'Отвечено',
            'closed'   => 'Закрыта',
            default    => 'Открыта',
        };
    }

    public function getAgeHoursAttribute(): float {
        return $this->created_at->diffInMinutes(now()) / 60;
    }

    public function getAgePercentAttribute(): int {
        return (int) min(100, ($this->ageHours / 24) * 100);
    }
}
