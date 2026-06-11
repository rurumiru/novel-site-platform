<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscription extends Model {
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = 'SUB-' . strtoupper(Str::random(5));
            }
        });
    }
    
    public function user() { return $this->belongsTo(User::class); }
    public function novel() { return $this->belongsTo(Novel::class); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function plusPlan() { return $this->belongsTo(PlusPlan::class, 'plus_plan_id'); }
}
