<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetaReaderNote extends Model {
    public $timestamps = false;
    protected $fillable = ['novel_id', 'chapter_id', 'user_id', 'content'];

    public function novel() {
        return $this->belongsTo(Novel::class);
    }

    public function chapter() {
        return $this->belongsTo(Chapter::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
