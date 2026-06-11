<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewRecommend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller {
    private const MOD_ROLES = ['owner', 'super_admin', 'deputy_admin', 'moderator'];

    public function show(Review $review) {
        if (!$review->is_published && !$this->canModerate() && Auth::id() !== $review->user_id) {
            abort(404);
        }

        $review->increment('views_count');
        $review->load(['user:id,name,username,avatar', 'novel:id,title,slug,cover_image']);

        return view('reviews.show', compact('review'));
    }

    public function create() {
        abort_unless(Auth::check(), 403);
        return view('reviews.create', [
            'review'      => new Review(['category' => 'review']),
            'canPin'      => $this->canModerate(),
        ]);
    }

    public function store(Request $request) {
        abort_unless(Auth::check(), 403);

        $data = $this->validateData($request);

        if (!$this->canModerate()) {
            $data['category'] = $data['category'] === 'notice' ? 'review' : $data['category'];
            $data['is_pinned'] = false;
        }

        $review = Review::create(array_merge($data, [
            'user_id'          => Auth::id(),
            'last_activity_at' => now(),
        ]));

        return redirect()->route('reviews.show', $review)->with('success', 'Обзор опубликован.');
    }

    public function edit(Review $review) {
        $this->authorizeEdit($review);
        return view('reviews.edit', [
            'review' => $review,
            'canPin' => $this->canModerate(),
        ]);
    }

    public function update(Request $request, Review $review) {
        $this->authorizeEdit($review);

        $data = $this->validateData($request);
        if (!$this->canModerate()) {
            $data['category']  = $data['category'] === 'notice' ? $review->category : $data['category'];
            $data['is_pinned'] = $review->is_pinned;
        }

        $review->update($data);
        return redirect()->route('reviews.show', $review)->with('success', 'Обзор обновлён.');
    }

    public function destroy(Review $review) {
        $this->authorizeEdit($review);
        $review->delete();
        return redirect()->route('reviews.index')->with('success', 'Обзор удалён.');
    }

    public function recommend(Review $review) {
        abort_unless(Auth::check(), 403);

        $uid = Auth::id();
        $existing = ReviewRecommend::where('review_id', $review->id)->where('user_id', $uid)->first();

        if ($existing) {
            $existing->delete();
            $review->decrement('recommends_count');
            $state = 'off';
        } else {
            ReviewRecommend::create(['review_id' => $review->id, 'user_id' => $uid]);
            $review->increment('recommends_count');
            $state = 'on';
        }

        if (request()->wantsJson()) {
            return response()->json([
                'state' => $state,
                'count' => $review->fresh()->recommends_count,
            ]);
        }
        return back();
    }

    public function pin(Review $review) {
        abort_unless($this->canModerate(), 403);
        $review->update(['is_pinned' => !$review->is_pinned]);
        return back()->with('success', $review->is_pinned ? 'Закреплено.' : 'Откреплено.');
    }

    private function validateData(Request $request): array {
        $data = $request->validate([
            'title'     => 'required|string|max:200',
            'body'      => 'required|string|max:200000',
            'category'  => 'required|in:review,promotion,notice',
            'novel_id'  => 'nullable|exists:novels,id',
            'is_pinned' => 'nullable|boolean',
        ]);

        $data['body'] = $this->sanitizeBody($data['body']);
        $data['is_pinned'] = (bool)($data['is_pinned'] ?? false);

        return $data;
    }

    private function sanitizeBody(string $html): string {
        $allowed = '<p><br><strong><b><em><i><u><s><strike><del><a><img><ul><ol><li><blockquote><h1><h2><h3><h4><pre><code><hr><details><summary><span><div>';
        $clean = strip_tags($html, $allowed);

        $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
        $clean = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '$1=$2#$2', $clean);

        $clean = preg_replace_callback('/<details\b[^>]*>/i', function () {
            return '<details class="rv-spoiler">';
        }, $clean);
        $clean = preg_replace('/<summary\b[^>]*>/i', '<summary>', $clean);

        $clean = preg_replace_callback('/<(span|div)\b([^>]*)>/i', function ($m) {
            $tag = strtolower($m[1]);
            if (preg_match('/class\s*=\s*(["\'])([^"\']+)\1/i', $m[2], $cm)) {
                $cls = trim($cm[2]);
                $allowedCls = ['rv-spoiler', 'spoiler'];
                $cls = implode(' ', array_intersect(preg_split('/\s+/', $cls), $allowedCls));
                return $cls ? "<{$tag} class=\"{$cls}\">" : "<{$tag}>";
            }
            return "<{$tag}>";
        }, $clean);

        return $clean;
    }

    private function authorizeEdit(Review $review): void {
        if (Auth::id() === $review->user_id) return;
        if ($this->canModerate()) return;
        abort(403);
    }

    private function canModerate(): bool {
        $u = Auth::user();
        return $u && $u->hasRole(self::MOD_ROLES);
    }
}
