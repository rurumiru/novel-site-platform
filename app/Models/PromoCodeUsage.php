<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCodeUsage extends Model {
    protected $fillable = ['promo_code_id', 'user_id', 'applied_at'];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function promoCode(): BelongsTo { return $this->belongsTo(PromoCode::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
}
