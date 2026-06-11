<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetaReader extends Model {
    public $timestamps = false;
    protected $fillable = ['novel_id', 'user_id', 'invited_by'];

    public function novel() {
        return $this->belongsTo(Novel::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function inviter() {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
