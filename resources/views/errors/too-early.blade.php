<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Глава ещё не вышла</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Source+Serif+Pro:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/eriiba.css') }}">
    <link rel="stylesheet" href="{{ asset('css/eriiba-pages.css') }}">
    <style>body{background:var(--bg);color:var(--text);font-family:var(--sans);margin:0;}</style>
</head>
<body>
<div class="eri-section" style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;">
    <div style="max-width:480px;">
        <div style="font-family:var(--display);font-size:clamp(80px,14vw,140px);font-weight:500;line-height:1;letter-spacing:-0.04em;color:var(--text-faint);margin-bottom:8px;">
            <i class="fa-regular fa-clock"></i>
        </div>
        <h1 style="font-family:var(--display);font-size:32px;font-weight:500;letter-spacing:-0.02em;color:var(--text);margin:0 0 14px;">Слишком рано</h1>
        <p style="font-family:var(--serif);font-size:17px;line-height:1.55;color:var(--text-3);margin:0 0 6px;">Эта глава ещё не опубликована.</p>
        <p style="font-family:var(--mono);font-size:13px;color:var(--accent);margin:0 0 32px;">Дата публикации: {{ $chapter->published_at->format('d.m.Y H:i') }}</p>
        <a href="{{ route('novel.show', $chapter->novel_id) }}" class="eri-btn primary lg">Вернуться к новелле</a>
    </div>
</div>
</body>
</html>
