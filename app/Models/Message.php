<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    protected $guarded = [];
    protected $casts = ['is_spam' => 'boolean', 'spam_reported_at' => 'datetime'];

    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }
    public function reporter() { return $this->belongsTo(User::class, 'reported_by'); }
}
