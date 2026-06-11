<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovelPromotion extends Model {
    protected $fillable = ['novel_id', 'user_id', 'package_id', 'type', 'starts_at', 'ends_at', 'amount_paid', 'status'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function novel() {
        return $this->belongsTo(Novel::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function package() {
        return $this->belongsTo(NovelPromotionPackage::class, 'package_id');
    }

    public function scopeActive($query) {
        return $query->where('status', 'active')->where('ends_at', '>', now());
    }

    public function isActive(): bool {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }
}
