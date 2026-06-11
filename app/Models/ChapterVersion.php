<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterVersion extends Model {
    protected $guarded = [];

    public function chapter(): BelongsTo { return $this->belongsTo(Chapter::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function saveVersion(int $chapterId, int $userId, string $content, ?string $label = null): self {
        $recent = static::where('chapter_id', $chapterId)
            ->where('user_id', $userId)
            ->where('updated_at', '>=', now()->subHour())
            ->latest('updated_at')
            ->first();

        if ($recent) {
            $recent->update(['content' => $content, 'label' => $label ?? $recent->label]);
            return $recent;
        }

        $count = static::where('chapter_id', $chapterId)->count();
        return static::create([
            'chapter_id' => $chapterId,
            'user_id'    => $userId,
            'content'    => $content,
            'label'      => $label ?? 'Вариант #' . ($count + 1),
            'is_active'  => false,
        ]);
    }
}
