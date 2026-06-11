<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PromoCode extends Model {
    protected $fillable = [
        'code', 'type', 'value', 'novel_id', 'chapters_count',
        'max_uses', 'max_uses_per_user', 'uses_count',
        'is_active', 'starts_at', 'expires_at', 'description',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PromoCode $promo) {
            $promo->code = strtoupper(trim($promo->code));
        });
    }

    public function novel(): BelongsTo { return $this->belongsTo(Novel::class); }
    public function usages(): HasMany  { return $this->hasMany(PromoCodeUsage::class); }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) return false;
        return true;
    }

    public function canBeUsedBy(User $user): bool
    {
        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        return $userUsageCount < ($this->max_uses_per_user ?? 1);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'balance'       => 'Баланс',
            'discount'      => 'Скидка',
            'free_novel'    => 'Бесплатная новелла',
            'free_chapters' => 'Бесплатные главы',
            default         => $this->type,
        };
    }

    public function getEffectLabelAttribute(): string
    {
        return match ($this->type) {
            'balance'       => "+{$this->value} ₽",
            'discount'      => "-{$this->value}%",
            'free_novel'    => $this->novel ? "«{$this->novel->title}»" : '—',
            'free_chapters' => $this->chapters_count . ' гл.' . ($this->novel ? " — «{$this->novel->title}»" : ' (любая)'),
            default         => '—',
        };
    }
}
