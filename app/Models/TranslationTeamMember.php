<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationTeamMember extends Model {
    protected $fillable = [
        'team_id','user_id','role','title','revenue_share',
        'is_active','display_on_profile','note',
        'can_assign_novels','can_invite_members','can_manage_payouts',
        'can_publish','can_edit_any_chapter',
        'joined_at','left_at',
    ];
    protected $casts = [
        'revenue_share'       => 'decimal:2',
        'is_active'           => 'boolean',
        'display_on_profile'  => 'boolean',
        'can_assign_novels'   => 'boolean',
        'can_invite_members'  => 'boolean',
        'can_manage_payouts'  => 'boolean',
        'can_publish'         => 'boolean',
        'can_edit_any_chapter'=> 'boolean',
        'joined_at'           => 'datetime',
        'left_at'             => 'datetime',
    ];

    public function team(): BelongsTo {
        return $this->belongsTo(TranslationTeam::class, 'team_id');
    }
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function getRoleLabelAttribute(): string {
        return match($this->role) {
            'leader'      => 'Лидер',
            'coordinator' => 'Координатор',
            'translator'  => 'Переводчик',
            'editor'      => 'Редактор',
            'typesetter'  => 'Вёрстка',
            'illustrator' => 'Художник',
            'tlc'         => 'TL-check',
            'beta'        => 'Бета-ридер',
            'member'      => 'Участник',
            default       => $this->role,
        };
    }

    public function getRoleColorAttribute(): string {
        return match($this->role) {
            'leader'      => 'var(--gold)',
            'coordinator' => 'var(--accent)',
            'translator'  => '#8b5cf6',
            'editor'      => '#14b8a6',
            'typesetter'  => '#f59e0b',
            'illustrator' => '#ec4899',
            'tlc'         => '#3b82f6',
            default       => 'var(--text-muted)',
        };
    }

    public function getDisplayTitleAttribute(): string {
        return $this->title ?: $this->role_label;
    }
}
