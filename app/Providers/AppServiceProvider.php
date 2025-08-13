<?php

namespace App\Providers;

use App\Models\XeroConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
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
        // When Xero auth succeeds, persist credentials + tenant to our DB for the logged-in user
        Event::listen(XeroAuthorized::class, function (XeroAuthorized $event): void {
            $userId = Auth::id();
            if (! $userId) {
                return; // only persist when a user is logged in
            }

            $tenantId = $event->tenants[0]['Id'] ?? null;
            if (! $tenantId) {
                return;
            }

            // Prefer tenant name from the event payload; fallback to Identity API, then Accounting API
            $orgName = $event->tenants[0]['Name'] ?? null;
            try {
                if (! $orgName) {
                    /** @var IdentityApi $identity */
                    $identity = app(IdentityApi::class);
                    $connections = $identity->getConnections();
                    foreach ($connections as $c) {
                        if ($c->getTenantId() === $tenantId) {
                            $orgName = $c->getTenantName();
                            break;
                        }
                    }
                }
                if (! $orgName) {
                    /** @var AccountingApi $accounting */
                    $accounting = app(AccountingApi::class);
                    $orgs = $accounting->getOrganisations($tenantId)?->getOrganisations();
                    $orgName = $orgs[0]?->getName() ?? null;
                }
            } catch (\Throwable $e) {
                // ignore, optional
            }

            XeroConnection::updateOrCreate(
                ['user_id' => $userId],
                [
                    'tenant_id'     => $tenantId,
                    'org_name'      => $orgName,
                    'access_token'  => Crypt::encryptString($event->token),
                    'refresh_token' => Crypt::encryptString($event->refresh_token),
                    'expires_at'    => CarbonImmutable::createFromTimestamp($event->expires),
                ]
            );
        });
    }
}
