<?php

namespace App\Http\Controllers;

use App\Services\Xero\DatabaseCredentialManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webfox\Xero\OauthCredentialManager;

class TokenController extends Controller
{
    /**
     * Refresh the current access token
     */
    public function refresh(Request $request)
    {
        Log::info('Token refresh request received', [
            'user_id' => $request->user()->id ?? null,
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'expects_json' => $request->expectsJson(),
        ]);
        
        try {
            /** @var DatabaseCredentialManager $credentialManager */
            $credentialManager = app(OauthCredentialManager::class);
            
            // If no credentials exist, return error
            if (!$credentialManager->exists()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No Xero connection found. Please connect to Xero first.'
                    ], 400);
                }
                return redirect()->route('xero.connect')
                    ->withErrors('No Xero connection found. Please connect to Xero first.');
            }

            $tokenStatus = $credentialManager->getTokenStatus();

            // If refresh token is expired, we can't refresh
            if ($tokenStatus['needs_reconnect']) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Your connection has expired. Please reconnect to Xero.'
                    ], 400);
                }
                return redirect()->route('xero.connect')
                    ->withErrors('Your connection has expired. Please reconnect to Xero.');
            }

            // Refresh the token (same logic as automated refresh)
            Log::info('Calling credentialManager->refresh()');
            $credentialManager->refresh();
            Log::info('credentialManager->refresh() completed successfully');

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Token refreshed successfully!'
                ]);
            }

            return back()->with('status', 'Token refreshed successfully!');

        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to refresh token: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors('Failed to refresh token. Please try reconnecting to Xero.');
        }
    }

    /**
     * Get token status for AJAX requests
     */
    public function status(Request $request)
    {
        try {
            /** @var DatabaseCredentialManager $credentialManager */
            $credentialManager = app(OauthCredentialManager::class);
            
            $tokenStatus = $credentialManager->getTokenStatus();

            return response()->json([
                'status' => $tokenStatus['status'],
                'message' => $tokenStatus['message'],
                'can_refresh' => $tokenStatus['can_refresh'],
                'needs_reconnect' => $tokenStatus['needs_reconnect'],
                'expires_at' => $credentialManager->exists() ? $credentialManager->getExpires() : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get token status',
                'can_refresh' => false,
                'needs_reconnect' => true,
            ]);
        }
    }

    /**
     * Force reconnection to Xero
     */
    public function reconnect(Request $request)
    {
        try {
            // Clear any existing tokens from the database
            $user = $request->user();
            if ($user) {
                $user->xeroConnections()->update(['is_active' => false]);
            }
            
            // Clear any temporary session tokens
            session()->forget(['xero_temp_token', 'xero_temp_refresh_token', 'xero_temp_id_token', 'xero_temp_expires', 'xero_temp_tenants']);
            
            return redirect()->route('xero.connect')
                ->with('status', 'Please reconnect to Xero to refresh your access.');
        } catch (\Exception $e) {
            Log::error('Reconnect failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);
            
            return redirect()->route('xero.connect')
                ->withErrors('Failed to clear existing connection. Please try again.');
        }
    }
}
