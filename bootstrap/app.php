<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->web(prepend: [
            \App\Http\Middleware\SelectDatabaseByDomain::class,
            \App\Http\Middleware\ScraperBlock::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\ScraperBlock::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\MinifyHtml::class,
            \App\Http\Middleware\SanitizeLivewireUploadFilename::class,
            \App\Http\Middleware\LogLivewireUploads::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ThemeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Страница не найдена'], 404);
            }
            return redirect('/')->with('error', 'Страница не найдена. Вы перешли на главную.');
        });

        $exceptions->reportable(function (\Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException $e) {
            try {
                $req = request();
                $components = (array) $req->input('components', []);
                $summary = [];
                foreach ($components as $i => $c) {
                    $snap = is_array($c) ? ($c['snapshot'] ?? null) : null;
                    $summary[$i] = [
                        'snapshot_type'   => gettype($snap),
                        'snapshot_length' => is_string($snap) ? strlen($snap) : null,
                        'snapshot_head'   => is_string($snap) ? mb_substr($snap, 0, 600) : null,
                        'snapshot_tail'   => is_string($snap) ? mb_substr($snap, -200) : null,
                        'updates_keys'    => is_array($c) ? array_keys($c['updates'] ?? []) : null,
                        'calls'           => is_array($c) ? ($c['calls'] ?? []) : null,
                    ];
                }
                \Log::error('Livewire CorruptComponent debug', [
                    'url'            => $req->fullUrl(),
                    'referer'        => $req->header('referer'),
                    'user_id'        => optional($req->user())->id,
                    'host'           => $req->getHost(),
                    'ua'             => $req->userAgent(),
                    'components'     => $summary,
                ]);
            } catch (\Throwable $ignore) {
            }
        });
    })->create();
