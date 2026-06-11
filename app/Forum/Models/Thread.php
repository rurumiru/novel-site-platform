<?php

namespace App\Forum\Models;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Thread extends Model
{
    use SoftDeletes;

    protected $table = 'forum_threads';
    protected $guarded = [];
    protected $casts = [
        'is_pinned'        => 'bool',
        'is_locked'        => 'bool',
        'allowed_roles'    => 'array',
        'last_post_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->title) . '-' . Str::lower(Str::random(6));
            }
            if (empty($m->last_post_at)) {
                $m->last_post_at = now();
            }
            if (empty($m->last_post_user_id)) {
                $m->last_post_user_id = $m->user_id;
            }
        });
    }

    public function section(): BelongsTo  { return $this->belongsTo(Section::class); }
    public function author(): BelongsTo   { return $this->belongsTo(User::class, 'user_id'); }
    public function lastPoster(): BelongsTo { return $this->belongsTo(User::class, 'last_post_user_id'); }
    public function novel(): BelongsTo    { return $this->belongsTo(Novel::class); }
    public function chapter(): BelongsTo  { return $this->belongsTo(Chapter::class); }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('created_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'forum_thread_tag', 'thread_id', 'tag_id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'forum_participants', 'thread_id', 'user_id')
            ->withPivot('can_post')->withTimestamps();
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'forum_subscriptions', 'thread_id', 'user_id')
            ->withPivot('notify')->withTimestamps();
    }

    public function reads(): HasMany { return $this->hasMany(ThreadRead::class, 'thread_id'); }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function scopeVisibleTo(Builder $q, ?User $user): Builder
    {
        return $q->where(function (Builder $q) use ($user) {
            $q->where('visibility', 'public');

            if (!$user) return;

            $q->orWhere(function (Builder $q) use ($user) {
                $q->where('visibility', 'private_users')
                  ->whereExists(function ($sub) use ($user) {
                      $sub->select(\DB::raw(1))
                          ->from('forum_participants')
                          ->whereColumn('forum_participants.thread_id', 'forum_threads.id')
                          ->where('forum_participants.user_id', $user->id);
                  });
            });

            $roles = $user->getRoleNames()->all();
            if (!empty($roles)) {
                $q->orWhere(function (Builder $q) use ($roles) {
                    $q->where('visibility', 'private_roles');
                    $q->where(function (Builder $q) use ($roles) {
                        foreach ($roles as $role) {
                            $q->orWhereJsonContains('allowed_roles', $role);
                        }
                    });
                });
            }

            $q->orWhere('user_id', $user->id);
        });
    }

    public function url(): string
    {
        return route('forum.threads.show', ['section' => $this->section?->slug ?? 'general', 'thread' => $this->slug]);
    }
}
