<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlusPlan extends Model {
    protected $guarded = [];

    protected $casts = [
        'features'     => 'array',
        'is_recurring' => 'boolean',
        'is_active'    => 'boolean',
        'price'        => 'decimal:2',
    ];

    public function subscriptions(): HasMany {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeOrdered($q) { return $q->orderBy('sort_order')->orderBy('id'); }

    public function getPriceLabelAttribute(): string {
        if ($this->price === null || (float)$this->price <= 0) {
            return 'Цена уточняется';
        }
        return number_format((float)$this->price, 0, ',', ' ') . ' ' . $this->currency;
    }

    public function getDurationLabelAttribute(): string {
        if ($this->is_recurring) {
            return 'Ежемесячно (' . $this->days . ' дней / цикл)';
        }
        return $this->days . ' дней';
    }
}
