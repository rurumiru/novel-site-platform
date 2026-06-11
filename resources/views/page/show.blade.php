@extends('layouts.app')
@section('title', $page->title)

@section('content')
<div class="eri-section" style="padding-top:24px;padding-bottom:0;">
    <a href="{{ url()->previous() }}"
       style="font-family:var(--mono);font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-3);text-decoration:none;font-weight:600;">
        ← Назад
    </a>
</div>

<article class="eri-section" style="padding-top:18px;">
    <header style="max-width:720px;margin:0 auto 28px;">
        <div style="font-family:var(--mono);font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:var(--accent);font-weight:700;margin-bottom:14px;">
            Информация
        </div>
        <h1 style="font-family:var(--display);font-size:clamp(34px,5vw,56px);font-weight:500;letter-spacing:-0.025em;line-height:1.02;margin:0 0 8px;color:var(--text);text-wrap:balance;">
            {{ $page->title }}
        </h1>
        @if(isset($page->updated_at))
            <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);letter-spacing:0.04em;font-weight:600;">
                Обновлено · {{ mb_strtoupper($page->updated_at->translatedFormat('d M Y')) }}
            </div>
        @endif
    </header>

    <div class="eri-card eri-card-pad-lg" style="max-width:720px;margin:0 auto;">
        <div class="serif" style="font-family:var(--serif);font-size:18px;line-height:1.7;color:var(--text);">
            <style scoped>
                .serif h2 { font-family: var(--display); font-size: 30px; font-weight: 500; letter-spacing: -0.02em; line-height: 1.15; margin: 38px 0 14px; color: var(--text); }
                .serif h3 { font-family: var(--display); font-size: 23px; font-weight: 500; letter-spacing: -0.015em; line-height: 1.2; margin: 28px 0 10px; color: var(--text); }
                .serif h4 { font-family: var(--display); font-size: 19px; font-weight: 500; letter-spacing: -0.01em; margin: 22px 0 8px; color: var(--text); }
                .serif p { margin: 0 0 20px; text-wrap: pretty; }
                .serif a { color: var(--accent); text-decoration: underline; text-underline-offset: 3px; text-decoration-thickness: 1px; }
                .serif a:hover { color: var(--accent-strong, var(--accent)); }
                .serif blockquote { margin: 24px 0; padding: 6px 20px; border-left: 3px solid var(--accent); font-style: italic; color: var(--text-2); }
                .serif ul, .serif ol { margin: 0 0 20px; padding-left: 24px; }
                .serif li { margin-bottom: 6px; }
                .serif code { font-family: var(--mono); font-size: 0.88em; background: var(--surface-2); padding: 2px 6px; border-radius: var(--r-xs); }
                .serif pre { font-family: var(--mono); font-size: 14px; background: var(--surface-2); padding: 16px 18px; border-radius: var(--r-sm); overflow-x: auto; margin: 0 0 20px; }
                .serif pre code { background: none; padding: 0; font-size: inherit; }
                .serif img { max-width: 100%; height: auto; border-radius: var(--r-sm); margin: 20px 0; display: block; }
                .serif hr { border: 0; border-top: 1px solid var(--border); margin: 32px 0; }
                .serif strong { color: var(--text); font-weight: 600; }
                .serif table { width: 100%; border-collapse: collapse; margin: 0 0 20px; font-family: var(--sans); font-size: 14px; }
                .serif th, .serif td { padding: 10px 12px; border-bottom: 1px solid var(--border); text-align: left; }
                .serif th { font-family: var(--mono); font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); font-weight: 700; }
            </style>
            {!! $page->content !!}
        </div>
    </div>
</article>
@endsection
