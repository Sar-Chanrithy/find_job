<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PlatformAccess
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        abort_unless($request->user(), 401, 'Please sign in.');
        abort_unless($request->user()->is_active, 403, 'Your account is suspended.');
        abort_if($roles && !in_array($request->user()->role, $roles, true), 403, 'You do not have access to this action.');
        return $next($request);
    }
}
