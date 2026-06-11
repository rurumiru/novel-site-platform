@php $pageTitle = 'Модерация — Жалобы'; @endphp
@extends('forum.layout')

@section('hideForumHero')
@endsection

@section('breadcrumbs')
    <span class="forum-bc-sep">/</span>
    <span class="forum-bc-current">Жалобы</span>
@endsection

@section('forum')
    <h1 class="forum-section-head__title"><i class="fa-solid fa-flag"></i> Жалобы</h1>

    <div class="forum-threads">
        @forelse($reports as $r)
            <div class="forum-thread">
                <div class="forum-thread__body" style="padding: 12px 16px;">
                    <div class="forum-thread__top">
                        <span class="forum-thread__badge">{{ $r->reason }}</span>
                        <span class="forum-thread__title">
                            {{ class_basename($r->reportable_type) }} #{{ $r->reportable_id }}
                        </span>
                    </div>
                    <div class="forum-thread__meta">
                        <span>от {{ optional($r->reporter)->name }} · {{ $r->created_at->diffForHumans() }}</span>
                    </div>
                    @if($r->comment)
                        <p style="margin-top: 8px; color: var(--text-mute);">{{ $r->comment }}</p>
                    @endif
                </div>
                <div class="forum-post__tools" style="padding: 12px 16px;">
                    <form method="POST" action="{{ route('forum.moderation.reports.resolve', $r) }}" class="forum-inline">
                        @csrf
                        <input type="hidden" name="status" value="accepted">
                        <button class="forum-link-btn">Принять</button>
                    </form>
                    <form method="POST" action="{{ route('forum.moderation.reports.resolve', $r) }}" class="forum-inline">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <button class="forum-link-btn forum-link-btn--danger">Отклонить</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="forum-empty">Открытых жалоб нет.</div>
        @endforelse
    </div>

    <div class="forum-pagination">{{ $reports->links() }}</div>
@endsection
