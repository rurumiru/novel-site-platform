<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model {
    protected $guarded = [];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = strtoupper(Str::random(10));
            }
        });
    }

    public function user() { return $this->belongsTo(User::class); }
}
