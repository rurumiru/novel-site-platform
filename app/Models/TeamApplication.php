<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamApplication extends Model {
    protected $fillable = [
        'team_id','user_id','desired_role','cover_letter','portfolio_url',
        'status','reviewed_by','rejection_reason','reviewed_at',
    ];
    protected $casts = ['reviewed_at' => 'datetime'];

    public function team(): BelongsTo { return $this->belongsTo(TranslationTeam::class, 'team_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
