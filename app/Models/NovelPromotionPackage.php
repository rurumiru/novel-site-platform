<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovelPromotionPackage extends Model {
    protected $fillable = ['name', 'type', 'duration_days', 'price_coins', 'max_slots', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function promotions() {
        return $this->hasMany(NovelPromotion::class, 'package_id');
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function getTypeLabel(): string {
        return match($this->type) {
            'homepage_featured' => 'Главная страница',
            'catalog_top'       => 'Топ каталога',
            default             => $this->type,
        };
    }
}
