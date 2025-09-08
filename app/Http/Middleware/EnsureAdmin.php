<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\Http\Foundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->admin) {
            abort(403, 'This area is restricted to administrators.');
        }

        return $next($request);
    }
}


