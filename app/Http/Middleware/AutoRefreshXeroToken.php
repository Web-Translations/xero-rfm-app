<?php

namespace App\Http\Middleware;

use App\Services\Xero\DatabaseCredentialManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Webfox\Xero\OauthCredentialManager;

class AutoRefreshXeroToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check tokens for authenticated users
        if (!auth()->check()) {
            return $next($request);
        }

        try {
            /** @var DatabaseCredentialManager $credentialManager */
            $credentialManager = app(OauthCredentialManager::class);
            
            // If no credentials exist, continue (user will be redirected to connect)
            if (!$credentialManager->exists()) {
                return $next($request);
            }

            $tokenStatus = $credentialManager->getTokenStatus();

            // If refresh token is expired, we can't auto-refresh
            if ($tokenStatus['needs_reconnect']) {
                return $next($request);
            }

            // If access token is expired but refresh token is valid, auto-refresh
            if ($tokenStatus['can_refresh']) {
                try {
                    $credentialManager->refresh();
                } catch (\Exception $e) {
                    // Log the error but don't break the request
                    Log::warning('Auto token refresh failed', [
                        'error' => $e->getMessage(),
                        'user_id' => auth()->id(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Log the error but don't break the request
            Log::warning('Token status check failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
        }

        return $next($request);
    }
}
