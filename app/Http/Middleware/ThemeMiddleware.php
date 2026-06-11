<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class ThemeMiddleware {
    public function handle(Request $request, Closure $next) {
        $theme = $request->cookie('theme', 'dark');
        view()->share('theme', $theme);
        return $next($request);
    }
}
