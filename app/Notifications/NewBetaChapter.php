<?php
namespace App\Notifications;

use App\Models\Chapter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBetaChapter extends Notification {
    use Queueable;

    public function __construct(public Chapter $chapter) {}

    public function via($notifiable): array {
        return ['database'];
    }

    public function toDatabase($notifiable): array {
        $novel = $this->chapter->novel;
        return [
            'type'       => 'beta_chapter',
            'message'    => "Новая глава «{$this->chapter->title}» в новелле «{$novel->title}» доступна для бета-прочтения.",
            'chapter_id' => $this->chapter->id,
            'novel_id'   => $novel->id,
            'link'       => route('novel.read', [$novel->id, $this->chapter->id]),
        ];
    }
}
