<?php

namespace App\Http\Controllers;

use App\Models\XeroConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    /**
     * Show organization management page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $organizations = $user->getAllXeroConnections();
        $activeOrg = $user->getActiveXeroConnection();

        return view('organizations.index', [
            'organizations' => $organizations,
            'activeOrg' => $activeOrg,
        ]);
    }

    /**
     * Switch to a different organization
     */
    public function switch(Request $request, int $connectionId)
    {
        $user = $request->user();
        
        // Verify the connection belongs to this user
        $connection = XeroConnection::where('id', $connectionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Ensure data integrity before switching
        XeroConnection::ensureSingleActiveConnection($user->id);

        // Set as active
        $connection->setActive();

        return redirect()->back()->with('status', "Switched to {$connection->org_name}");
    }

    /**
     * Disconnect an organization
     */
    public function disconnect(Request $request, int $connectionId)
    {
        $user = $request->user();
        
        // Verify the connection belongs to this user
        $connection = XeroConnection::where('id', $connectionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $orgName = $connection->org_name;

        // Delete the connection and all associated data
        DB::transaction(function () use ($connection, $user) {
            // Delete RFM reports for this organization
            DB::table('rfm_reports')
                ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                ->where('clients.user_id', $user->id)
                ->where('clients.tenant_id', $connection->tenant_id)
                ->delete();

            // Delete invoices for this organization
            DB::table('xero_invoices')
                ->where('user_id', $user->id)
                ->where('tenant_id', $connection->tenant_id)
                ->delete();

            // Delete clients for this organization
            DB::table('clients')
                ->where('user_id', $user->id)
                ->where('tenant_id', $connection->tenant_id)
                ->delete();

            // Delete the connection
            $connection->delete();
        });

        return redirect()->back()->with('status', "Disconnected from {$orgName}");
    }
}
