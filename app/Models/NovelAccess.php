<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NovelAccess extends Model {
    protected $guarded = [];
    public function novel() { return $this->belongsTo(Novel::class); }
}
