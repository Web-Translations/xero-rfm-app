<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureXeroLinked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->xeroConnection) {
            return redirect()->route('xero.connect')->with('status', 'Connect your Xero account first.');
        }

        return $next($request);
    }
}

