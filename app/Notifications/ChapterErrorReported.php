<?php
namespace App\Notifications;

use App\Models\ChapterError;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ChapterErrorReported extends Notification {
    use Queueable;

    public function __construct(public ChapterError $error) {}

    public function via($notifiable): array {
        return ['database'];
    }

    public function toDatabase($notifiable): array {
        $chapter = $this->error->chapter;
        return [
            'type'          => 'chapter_error',
            'message'       => "Найдена ошибка в главе «{$chapter->title}»: «" . Str::limit($this->error->selected_text, 80) . '»',
            'chapter_id'    => $chapter->id,
            'novel_id'      => $chapter->novel_id,
            'error_id'      => $this->error->id,
            'selected_text' => $this->error->selected_text,
            'suggestion'    => $this->error->suggestion,
        ];
    }
}
