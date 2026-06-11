<?php
namespace App\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use App\Models\CommentRecommendation;
use App\Models\CommentReport;
use App\Models\Sticker;

class CommentsSection extends Component {
    public $model;
    public $content = '';
    public $replyToId = null;

    public string $sortBy = 'latest';

    public $reportingId = null;
    public $reportReason = '';
    public $reportDetails = '';

    public bool $stickerPickerOpen = false;

    public function setSort(string $key): void {
        $allowed = ['latest', 'oldest', 'recommended'];
        $this->sortBy = in_array($key, $allowed, true) ? $key : 'latest';
    }

    protected $rules = [
        'content' => 'required|min:2|max:2000',
        'reportReason' => 'required|min:2|max:255',
    ];

    public function mount($model) {
        $this->model = $model;
    }

    public function setReply($commentId) {
        $this->replyToId = $commentId;
    }

    public function cancelReply() {
        $this->replyToId = null;
    }

    public function toggleLike($commentId) {
        if (!Auth::check()) return redirect()->route('login');

        $comment = Comment::findOrFail($commentId);
        $existing = $comment->likes()->where('user_id', Auth::id())->first();

        if ($existing) {
            $existing->delete();
        } else {
            $comment->likes()->create(['user_id' => Auth::id()]);
        }
    }

    public function toggleRecommend($commentId) {
        if (!Auth::check()) return redirect()->route('login');

        $existing = CommentRecommendation::where('comment_id', $commentId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            CommentRecommendation::create([
                'comment_id' => $commentId,
                'user_id' => Auth::id(),
            ]);
        }
    }

    public function openReport($commentId) {
        if (!Auth::check()) return redirect()->route('login');
        $this->reportingId = (int) $commentId;
        $this->reportReason = '';
        $this->reportDetails = '';
    }

    public function cancelReport() {
        $this->reportingId = null;
        $this->reportReason = '';
        $this->reportDetails = '';
    }

    public function submitReport() {
        if (!Auth::check()) return redirect()->route('login');
        if (!$this->reportingId) return;

        $this->validate(['reportReason' => 'required|min:2|max:255']);

        CommentReport::updateOrCreate(
            ['comment_id' => $this->reportingId, 'user_id' => Auth::id()],
            [
                'reason' => trim($this->reportReason),
                'details' => trim($this->reportDetails) ?: null,
                'status' => 'new',
            ]
        );

        $this->cancelReport();
        session()->flash('comment_report_ok', 'Спасибо! Жалоба отправлена модераторам.');
    }

    public function postComment() {
        $this->validate(['content' => 'required|min:2|max:2000']);
        if (!Auth::check()) return redirect()->route('login');

        $comment = $this->model->comments()->create([
            'user_id' => Auth::id(),
            'content' => $this->content,
            'parent_id' => $this->replyToId,
        ]);

        $this->content = '';
        $this->replyToId = null;
        $this->stickerPickerOpen = false;

        try { $this->notifyAboutNewComment($comment); }
        catch (\Throwable $e) { \Log::warning('NewCommentNotification failed: ' . $e->getMessage()); }

        try { \App\Services\TrustLevelService::recalculateFor(Auth::user()); }
        catch (\Throwable $e) {}
    }

    private function notifyAboutNewComment(\App\Models\Comment $comment): void {
        $actorId = Auth::id();

        $ownerId = null;
        if ($this->model instanceof \App\Models\Chapter) {
            $ownerId = $this->model->novel?->user_id;
        } elseif ($this->model instanceof \App\Models\Novel) {
            $ownerId = $this->model->user_id;
        } elseif ($this->model instanceof \App\Models\Review) {
            $ownerId = $this->model->user_id;
        } elseif (isset($this->model->user_id)) {
            $ownerId = $this->model->user_id;
        }

        if ($ownerId && $ownerId !== $actorId) {
            $owner = \App\Models\User::find($ownerId);
            if ($owner) {
                $owner->notify(new \App\Notifications\NewCommentNotification($comment, 'on_content'));
            }
        }

        if ($comment->parent_id) {
            $parent = \App\Models\Comment::find($comment->parent_id);
            if ($parent && $parent->user_id && $parent->user_id !== $actorId && $parent->user_id !== $ownerId) {
                $parentOwner = \App\Models\User::find($parent->user_id);
                if ($parentOwner) {
                    $parentOwner->notify(new \App\Notifications\NewCommentNotification($comment, 'reply'));
                }
            }
        }
    }

    public function insertSticker(int $stickerId) {
        $st = Sticker::where('id', $stickerId)->where('is_active', true)->first();
        if (!$st) return;
        $this->content = trim($this->content . ' [sticker:' . $st->slug . ']');
    }

    public function toggleStickerPicker() {
        $this->stickerPickerOpen = !$this->stickerPickerOpen;
    }

    public function render() {
        $query = $this->model->comments()
            ->whereNull('parent_id')
            ->with([
                'user.commenterBadge',
                'user.commenterBackground',
                'likes',
                'recommendations',
                'replies' => function ($q) {
                    $q->with([
                        'user.commenterBadge', 'user.commenterBackground', 'likes', 'recommendations',
                        'replies' => function ($q2) {
                            $q2->with([
                                'user.commenterBadge', 'user.commenterBackground', 'likes', 'recommendations',
                                'replies' => function ($q3) {
                                    $q3->with(['user.commenterBadge', 'user.commenterBackground', 'likes', 'recommendations'])
                                       ->withCount(['likes', 'recommendations'])->oldest();
                                },
                            ])->withCount(['likes', 'recommendations'])->oldest();
                        },
                    ])->withCount(['likes', 'recommendations'])->oldest();
                },
            ])
            ->withCount(['likes', 'recommendations']);

        $comments = match ($this->sortBy) {
            'oldest'      => $query->oldest()->get(),
            'recommended' => $query->orderByDesc('recommendations_count')->latest()->get(),
            default       => $query->latest()->get(),
        };

        $stickerPacks = collect();
        if ($this->stickerPickerOpen) {
            $stickerPacks = \App\Models\StickerPack::where('is_active', true)
                ->orderBy('sort_order')
                ->with(['stickers' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->get();
        }

        return view('livewire.comments-section', compact('comments', 'stickerPacks'));
    }
}
