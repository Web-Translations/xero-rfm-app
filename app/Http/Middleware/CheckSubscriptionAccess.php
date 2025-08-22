<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = 'premium'): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has access to the requested feature
        $hasAccess = match ($feature) {
            'premium' => $user->canAccessPremium(),
            'ai' => $user->canAccessAI(),
            default => $user->hasActiveSubscription(),
        };

        if (!$hasAccess) {
            return redirect()->route('memberships.index')
                ->with('error', 'This feature requires a premium subscription. Please upgrade your plan to continue.');
        }

        return $next($request);
    }
}
