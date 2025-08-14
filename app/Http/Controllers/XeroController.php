<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\XeroConnection;
use App\Models\XeroInvoice;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Webfox\Xero\OauthCredentialManager;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;

class XeroController extends Controller
{
    // Step 1: send the user to Xero consent
    public function connect(OauthCredentialManager $xero)
    {
        return redirect()->to($xero->getAuthorizationUrl());
    }

    // Step 2: handle callback, persist tokens + tenant (one org per user)
    // Note: callback is handled by the package route 'xero.auth.callback'.

    // Demo: fetch recent invoices, upsert locally, and render
    public function demoInvoices(OauthCredentialManager $xero)
    {
        $user = auth()->user();
        abort_unless($user?->xeroConnection, 403, 'Connect Xero first.');

        // AccountingApi is bound by the package; it uses tokens from session/credentials
        $api = app(AccountingApi::class);
        $tenantId = $user->xeroConnection->tenant_id;

        // last N days of sales invoices; include DRAFT even if Date is missing
        $days = (int) request('days', 90);
        $days = $days > 0 ? $days : 90;
        $where = sprintf(
            '(Type=="ACCREC")&&(Date>=DateTime(%s)||Status=="DRAFT")',
            now()->subDays($days)->format('Y,m,d')
        );

        $page = 1; $all = [];
        do {
            // Signature: ($tenantId, $ifModifiedSince=null, $where=null, $order=null,
            //             $ids=null, $invoiceNumbers=null, $contactIDs=null, $statuses=null,
            //             $page=null, $includeArchived=null, $createdByMyApp=null, $unitdp=null,
            //             $summaryOnly=null, $sentToContact=null, $isOverpaid=null)
            $resp = $api->getInvoices(
                $tenantId,
                null,
                $where,
                'Date DESC',
                null,
                null,
                null,
                null,
                $page,
                null,
                null,
                null,
                null,
                null,
                null
            );
            $batch = $resp?->getInvoices() ?? [];
            $all = array_merge($all, $batch);
            $page++;
        } while (count($batch) === 100);

        // upsert Clients + Invoices (idempotent)
        DB::transaction(function () use ($user, $all) {
            foreach ($all as $inv) {
                $contact = $inv->getContact();
                if ($contact) {
                    Client::updateOrCreate(
                        ['user_id' => $user->id, 'contact_id' => $contact->getContactId()],
                        ['name'    => $contact->getName()]
                    );
                }
                XeroInvoice::updateOrCreate(
                    ['user_id' => $user->id, 'invoice_id' => $inv->getInvoiceId()],
                    [
                        'contact_id'        => optional($contact)->getContactId(),
                        'status'            => $inv->getStatus(),
                        'type'              => $inv->getType(),
                        'invoice_number'    => $inv->getInvoiceNumber(),
                        // Some invoices can miss Date; fall back to UpdatedDateUTC, then DueDate, then today
                        'date'              => optional($inv->getDate())->format('Y-m-d')
                                                ?? optional($inv->getUpdatedDateUtc())->format('Y-m-d')
                                                ?? optional($inv->getDueDate())->format('Y-m-d')
                                                ?? now()->format('Y-m-d'),
                        'due_date'          => optional($inv->getDueDate())->format('Y-m-d'),
                        'subtotal'          => $inv->getSubTotal(),
                        'total'             => $inv->getTotal(),
                        'currency'          => $inv->getCurrencyCode(),
                        'updated_date_utc'  => optional($inv->getUpdatedDateUtc())->format('Y-m-d H:i:s')
                                                ?? now()->format('Y-m-d H:i:s'),
                        'fully_paid_at'     => optional($inv->getFullyPaidOnDate())->format('Y-m-d H:i:s'),
                    ]
                );
            }
        });

        return view('demo.invoices', ['invoices' => $all]);
    }

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

