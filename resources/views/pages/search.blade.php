@extends('layouts.app')
@section('title', 'Поиск')

@section('content')
<div class="eri-section">
    <div style="max-width:680px;margin:0 auto 36px;text-align:center;">
        <h1 style="font-family:var(--display);font-size:40px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 24px;">Поиск новелл</h1>
        <form action="{{ route('search') }}" method="GET" style="position:relative;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:18px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:15px;pointer-events:none;"></i>
            <input type="text" name="q" value="{{ $query }}" placeholder="Введите название или тег..."
                   class="eri-input" style="padding:14px 120px 14px 44px;font-size:15px;border-radius:var(--r-pill);">
            <button type="submit" class="eri-btn primary" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);padding:8px 20px;">Найти</button>
        </form>
    </div>

    @if($query)
        <h2 style="font-family:var(--display);font-size:20px;font-weight:500;color:var(--text-2);margin:0 0 20px;letter-spacing:-0.01em;">
            Результаты по запросу: <span style="color:var(--text);">«{{ $query }}»</span>
        </h2>

        @if($novels->count())
            <div class="eri-grid-row">
                @foreach($novels as $novel)
                    <x-eriiba.novel-card :novel="$novel" />
                @endforeach
            </div>

            <div style="margin-top:32px;">
                {{ $novels->links() }}
            </div>
        @else
            <div style="text-align:center;padding:80px 20px;">
                <i class="fa-regular fa-face-frown" style="font-size:56px;color:var(--text-faint);display:block;margin-bottom:18px;"></i>
                <p style="font-family:var(--serif);font-size:17px;color:var(--text-3);margin:0;">Ничего не найдено.</p>
            </div>
        @endif
    @endif
</div>
@endsection
