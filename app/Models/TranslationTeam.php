<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class TranslationTeam extends Model {
    protected $fillable = [
        'name','slug','leader_id','description','mission',
        'logo_path','banner_path','website','discord','telegram','vk',
        'status','application_mode','min_trust_level','require_portfolio',
        'is_official','is_featured','balance','total_earned','leader_share','settings',
    ];
    protected $casts = [
        'is_official' => 'boolean',
        'is_featured' => 'boolean',
        'require_portfolio' => 'boolean',
        'leader_share' => 'decimal:2',
        'balance' => 'integer',
        'total_earned' => 'integer',
        'settings' => 'array',
    ];

    protected static function boot(): void {
        parent::boot();
        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    public function leader(): BelongsTo {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): HasMany {
        return $this->hasMany(TranslationTeamMember::class, 'team_id');
    }

    public function activeMembers(): HasMany {
        return $this->members()->where('is_active', true);
    }

    public function novels(): BelongsToMany {
        return $this->belongsToMany(Novel::class, 'novel_team', 'team_id', 'novel_id')
            ->withPivot(['team_revenue_share','is_primary','show_credits'])
            ->withTimestamps();
    }

    public function kickLog(): HasMany {
        return $this->hasMany(TeamKickLog::class, 'team_id');
    }

    public function invites(): HasMany {
        return $this->hasMany(TeamInvite::class, 'team_id');
    }

    public function applications(): HasMany {
        return $this->hasMany(TeamApplication::class, 'team_id');
    }

    public function payouts(): HasMany {
        return $this->hasMany(TeamPayout::class, 'team_id');
    }

    public function novelMemberShares(): HasMany {
        return $this->hasMany(NovelTeamMemberShare::class, 'team_id');
    }

    public function hasMember(int $userId): bool {
        return $this->members()->where('user_id', $userId)->where('is_active', true)->exists();
    }

    public function getMember(int $userId): ?TranslationTeamMember {
        return $this->members()->where('user_id', $userId)->first();
    }

    public function isLeader(int $userId): bool {
        return $this->leader_id === $userId;
    }

    public function canManage(int $userId): bool {
        if ($this->isLeader($userId)) return true;
        $m = $this->getMember($userId);
        return $m && $m->is_active && in_array($m->role, ['leader', 'coordinator']);
    }

    public function getTotalShareAttribute(): float {
        return (float) $this->activeMembers()->sum('revenue_share');
    }

    public function getLeaderEffectiveShareAttribute(): float {
        if ($this->leader_share > 0) return (float) $this->leader_share;
        $otherShare = $this->activeMembers()
            ->where('user_id', '!=', $this->leader_id)
            ->sum('revenue_share');
        return max(0, 100 - (float) $otherShare);
    }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'active'     => 'Активна',
            'recruiting' => 'Набор',
            'closed'     => 'Закрыта',
            'on_hiatus'  => 'Пауза',
            'disbanded'  => 'Распущена',
            default      => $this->status,
        };
    }
}
