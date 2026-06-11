<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterError extends Model {
    protected $fillable = ['chapter_id', 'user_id', 'selected_text', 'suggestion', 'status'];

    public function chapter() { return $this->belongsTo(Chapter::class); }
    public function user() { return $this->belongsTo(User::class); }
}
