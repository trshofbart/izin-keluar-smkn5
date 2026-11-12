<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SatpamMiddleware
{
    /**
     * Halaman satpam hanya untuk user login dengan role satpam atau admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['satpam', 'admin'], true)) {
            abort(403, 'Halaman ini hanya untuk satpam.');
        }

        return $next($request);
    }
}
