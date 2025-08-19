<?php

namespace App\Services\Xero;

use App\Models\XeroConnection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Webfox\Xero\Oauth2CredentialManagers\BaseCredentialManager;
use Webfox\Xero\OauthCredentialManager;
use Webfox\Xero\Exceptions\XeroCredentialsNotFound;
use Carbon\Carbon;

class DatabaseCredentialManager extends BaseCredentialManager implements OauthCredentialManager
{
    protected ?XeroConnection $activeConnection = null;

    public function __construct()
    {
        parent::__construct();
        $this->loadActiveConnection();
    }

    protected function loadActiveConnection(): void
    {
        if (Auth::check()) {
            // Force a fresh query to get the latest data
            $this->activeConnection = XeroConnection::where('user_id', Auth::id())
                ->where('is_active', true)
                ->first();
                
            \Log::info('loadActiveConnection called', [
                'user_id' => Auth::id(),
                'active_connection_id' => $this->activeConnection->id ?? null,
                'active_connection_expires_at' => $this->activeConnection->expires_at ?? null,
            ]);
        }
    }

    public function exists(): bool
    {
        // Check for temporary session data during OAuth callback
        if (session()->has('xero_temp_token')) {
            return true;
        }
        
        return $this->activeConnection !== null;
    }

    public function getAccessToken(): string
    {
        if (!$this->exists()) {
            throw XeroCredentialsNotFound::make();
        }

        // During OAuth callback, return temporary session data
        if (session()->has('xero_temp_token')) {
            return session('xero_temp_token');
        }

        return Crypt::decryptString($this->activeConnection->access_token);
    }

    public function getRefreshToken(): string
    {
        if (!$this->exists()) {
            throw XeroCredentialsNotFound::make();
        }

        // During OAuth callback, return temporary session data
        if (session()->has('xero_temp_refresh_token')) {
            return session('xero_temp_refresh_token');
        }

        return Crypt::decryptString($this->activeConnection->refresh_token);
    }

    public function getExpires(): int
    {
        if (!$this->exists()) {
            throw XeroCredentialsNotFound::make();
        }

        // During OAuth callback, return temporary session data
        if (session()->has('xero_temp_expires')) {
            return session('xero_temp_expires');
        }

        // Return the UTC timestamp directly (no timezone conversion needed)
        return $this->activeConnection->expires_at->timestamp;
    }

    public function isExpired(): bool
    {
        // If we are in the middle of callback temp session, compute using session expiry
        if (session()->has('xero_temp_expires')) {
            return time() >= (int) session('xero_temp_expires');
        }

        if (!$this->exists()) {
            return true;
        }

        // Guard against null active connection
        if (!$this->activeConnection) {
            return true;
        }

        return $this->activeConnection->isExpired();
    }

