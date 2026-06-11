<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SelectDatabaseByDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if ($host === 'beta.localhost' || str_ends_with($host, '.beta.localhost')) {
            $betaConnection = config('database.connections.pgsql_beta')
                ? 'pgsql_beta'
                : (config('database.connections.mysql_beta') ? 'mysql_beta' : null);

            if ($betaConnection) {
                Config::set('database.default', $betaConnection);
            }
        }

        return $next($request);
    }
}
