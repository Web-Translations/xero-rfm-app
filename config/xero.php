<?php

use Webfox\Xero\Oauth2CredentialManagers\FileStore;

return [
    'api_host' => 'https://api.xero.com/api.xro/2.0',

    // Store tokens on the filesystem for now (good for dev)
    'credential_store' => FileStore::class,
    'credential_disk' => env('XERO_CREDENTIAL_DISK', 'local'),

    'oauth' => [
        'client_id'     => env('XERO_CLIENT_ID'),
        'client_secret' => env('XERO_CLIENT_SECRET'),

        // Scopes: openid provides id_token required by the package; plus invoice read + refresh
        'scopes' => [
            'openid',
            'profile',
            'email',
            'offline_access',
            'accounting.transactions.read',
        ],

        // After tokens are saved, send user to the dashboard
        'redirect_on_success' => 'dashboard',

        // Use a full URL from .env (must exactly match the Xero app Redirect URI)
        'redirect_uri' => env('XERO_REDIRECT_URI'),
        'redirect_full_url' => true,

        // Xero OAuth endpoints (defaults are fine)
        'url_authorize' => 'https://login.xero.com/identity/connect/authorize',
        'url_access_token' => 'https://identity.xero.com/connect/token',
        'url_resource_owner_details' => 'https://api.xero.com/api.xro/2.0/Organisation',
    ],
];
