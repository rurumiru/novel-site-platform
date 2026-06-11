<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SupportIndex extends Component {
    use WithPagination;

    #[Url(as: 'tab')]
    public string $tab = 'list';

    public string $category = 'reader';
    public string $subject  = '';
    public string $body     = '';

    protected function rules(): array {
        return [
            'category' => 'required|in:reader,author,bug,other',
            'subject'  => 'required|string|min:5|max:255',
            'body'     => 'required|string|min:10|max:5000',
        ];
    }

    protected function messages(): array {
        return [
            'subject.required' => 'Укажите тему.',
            'subject.min'      => 'Тема слишком короткая (минимум 5 символов).',
            'subject.max'      => 'Тема слишком длинная.',
            'body.required'    => 'Напишите текст заявки.',
            'body.min'         => 'Текст слишком короткий (минимум 10 символов).',
            'body.max'         => 'Текст слишком длинный (максимум 5000 символов).',
        ];
    }

    public function updatingTab(): void {
        $this->resetPage();
        $this->resetValidation();
    }

    public function submit(): void {
        abort_unless(auth()->check(), 403);
        $data = $this->validate();

        $ticket = SupportTicket::create([
            'user_id'  => auth()->id(),
            'category' => $data['category'],
            'subject'  => trim($data['subject']),
            'body'     => trim($data['body']),
            'status'   => 'open',
        ]);

        $this->reset('subject', 'body');
        $this->tab = 'list';
        session()->flash('flash_success', 'Заявка #' . $ticket->id . ' создана. Ответим в течение суток.');
    }

    private function isStaff(): bool {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['owner', 'super_admin', 'deputy_admin', 'moderator']);
    }

    public function render() {
        $user    = auth()->user();
        $isStaff = $this->isStaff();

        $query = SupportTicket::query()
            ->with('user:id,name,username,avatar')
            ->withCount('replies');

        if (!$isStaff) {
            $query->where('user_id', $user?->id ?? 0);
        }

        $query->orderByRaw("FIELD(status, 'open', 'answered', 'closed')")
              ->orderByDesc('created_at');

        $tickets = $query->paginate(20);

        return view('livewire.support.support-index', [
            'tickets' => $tickets,
            'isStaff' => $isStaff,
        ])->extends('layouts.app')->section('content');
    }
}
