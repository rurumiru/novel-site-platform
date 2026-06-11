<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\BetaReader;
use App\Models\BetaReaderNote;
use App\Models\Chapter;
use App\Models\ChapterBookmark;
use App\Models\ChapterError;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\ChapterErrorReported;
use App\Services\CommentSanitizer;
use App\Services\CommentContentRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterApiController extends Controller
{
    public function comments($chapter_id)
    {
        $chapter = Chapter::where('is_published', true)->findOrFail($chapter_id);
        $comments = $chapter->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar', 'replies' => fn($q) => $q->with(['user:id,name,avatar'])->latest()])
            ->withCount('likes')
            ->latest()
            ->get()
            ->map(fn($c) => $this->formatComment($c));

        return response()->json([
            'comments' => $comments,
            'count' => $chapter->comments()->count(),
        ]);
    }

    public function storeComment(Request $request, $chapter_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Войдите, чтобы оставить комментарий'], 401);
        }
        $request->validate(['content' => 'required|min:2|max:5000']);
        $chapter = Chapter::where('is_published', true)->findOrFail($chapter_id);

        $isAdmin = Auth::user()->hasRole(['super_admin', 'moderator']);
        $content = CommentSanitizer::sanitize($request->content, $isAdmin);
        if (strlen(trim(strip_tags($content))) < 2) {
            return response()->json(['error' => 'Комментарий слишком короткий'], 422);
        }

        $comment = $chapter->comments()->create([
            'user_id' => Auth::id(),
            'content' => $content,
            'parent_id' => $request->parent_id,
        ]);
        $comment->load(['user:id,name,avatar', 'replies']);
        $comment->loadCount('likes');

        return response()->json([
            'comment' => $this->formatComment($comment),
        ], 201);
    }

    public function toggleLike($comment_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Войдите'], 401);
        }
        $comment = Comment::findOrFail($comment_id);
        $existing = $comment->likes()->where('user_id', Auth::id())->first();
        if ($existing) {
            $existing->delete();
        } else {
            $comment->likes()->create(['user_id' => Auth::id()]);
        }
        return response()->json([
            'likes_count' => $comment->likes()->count(),
            'is_liked' => !$existing,
        ]);
    }

    public function reportError(Request $request, $chapter_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Войдите, чтобы сообщить об ошибке'], 401);
        }
        $request->validate([
            'selected_text' => 'required|string|max:500',
            'suggestion'    => 'nullable|string|max:1000',
        ]);
        $chapter = Chapter::findOrFail($chapter_id);

        $error = ChapterError::create([
            'chapter_id'    => $chapter->id,
            'user_id'       => Auth::id(),
            'selected_text' => $request->selected_text,
            'suggestion'    => $request->suggestion,
        ]);

        $error->load('chapter.novel');
        $novel = $error->chapter->novel;

        if ($novel && $novel->user_id && $novel->user_id !== Auth::id()) {
            optional(User::find($novel->user_id))->notify(new ChapterErrorReported($error));
        }

        User::role('super_admin')->where('id', '!=', Auth::id())->each(
            fn($admin) => $admin->notify(new ChapterErrorReported($error))
        );

        return response()->json(['success' => true, 'message' => 'Спасибо! Ошибка отправлена на проверку.']);
    }

    public function storeBetaNote(Request $request, $chapter_id)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);

        $chapter = Chapter::findOrFail($chapter_id);

        $isBeta = BetaReader::where('novel_id', $chapter->novel_id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isBeta) {
            return response()->json(['error' => 'Доступ только для бета-ридеров'], 403);
        }

        $request->validate(['content' => 'required|string|min:2|max:2000']);

        BetaReaderNote::create([
            'novel_id'   => $chapter->novel_id,
            'chapter_id' => $chapter->id,
            'user_id'    => Auth::id(),
            'content'    => strip_tags($request->content),
        ]);

        return response()->json(['success' => true, 'message' => 'Заметка отправлена автору.']);
    }

    private function formatComment(Comment $c): array
    {
        $user = $c->user;
        return [
            'id' => $c->id,
            'content' => $c->content,
            'content_html' => CommentContentRenderer::toSafeHtml($c->content),
            'created_at' => $c->created_at->diffForHumans(),
            'likes_count' => $c->likes_count ?? $c->likes()->count(),
            'is_liked' => $c->is_liked,
            'user' => $this->formatCommentUser($user),
            'replies' => $c->replies->map(fn($r) => [
                'id' => $r->id,
                'content' => $r->content,
                'content_html' => CommentContentRenderer::toSafeHtml($r->content),
                'created_at' => $r->created_at->diffForHumans(),
                'user' => $this->formatCommentUser($r->user),
            ])->values()->toArray(),
        ];
    }

    private function formatCommentUser($user): array
    {
        return [
            'id' => $user?->id,
            'name' => $user?->name ?? 'Удалён',
            'avatar_url' => $user?->avatar_url ?? 'https://ui-avatars.com/api/?name=U',
        ];
    }

    public function checkBookmark($chapter_id)
    {
        if (!Auth::check()) return response()->json(['bookmarked' => false]);
        $exists = ChapterBookmark::where('user_id', Auth::id())->where('chapter_id', $chapter_id)->exists();
        return response()->json(['bookmarked' => $exists]);
    }

    public function toggleBookmark(Request $request, $chapter_id)
    {
        if (!Auth::check()) return response()->json(['error' => 'unauth'], 401);
        $chapter = Chapter::findOrFail($chapter_id);

        $existing = ChapterBookmark::where('user_id', Auth::id())->where('chapter_id', $chapter_id)->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false]);
        }
        ChapterBookmark::create([
            'user_id'    => Auth::id(),
            'chapter_id' => $chapter->id,
            'novel_id'   => $chapter->novel_id,
            'percent'    => (float) $request->input('percent', 0),
            'note'       => $request->input('note'),
        ]);
        return response()->json(['bookmarked' => true]);
    }

    public function complaint(Request $request, $chapter_id)
    {
        if (!Auth::check()) return response()->json(['error' => 'unauth'], 401);
        $chapter = Chapter::findOrFail($chapter_id);

        $reason  = trim((string) $request->input('reason', ''));
        $details = trim((string) $request->input('details', ''));
        if (mb_strlen($reason) < 1) return response()->json(['error' => 'Укажите причину'], 422);

        ChapterError::create([
            'chapter_id'    => $chapter->id,
            'user_id'       => Auth::id(),
            'selected_text' => '[жалоба] ' . $reason,
            'suggestion'    => $details,
            'status'        => 'new',
        ]);

        try {
            $author = $chapter->novel?->publisher;
            if ($author && class_exists(\App\Notifications\ChapterErrorReported::class)) {
                $author->notify(new \App\Notifications\ChapterErrorReported($chapter, '[жалоба] ' . $reason));
            }
        } catch (\Throwable $e) {}

        return response()->json(['ok' => true]);
    }
}
