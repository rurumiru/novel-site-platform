<?php

namespace App\Livewire\Support;

use App\Models\SupportReply;
use App\Models\SupportTicket;
use Livewire\Component;

class SupportTicketShow extends Component {
    public SupportTicket $ticket;

    public string $replyBody = '';
    public string $newStatus = '';

    protected function rules(): array {
        return [
            'replyBody' => 'required|string|min:2|max:5000',
        ];
    }

    protected function messages(): array {
        return [
            'replyBody.required' => 'Напишите ответ.',
            'replyBody.min'      => 'Ответ слишком короткий.',
        ];
    }

    public function mount(int $id): void {
        $this->ticket = SupportTicket::with([
            'user:id,name,username,avatar',
            'replies.user:id,name,username,avatar',
        ])->findOrFail($id);

        $user    = auth()->user();
        $isOwner = $user && $this->ticket->user_id === $user->id;
        $isStaff = $user && $user->hasAnyRole(['owner', 'super_admin', 'deputy_admin', 'moderator']);

        abort_unless($isOwner || $isStaff, 403);

        $this->newStatus = $this->ticket->status;
    }

    private function isStaff(): bool {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['owner', 'super_admin', 'deputy_admin', 'moderator']);
    }

    public function sendReply(): void {
        $user = auth()->user();
        abort_unless($user, 403);

        $isOwner = $this->ticket->user_id === $user->id;
        $isStaff = $this->isStaff();
        abort_unless($isOwner || $isStaff, 403);
        abort_if($this->ticket->status === 'closed' && !$isStaff, 403);

        $data = $this->validate();

        SupportReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id'   => $user->id,
            'body'      => trim($data['replyBody']),
            'is_staff'  => $isStaff,
        ]);

        if ($isStaff && $this->ticket->status === 'open') {
            $this->ticket->update(['status' => 'answered']);
        } elseif (!$isStaff && $this->ticket->status === 'answered') {
            $this->ticket->update(['status' => 'open']);
        }

        $this->reset('replyBody');
        $this->ticket->refresh()->load('replies.user:id,name,username,avatar');
        $this->newStatus = $this->ticket->status;
    }

    public function changeStatus(string $status): void {
        abort_unless($this->isStaff(), 403);
        abort_unless(in_array($status, ['open', 'answered', 'closed'], true), 422);

        $this->ticket->update(['status' => $status]);
        $this->ticket->refresh();
        $this->newStatus = $status;
    }

    public function render() {
        return view('livewire.support.support-ticket-show', [
            'isStaff' => $this->isStaff(),
        ])->extends('layouts.app')->section('content');
    }
}
