<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check() || ! auth()->user()->role) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role->key ?? '';

        if (in_array('not_admin', $roles)) {
            if ($userRole === 'admin') {
                abort(403, 'Unauthorized access');
            }
        } else {
            if (! in_array($userRole, $roles)) {
                abort(403, 'Unauthorized access');
            }
        }

        return $next($request);
    }
}
