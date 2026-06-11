<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelTeamMemberShare extends Model {
    protected $table = 'novel_team_member_shares';
    protected $fillable = ['novel_id','team_id','user_id','revenue_share','role_override','note'];
    protected $casts = ['revenue_share' => 'decimal:2'];

    public function novel(): BelongsTo { return $this->belongsTo(Novel::class); }
    public function team(): BelongsTo { return $this->belongsTo(TranslationTeam::class, 'team_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
