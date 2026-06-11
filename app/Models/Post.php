<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model {
    protected $guarded = [];
    protected function casts(): array {
        $casts = ['is_published' => 'boolean'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_recruitment')) {
            $casts['is_recruitment'] = 'boolean';
        }
        return $casts;
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) $model->slug = Str::slug($model->title) . '-' . uniqid();
        });
    }

    public function getImageUrlAttribute(): ?string {
        return Novel::storageUrl($this->image);
    }

    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable')->latest(); }
    public function recruitmentApplications() { return $this->hasMany(RecruitmentApplication::class); }
}
