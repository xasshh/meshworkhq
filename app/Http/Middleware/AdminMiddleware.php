<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards everything under /admin.
 *
 * These screens show identity documents and can hand out verified badges, so
 * they are gated on a flag that only the admin:grant command can set, never on
 * anything the user controls.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
