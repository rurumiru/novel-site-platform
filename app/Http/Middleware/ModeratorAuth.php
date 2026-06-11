<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ModeratorAuth {
    public function handle(Request $request, Closure $next) {
        if (!$request->user()) {
            return redirect()->route('login');
        }
        if ($request->user()->hasRole('super_admin')) {
            return $next($request);
        }
        if (!$request->user()->hasRole('moderator')) {
            abort(403);
        }
        if (session('moderator_unlocked')) {
            return $next($request);
        }
        return redirect()->route('moderator.password');
    }
}
