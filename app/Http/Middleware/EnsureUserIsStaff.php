<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Allow admin and manager roles.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }
        $role = Auth::user()->role ?? null;
        if (!in_array($role, ['admin', 'manager'], true)) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
}

