<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureXeroLinked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user has any Xero connections
        if ($user->getAllXeroConnections()->isEmpty()) {
            return redirect()->route('xero.connect')->with('status', 'Connect your Xero account first.');
        }

        // Check if user has an active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please select an active organisation.');
        }

        return $next($request);
    }
}

