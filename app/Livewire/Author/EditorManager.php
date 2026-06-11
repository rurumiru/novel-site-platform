<?php
namespace App\Livewire\Author;

use App\Models\Novel;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class EditorManager extends Component {
    public Novel $novel;
    public string $search = '';
    public array $searchResults = [];
    public string $feedback = '';
    public string $feedbackType = '';

    public const FLAGS = [
        'can_edit_chapters'   => ['Редактировать главы',         'Может править текст и метаданные глав'],
        'can_publish_chapters'=> ['Публиковать главы',           'Может менять статус «опубликовано»'],
        'can_set_paid'        => ['Управлять платным статусом',  'Может делать главы платными/бесплатными и менять цену'],
        'can_read_paid'       => ['Читать платные бесплатно',    'Имеет доступ к закрытым главам без оплаты'],
        'can_edit_settings'   => ['Менять настройки новеллы',    'Описание, обложка, жанры, теги — но не удаление'],
        'can_manage_team'     => ['Управлять командой',          'Добавлять/убирать редакторов и бета-ридеров'],
        'can_moderate_comments' => ['Модерировать комментарии', 'Удалять комменты, отвечать на жалобы'],
    ];

    public const ROLE_LABELS = [
        'editor'      => '✎ Редактор',
        'translator'  => '🌐 Переводчик',
        'proofreader' => '👁 Корректор',
        'co-author'   => '✦ Со-автор',
        'beta'        => '🐞 Бета-ридер',
    ];

    public function mount(Novel $novel) {
        $this->novel = $novel;
    }

    public function updatedSearch() {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }
        $existing = $this->novel->editors()->pluck('users.id');
        $this->searchResults = User::where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->whereNotIn('id', $existing)
            ->where('id', '!=', $this->novel->user_id)
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function addEditor(int $userId, string $roleLabel = 'editor') {
        $user = User::find($userId);
        if (!$user) return;

        $defaults = $this->presetForRole($roleLabel);
        $defaults['role_label'] = $roleLabel;

        $this->novel->editors()->syncWithoutDetaching([$userId => $defaults]);

        $this->search = '';
        $this->searchResults = [];
        $this->feedback = "Добавлен: {$user->name} (роль: {$roleLabel})";
        $this->feedbackType = 'success';
    }

    public function removeEditor(int $userId) {
        $this->novel->editors()->detach($userId);
        $this->feedback = 'Редактор удалён.';
        $this->feedbackType = 'info';
    }

    public function toggleFlag(int $userId, string $flag) {
        if (!array_key_exists($flag, self::FLAGS)) return;

        $current = DB::table('novel_editors')
            ->where('novel_id', $this->novel->id)
            ->where('user_id', $userId)
            ->value($flag);

        DB::table('novel_editors')
            ->where('novel_id', $this->novel->id)
            ->where('user_id', $userId)
            ->update([$flag => !$current, 'updated_at' => now()]);
    }

    public function changeRole(int $userId, string $roleLabel) {
        $preset = $this->presetForRole($roleLabel);
        $preset['role_label'] = $roleLabel;
        DB::table('novel_editors')
            ->where('novel_id', $this->novel->id)
            ->where('user_id', $userId)
            ->update($preset + ['updated_at' => now()]);
    }

    private function presetForRole(string $role): array {
        return match ($role) {
            'editor' => [
                'can_edit_chapters' => true, 'can_publish_chapters' => false,
                'can_set_paid' => false, 'can_read_paid' => true,
                'can_edit_settings' => false, 'can_manage_team' => false,
                'can_moderate_comments' => false,
            ],
            'translator' => [
                'can_edit_chapters' => true, 'can_publish_chapters' => false,
                'can_set_paid' => false, 'can_read_paid' => true,
                'can_edit_settings' => false, 'can_manage_team' => false,
                'can_moderate_comments' => false,
            ],
            'proofreader' => [
                'can_edit_chapters' => true, 'can_publish_chapters' => false,
                'can_set_paid' => false, 'can_read_paid' => false,
                'can_edit_settings' => false, 'can_manage_team' => false,
                'can_moderate_comments' => false,
            ],
            'co-author' => [
                'can_edit_chapters' => true, 'can_publish_chapters' => true,
                'can_set_paid' => true, 'can_read_paid' => true,
                'can_edit_settings' => true, 'can_manage_team' => false,
                'can_moderate_comments' => true,
            ],
            'beta' => [
                'can_edit_chapters' => false, 'can_publish_chapters' => false,
                'can_set_paid' => false, 'can_read_paid' => true,
                'can_edit_settings' => false, 'can_manage_team' => false,
                'can_moderate_comments' => false,
            ],
            default => [
                'can_edit_chapters' => true, 'can_publish_chapters' => false,
                'can_set_paid' => false, 'can_read_paid' => false,
                'can_edit_settings' => false, 'can_manage_team' => false,
                'can_moderate_comments' => false,
            ],
        };
    }

    public function getEditorsProperty() {
        return $this->novel->editors()
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->map(function ($user) {
                $pivot = DB::table('novel_editors')
                    ->where('novel_id', $this->novel->id)
                    ->where('user_id', $user->id)
                    ->first();
                $user->pivot_data = (array) $pivot;
                return $user;
            });
    }

    public function render() {
        return view('livewire.author.editor-manager', [
            'editors' => $this->editors,
            'flags' => self::FLAGS,
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }
}
