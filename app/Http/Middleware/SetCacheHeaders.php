<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class SetCacheHeaders {
    public function handle(Request $request, Closure $next) {
        $response = $next($request);
        
        if (preg_match('/\.(?:jpg|jpeg|gif|png|ico|woff2|css|js)$/i', $request->getRequestUri())) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }
        
        return $response;
    }
}
