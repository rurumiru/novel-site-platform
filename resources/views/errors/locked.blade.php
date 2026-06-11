@extends('layouts.app')
@section('title', 'Доступ закрыт')
@section('content')
<div class="eri-section" style="min-height:60vh;display:flex;align-items:center;justify-content:center;text-align:center;">
    <div style="max-width:460px;">
        <div style="width:72px;height:72px;border-radius:var(--r-md);background:var(--surface-2);color:var(--warn);display:grid;place-items:center;margin:0 auto 18px;font-size:30px;">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h1 style="font-family:var(--display);font-size:28px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 12px;">Глава закрыта</h1>
        <p style="font-family:var(--serif);font-size:16px;line-height:1.55;color:var(--text-3);margin:0 0 26px;">Доступ к этой части произведения ограничен автором или администрацией.</p>
        <a href="{{ route('novel.show', $novel->id) }}" wire:navigate class="eri-btn primary lg">
            <i class="fa-solid fa-arrow-left"></i> Вернуться к новелле
        </a>
    </div>
</div>
@endsection