    public function refresh(): void
    {
        if (!$this->exists()) {
            throw XeroCredentialsNotFound::make();
        }

        // Guard against null active connection
        if (!$this->activeConnection) {
            throw new \Exception('No active Xero connection found');
        }

        try {
            \Log::info('Starting manual token refresh', [
                'user_id' => $this->activeConnection->user_id ?? null,
                'connection_id' => $this->activeConnection->id ?? null,
                'current_expires_at' => $this->activeConnection->expires_at ?? null,
            ]);

            // Get new access token using refresh token (same as parent method)
            $newAccessToken = $this->oauthProvider->getAccessToken('refresh_token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->getRefreshToken(),
            ]);

            \Log::info('Got new access token from Xero', [
                'new_expires_at' => $newAccessToken->getExpires(),
                'has_refresh_token' => !empty($newAccessToken->getRefreshToken()),
            ]);

            // Update the database with new tokens
            $newExpiresAt = $newAccessToken->getExpires();
            
            $updated = $this->activeConnection->update([
                'access_token' => Crypt::encryptString($newAccessToken->getToken()),
                'refresh_token' => Crypt::encryptString($newAccessToken->getRefreshToken()),
                'expires_at' => $newExpiresAt, // Store raw UTC timestamp
            ]);

            \Log::info('Database update result', [
                'update_successful' => $updated,
                'new_expires_at_stored' => $newExpiresAt,
                'connection_id' => $this->activeConnection->id,
                'user_id' => $this->activeConnection->user_id,
            ]);

            // Reload the connection
            $this->loadActiveConnection();
            
            \Log::info('Connection reloaded after update', [
                'new_expires_at_in_db' => $this->activeConnection->expires_at ?? null,
                'is_expired_after_update' => $this->activeConnection->isExpired(),
                'connection_id' => $this->activeConnection->id ?? null,
            ]);
            
            // Also verify the database was actually updated by querying it directly
            $freshConnection = XeroConnection::find($this->activeConnection->id);
            \Log::info('Direct database query result', [
                'fresh_expires_at' => $freshConnection->expires_at ?? null,
                'fresh_is_expired' => $freshConnection->isExpired(),
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->activeConnection->user_id ?? null,
                'connection_id' => $this->activeConnection->id ?? null,
            ]);
            
            // Re-throw the exception
            throw $e;
        }
    }

    public function store(AccessTokenInterface $token, array $tenants = null): void
    {
        // This method is called by the package during OAuth callback
        // We need to store the tokens temporarily so the callback process
        // The actual storage in the database is handled in AppServiceProvider via the XeroAuthorized event
        
        // Store tokens in session temporarily for the callback process
        $tokenValues = $token->getValues();
        
        session([
            'xero_temp_token' => $token->getToken(),
            'xero_temp_refresh_token' => $token->getRefreshToken(),
            'xero_temp_id_token' => $tokenValues['id_token'] ?? null,
            'xero_temp_expires' => $token->getExpires(),
            'xero_temp_tenants' => $tenants,
        ]);
    }

    protected function data(string $key = null)
    {
        // During OAuth callback, check for temporary session data first
        if (session()->has('xero_temp_token')) {
            $data = [
                'token' => session('xero_temp_token'),
                'refresh_token' => session('xero_temp_refresh_token'),
                'id_token' => session('xero_temp_id_token'),
                'expires' => session('xero_temp_expires'),
                'tenants' => session('xero_temp_tenants'),
            ];
            
            return empty($key) ? $data : ($data[$key] ?? null);
        }

        // Otherwise, use database data
        if (!$this->exists()) {
            throw XeroCredentialsNotFound::make();
        }

        $data = [
            'token' => $this->getAccessToken(),
            'refresh_token' => $this->getRefreshToken(),
            'expires' => $this->getExpires(),
            'tenants' => $this->getTenants(),
        ];

        return empty($key) ? $data : ($data[$key] ?? null);
    }

    public function getTenants(): ?array
    {
        if (!$this->exists()) {
            return null;
        }

        // During OAuth callback, return temporary session data
        if (session()->has('xero_temp_tenants')) {
            return session('xero_temp_tenants');
        }

        // Return the tenant data in the format expected by the package
        return [
            [
                'Id' => $this->activeConnection->tenant_id,
                'Name' => $this->activeConnection->org_name,
            ]
        ];
    }

    public function getTenantId(int $tenant = 0): string
    {
        if (!$this->exists()) {
            throw new \Exception('No active Xero connection found');
        }

        return $this->activeConnection->tenant_id;
    }

    public function getUser(): ?array
    {
        if (!$this->exists()) {
            return null;
        }

        // Return basic user info from the authenticated user
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return [
            'given_name' => $user->name,
            'family_name' => '',
            'email' => $user->email,
            'user_id' => (string) $user->id,
            'username' => $user->email,
            'session_id' => session()->getId(),
        ];
    }

    public function getData(): array
    {
        return $this->data();
    }

    /**
     * Check if refresh token is expired (60 days)
     */
    public function isRefreshTokenExpired(): bool
    {
        if (!$this->exists()) {
            return true;
        }

        // Refresh tokens typically expire after 60 days
        // We'll check if the connection was created more than 55 days ago as a safety margin
        $refreshTokenExpiryDate = $this->activeConnection->created_at->addDays(55);
        
        return now()->isAfter($refreshTokenExpiryDate);
    }

    /**
     * Get token status for UI display
     */
    public function getTokenStatus(): array
    {
        if (!$this->exists()) {
            return [
                'status' => 'not_connected',
                'message' => 'No Xero connection found',
                'can_refresh' => false,
                'needs_reconnect' => false,
            ];
        }

        $isExpired = $this->isExpired();
        $isRefreshTokenExpired = $this->isRefreshTokenExpired();

        if ($isRefreshTokenExpired) {
            return [
                'status' => 'refresh_token_expired',
                'message' => 'Connection expired - needs re-authentication',
                'can_refresh' => false,
                'needs_reconnect' => true,
            ];
        }

        if ($isExpired) {
            return [
                'status' => 'access_token_expired',
                'message' => 'Token expired - can be refreshed',
                'can_refresh' => true,
                'needs_reconnect' => false,
            ];
        }

        return [
            'status' => 'valid',
            'message' => 'Token is valid',
            'can_refresh' => false,
            'needs_reconnect' => false,
        ];
    }
}
