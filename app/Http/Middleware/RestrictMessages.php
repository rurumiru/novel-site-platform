<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictMessages
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(['owner', 'super_admin', 'deputy_admin', 'moderator', 'editor', 'author'])) {
            abort(403, 'Сообщения доступны только авторам и администрации.');
        }

        return $next($request);
    }
}
