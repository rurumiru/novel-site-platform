<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictApiDocs
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->hasRole('super_admin') && !$user->hasRole('moderator')) {
            abort(403, 'Доступ к API-документации только для администраторов.');
        }

        return $next($request);
    }
}
