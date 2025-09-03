<?php

namespace App\Providers;

use App\Models\XeroConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Webfox\Xero\Events\XeroAuthorized;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MariaDB compatibility: limit default string length to avoid index byte-size issues on older MariaDB
        try {
            if (config('database.default') === 'mariadb') {
                \Illuminate\Support\Facades\Schema::defaultStringLength(191);
            }
        } catch (\Throwable $e) {
            // no-op if Schema not available in this context
        }
        // Force HTTPS URL generation when configured (e.g., using ngrok)
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // When Xero auth succeeds, persist credentials + tenant to our DB for the logged-in user
        Event::listen(XeroAuthorized::class, function (XeroAuthorized $event): void {
            $userId = Auth::id();
            if (! $userId) {
                return; // only persist when a user is logged in
            }

            try {
                // Get all available connections from Xero Identity API
                /** @var IdentityApi $identity */
                $identity = app(IdentityApi::class);
                $connections = $identity->getConnections();
                
                // Check if this is the first connection for this user
                $isFirstConnection = !XeroConnection::where('user_id', $userId)->exists();
                
                foreach ($connections as $connection) {
                    $tenantId = $connection->getTenantId();
                    $orgName = $connection->getTenantName();
                    
                    // Check if this tenant is already connected
                    $existingConnection = XeroConnection::where('user_id', $userId)
                        ->where('tenant_id', $tenantId)
                        ->first();

                    if ($existingConnection) {
                        // Update existing connection with new tokens and set as active
                        $existingConnection->update([
                            'org_name'      => $orgName,
                            'access_token'  => Crypt::encryptString($event->token),
                            'refresh_token' => Crypt::encryptString($event->refresh_token),
                            'expires_at'    => \Carbon\CarbonImmutable::createFromTimestampUTC((int) $event->expires)->toDateTimeString(),
                            'is_active'     => true, // Set as active when reconnecting
                        ]);
                        
                        // Deactivate all other connections for this user
                        XeroConnection::where('user_id', $userId)
                            ->where('id', '!=', $existingConnection->id)
                            ->update(['is_active' => false]);
                    } else {
                        // Create new connection
                        $newConnection = XeroConnection::create([
                            'user_id'       => $userId,
                            'tenant_id'     => $tenantId,
                            'org_name'      => $orgName,
                            'access_token'  => Crypt::encryptString($event->token),
                            'refresh_token' => Crypt::encryptString($event->refresh_token),
                            'expires_at'    => \Carbon\CarbonImmutable::createFromTimestampUTC((int) $event->expires)->toDateTimeString(),
                            'is_active'     => $isFirstConnection,
                        ]);
                        
                        // If this is the first connection, deactivate any others (safety check)
                        if ($isFirstConnection) {
                            XeroConnection::where('user_id', $userId)
                                ->where('id', '!=', $newConnection->id)
                                ->update(['is_active' => false]);
                        }
                        
                        // Only set the first new connection as active
                        $isFirstConnection = false;
                    }
                }
                
                // Clear temporary session data after storing in database
                session()->forget(['xero_temp_token', 'xero_temp_refresh_token', 'xero_temp_id_token', 'xero_temp_expires', 'xero_temp_tenants']);
            } catch (\Throwable $e) {
                // Fallback to the original method if Identity API fails
                $tenantId = $event->tenants[0]['Id'] ?? null;
                if (! $tenantId) {
                    return;
                }

                $orgName = $event->tenants[0]['Name'] ?? null;
                try {
                    if (! $orgName) {
                        /** @var AccountingApi $accounting */
                        $accounting = app(AccountingApi::class);
                        $orgs = $accounting->getOrganisations($tenantId)?->getOrganisations();
                        $orgName = $orgs[0]?->getName() ?? null;
                    }
                } catch (\Throwable $e2) {
                    // ignore, optional
                }

                // Check if this tenant is already connected
                $existingConnection = XeroConnection::where('user_id', $userId)
                    ->where('tenant_id', $tenantId)
                    ->first();

                if ($existingConnection) {
                    // Update existing connection and set as active
                    $existingConnection->update([
                        'org_name'      => $orgName,
                        'access_token'  => Crypt::encryptString($event->token),
                        'refresh_token' => Crypt::encryptString($event->refresh_token),
                        'expires_at'    => \Carbon\CarbonImmutable::createFromTimestampUTC((int) $event->expires)->toDateTimeString(),
                        'is_active'     => true, // Set as active when reconnecting
                    ]);
                    
                    // Deactivate all other connections for this user
                    XeroConnection::where('user_id', $userId)
                        ->where('id', '!=', $existingConnection->id)
                        ->update(['is_active' => false]);
                } else {
                    // Create new connection and set as active if it's the first one
                    $isFirstConnection = !XeroConnection::where('user_id', $userId)->exists();
                    
                    $newConnection = XeroConnection::create([
                        'user_id'       => $userId,
                        'tenant_id'     => $tenantId,
                        'org_name'      => $orgName,
                        'access_token'  => Crypt::encryptString($event->token),
                        'refresh_token' => Crypt::encryptString($event->refresh_token),
                        'expires_at'    => \Carbon\CarbonImmutable::createFromTimestampUTC((int) $event->expires)->toDateTimeString(),
                        'is_active'     => $isFirstConnection,
                    ]);
                    
                    // If this is the first connection, deactivate any others (safety check)
                    if ($isFirstConnection) {
                        XeroConnection::where('user_id', $userId)
                            ->where('id', '!=', $newConnection->id)
                            ->update(['is_active' => false]);
                    }
                }
                
                // Clear temporary session data after storing in database
                session()->forget(['xero_temp_token', 'xero_temp_refresh_token', 'xero_temp_id_token', 'xero_temp_expires', 'xero_temp_tenants']);
            }
        });
    }
}
