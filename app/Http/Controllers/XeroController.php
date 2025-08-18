<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\XeroConnection;
use App\Models\XeroInvoice;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Webfox\Xero\OauthCredentialManager;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;

class XeroController extends Controller
{
    // Step 1: send the user to Xero consent
    public function connect(OauthCredentialManager $xero)
    {
        // Ensure any temp callback tokens are cleared for a fresh connect flow
        Session::forget(['xero_temp_token', 'xero_temp_refresh_token', 'xero_temp_id_token', 'xero_temp_expires', 'xero_temp_tenants']);
        return redirect()->to($xero->getAuthorizationUrl());
    }

    // Step 2: handle callback, persist tokens + tenant (one org per user)
    // Note: callback is handled by the package route 'xero.auth.callback'.



    // Debug: fetch identity connections and organisation name, persist if found
    public function debugOrg(IdentityApi $identity, AccountingApi $accounting)
    {
        $user = auth()->user();
        abort_unless($user?->xeroConnection, 403, 'Connect Xero first.');

        $tenantId = $user->xeroConnection->tenant_id;

        $connections = [];
        $matched = null;
        try {
            foreach ($identity->getConnections() as $c) {
                $row = [
                    'tenant_id' => $c->getTenantId(),
                    'tenant_type' => $c->getTenantType(),
                    'tenant_name' => $c->getTenantName(),
                    'id' => $c->getId(),
                ];
                $connections[] = $row;
                if ($c->getTenantId() === $tenantId) {
                    $matched = $row;
                }
            }
        } catch (\Throwable $e) {
            $connections = ['error' => $e->getMessage()];
        }

        $orgsArr = [];
        try {
            $orgs = $accounting->getOrganisations($tenantId)?->getOrganisations() ?? [];
            foreach ($orgs as $o) {
                $orgsArr[] = [
                    'name' => $o->getName(),
                    'org_id' => $o->getOrganisationID(),
                    'is_demo' => $o->getIsDemoCompany(),
                ];
            }
        } catch (\Throwable $e) {
            $orgsArr = ['error' => $e->getMessage()];
        }

        // Persist best available org name
        $orgName = $matched['tenant_name'] ?? ($orgsArr[0]['name'] ?? null);
        if ($orgName) {
            $user->xeroConnection->update(['org_name' => $orgName]);
        }

        return view('debug.org', [
            'tenantId' => $tenantId,
            'connections' => $connections,
            'matched' => $matched,
            'organisations' => $orgsArr,
            'saved' => $orgName,
        ]);
    }
}

