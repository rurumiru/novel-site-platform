<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportReply extends Model {
    protected $fillable = ['ticket_id', 'user_id', 'body', 'is_staff'];

    public function ticket(): BelongsTo {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
