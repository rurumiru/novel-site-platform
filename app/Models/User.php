<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements FilamentUser {
    use Notifiable, HasRoles, MustVerifyEmail;

    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'can_create_novels' => 'boolean',
        'can_set_banner' => 'boolean',
        'is_donation_link_approved' => 'boolean',
        'birth_date' => 'date',
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'is_premium' => 'boolean',
        'premium_until' => 'datetime',
        'trust_level' => 'integer',
        'patron_tier' => 'integer',
        'reviewer_badge' => 'boolean',
    ];

    public function hasAgeInfo(): bool {
        return $this->birth_date !== null && $this->gender !== null;
    }

    public function isOfAge(int $minAge = 18): bool {
        return $this->birth_date !== null
            && $this->birth_date->diffInYears(now()) >= $minAge;
    }

    public function isEmailVerified(): bool {
        return $this->hasVerifiedEmail();
    }

    public function canAccessPanel(Panel $panel): bool {
        return $this->hasRole(['owner', 'super_admin', 'deputy_admin', 'moderator'])
            || $this->email === 'admin@localhost';
    }

    public function isOwner(): bool {
        return $this->hasRole('owner');
    }

    public function isAdmin(): bool {
        return $this->hasRole(['owner', 'super_admin']);
    }

    public function canAssignRole(string $role): bool {
        $hierarchy = ['owner' => 0, 'super_admin' => 1, 'deputy_admin' => 2, 'moderator' => 3, 'editor' => 4, 'author' => 5, 'user' => 6];
        $myLevel   = collect($this->roles->pluck('name'))->map(fn($r) => $hierarchy[$r] ?? 99)->min() ?? 99;
        $roleLevel = $hierarchy[$role] ?? 99;
        return $myLevel < $roleLevel;
    }

    public function getAvatarUrlAttribute() {
        if (!$this->avatar) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
        }
        if (str_starts_with($this->avatar, 'http')) return $this->avatar;
        $path = ltrim(str_replace('\\', '/', $this->avatar), '/');
        $base = config('filesystems.disks.s3.url');
        return $base ? rtrim($base, '/') . '/' . $path : '/storage/' . $path;
    }

    public function getBannerUrlAttribute() {
        if (!$this->banner_image) return null;
        if (str_starts_with($this->banner_image, 'http')) return $this->banner_image;
        $path = ltrim(str_replace('\\', '/', $this->banner_image), '/');
        $base = config('filesystems.disks.s3.url');
        return $base ? rtrim($base, '/') . '/' . $path : '/storage/' . $path;
    }

    public function favorites() { return $this->belongsToMany(Novel::class, 'favorites'); }
    public function novels() { return $this->hasMany(Novel::class); }
    public function editedNovels() { return $this->belongsToMany(Novel::class, 'novel_editors', 'user_id', 'novel_id'); }
    public function unlockedChapters() { return $this->belongsToMany(Chapter::class, 'user_unlocked_chapters'); }
    public function transactions() { return $this->hasMany(Transaction::class)->latest(); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function commenterBadge() { return $this->belongsTo(CommenterBadge::class, 'commenter_badge_id'); }
    public function commenterBackground() { return $this->belongsTo(CommenterBackground::class, 'commenter_background_id'); }
    public function readingProgress() { return $this->hasMany(ReadingProgress::class); }
    public function messagesSent() { return $this->hasMany(Message::class, 'sender_id'); }
    public function messagesReceived() { return $this->hasMany(Message::class, 'receiver_id'); }
    
    public function novelAccesses() { return $this->hasMany(Subscription::class); }
    public function promoCodeUsages() { return $this->hasMany(PromoCodeUsage::class); }
    public function donations() { return $this->hasMany(\App\Models\Donation::class, 'user_id'); }
    public function receivedDonations() { return $this->hasMany(\App\Models\Donation::class, 'author_id'); }

    public const TRUST_LABELS = [
        0 => 'Новичок',
        1 => 'Базовый',
        2 => 'Участник',
        3 => 'Постоянный',
        4 => 'Лидер',
    ];

    public function getTrustLabelAttribute(): string {
        return self::TRUST_LABELS[$this->trust_level ?? 0] ?? 'Новичок';
    }

    public function isAtLeastTrust(int $level): bool {
        return (int) ($this->trust_level ?? 0) >= $level;
    }

    public function isVerified(): bool {
        return (bool) ($this->is_verified ?? false);
    }

    public function isPremiumActive(): bool {
        if (!($this->is_premium ?? false)) return false;
        if ($this->premium_until && $this->premium_until->isPast()) return false;
        return true;
    }

    public function isPlus(): bool { return $this->isPremiumActive(); }
    public function plusExpiresAt(): ?\Carbon\Carbon { return $this->isPremiumActive() ? $this->premium_until : null; }

    public function hasPendingPlusRequest(): bool {
        return Subscription::where('user_id', $this->id)
            ->where('type', 'plus')
            ->where('status', 'pending')
            ->exists();
    }

    public function getPatronTierLabelAttribute(): ?string {
        return [1 => 'Bronze 🥉', 2 => 'Silver 🥈', 3 => 'Gold 🥇'][$this->patron_tier ?? 0] ?? null;
    }

    public function canReadAdvance(?int $novelId = null): bool {
        if ($this->isPremiumActive()) return true;
        if ($this->hasRole(['patron', 'premium_reader'])) return true;
        if ($novelId) {
            return \App\Models\Donation::where('user_id', $this->id)
                ->where('novel_id', $novelId)
                ->where('status', 'completed')
                ->exists();
        }
        return false;
    }

    public function getDisplayBadges(): array {
        $badges = [];
        if ($this->is_verified) {
            $badges[] = ['icon' => 'fa-circle-check', 'label' => 'Верифицирован', 'color' => 'var(--accent)'];
        }
        if ($this->isPremiumActive()) {
            $badges[] = ['icon' => 'fa-crown', 'label' => 'Premium', 'color' => 'var(--gold)'];
        }
        if ($tierLabel = $this->patron_tier_label) {
            $badges[] = ['icon' => 'fa-heart', 'label' => $tierLabel, 'color' => '#ff6b9d'];
        }
        if ($this->reviewer_badge) {
            $badges[] = ['icon' => 'fa-star', 'label' => 'Рецензент', 'color' => 'var(--warn)'];
        }
        if ($this->trust_level >= 4) {
            $badges[] = ['icon' => 'fa-shield-halved', 'label' => 'Лидер сообщества', 'color' => 'var(--ok)'];
        }
        return $badges;
    }
}
