@extends('layouts.app')
@section('title', 'Как оформить подписку')

@section('content')
<div class="eri-section" style="max-width:880px;">
    <div class="eri-card eri-card-pad-lg">
        <h1 style="font-family:var(--display);font-size:36px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 18px;padding-bottom:18px;border-bottom:1px solid var(--border);">
            Как оформить подписку
        </h1>
        <div style="font-family:var(--serif);font-size:17px;line-height:1.65;color:var(--text-2);">
            {!! $content !!}
        </div>
    </div>
</div>
@endsection
