<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamKickLog extends Model {
    protected $fillable = [
        'team_id','user_id','kicked_by','action','reason',
        'show_in_profile','prev_role','new_role','prev_share','new_share','meta','logged_at',
    ];
    protected $casts = [
        'show_in_profile' => 'boolean',
        'prev_share'      => 'decimal:2',
        'new_share'       => 'decimal:2',
        'meta'            => 'array',
        'logged_at'       => 'datetime',
    ];

    public function team(): BelongsTo { return $this->belongsTo(TranslationTeam::class, 'team_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function kickedBy(): BelongsTo { return $this->belongsTo(User::class, 'kicked_by'); }

    public function getActionLabelAttribute(): string {
        return match($this->action) {
            'kick'        => 'Исключён',
            'leave'       => 'Вышел сам',
            'ban'         => 'Заблокирован',
            'role_change' => 'Смена роли',
            'share_change'=> 'Смена доли',
            default       => $this->action,
        ];
    }
}
