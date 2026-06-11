<?php
namespace App\Notifications;

use App\Models\ChapterError;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChapterErrorResolved extends Notification {
    use Queueable;

    public function __construct(public ChapterError $error) {}

    public function via($notifiable): array {
        return ['database'];
    }

    public function toDatabase($notifiable): array {
        $chapter = $this->error->chapter;
        $statusLabel = $this->error->status === 'fixed' ? 'исправлена' : 'проверена';
        return [
            'type'       => 'error_resolved',
            'message'    => "Ваш отчёт об ошибке в главе «{$chapter->title}» {$statusLabel}. Спасибо за помощь!",
            'chapter_id' => $chapter->id,
            'novel_id'   => $chapter->novel_id,
            'link'       => route('novel.read', [$chapter->novel_id, $chapter->id]),
        ];
    }
}
