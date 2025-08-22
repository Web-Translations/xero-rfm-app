<?php

namespace App\Console\Commands;

use App\Models\XeroInvoice;
use App\Models\RfmReport;
use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;

class CheckRfmData extends Command
{
    protected $signature = 'rfm:check-data {--user-id=}';
    protected $description = 'Check if RFM data exists in the database';

    public function handle()
    {
        $this->info('Checking RFM data...');
        
        // Get user ID from option or use first user
        $userId = $this->option('user-id');
        if (!$userId) {
            $user = User::first();
            if (!$user) {
                $this->error('No users found in database');
                return 1;
            }
            $userId = $user->id;
        }
        
        $this->info("Checking data for user ID: {$userId}");
        
        $totalInvoices = XeroInvoice::where('user_id', $userId)->count();
        $invoicesWithRfm = XeroInvoice::where('user_id', $userId)->whereNotNull('rfm_score')->count();
        $invoicesWithR = XeroInvoice::where('user_id', $userId)->whereNotNull('r_score')->count();
        $invoicesWithF = XeroInvoice::where('user_id', $userId)->whereNotNull('f_score')->count();
        $invoicesWithM = XeroInvoice::where('user_id', $userId)->whereNotNull('m_score')->count();
        
        $this->info("Total invoices for user: {$totalInvoices}");
        $this->info("Invoices with RFM scores: {$invoicesWithRfm}");
        $this->info("Invoices with R scores: {$invoicesWithR}");
        $this->info("Invoices with F scores: {$invoicesWithF}");
        $this->info("Invoices with M scores: {$invoicesWithM}");
        
        // Check RfmReport data (what charts use)
        $totalRfmReports = RfmReport::where('user_id', $userId)->count();
        $this->info("Total RFM reports for user: {$totalRfmReports}");
        
        // Check clients
        $totalClients = Client::where('user_id', $userId)->count();
        $clientsWithTenant = Client::where('user_id', $userId)->whereNotNull('tenant_id')->count();
        $this->info("Total clients for user: {$totalClients}");
        $this->info("Clients with tenant_id: {$clientsWithTenant}");
        
        // Check RFM reports with tenant filtering (like the controller does)
        $rfmWithTenant = RfmReport::where('user_id', $userId)
            ->whereHas('client', function($q) {
                $q->whereNotNull('tenant_id');
            })->count();
        $this->info("RFM reports with tenant_id: {$rfmWithTenant}");
        
        // Show sample data
        if ($totalRfmReports > 0) {
            $this->info("\nSample RFM Report data:");
            $sample = RfmReport::with('client')->where('user_id', $userId)->first();
            $this->info("Client: " . ($sample->client->name ?? 'Unknown'));
            $this->info("Client tenant_id: " . ($sample->client->tenant_id ?? 'NULL'));
            $this->info("RFM Score: {$sample->rfm_score}");
            $this->info("Date: {$sample->snapshot_date}");
            
            // Show unique clients
            $uniqueClients = RfmReport::with('client')
                ->where('user_id', $userId)
                ->get()
                ->pluck('client.name')
                ->unique();
            $this->info("\nUnique clients in RFM reports: " . $uniqueClients->count());
            $this->info("Client names: " . $uniqueClients->implode(', '));
            
            // Check what the controller query would return
            $this->info("\nTesting controller query...");
            $controllerQuery = RfmReport::select([
                    'rfm_reports.snapshot_date as date',
                    'rfm_reports.r_score',
                    'rfm_reports.f_score',
                    'rfm_reports.m_score',
                    'rfm_reports.rfm_score',
                    'rfm_reports.client_id',
                    'clients.name as client_name',
                ])
                ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                ->where('rfm_reports.user_id', $userId)
                ->where('rfm_reports.rfm_score', '>', 0)
                ->orderBy('rfm_reports.snapshot_date', 'asc');
            
            $controllerResult = $controllerQuery->get();
            $this->info("Controller query result count: " . $controllerResult->count());
            
            if ($controllerResult->count() > 0) {
                $this->info("First result: " . json_encode($controllerResult->first()));
            }
            
            // Check tenant_id filtering
            $this->info("\nTesting tenant_id filtering...");
            $user = User::find($userId);
            $activeConnection = $user->getActiveXeroConnection();
            if ($activeConnection) {
                $this->info("Active connection tenant_id: " . $activeConnection->tenant_id);
                
                // Test with tenant filtering
                $controllerQueryWithTenant = RfmReport::select([
                        'rfm_reports.snapshot_date as date',
                        'rfm_reports.r_score',
                        'rfm_reports.f_score',
                        'rfm_reports.m_score',
                        'rfm_reports.rfm_score',
                        'rfm_reports.client_id',
                        'clients.name as client_name',
                    ])
                    ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                    ->where('rfm_reports.user_id', $userId)
                    ->where('clients.tenant_id', $activeConnection->tenant_id)
                    ->where('rfm_reports.rfm_score', '>', 0)
                    ->orderBy('rfm_reports.snapshot_date', 'asc');
                
                $controllerResultWithTenant = $controllerQueryWithTenant->get();
                $this->info("Controller query with tenant filtering result count: " . $controllerResultWithTenant->count());
                
                if ($controllerResultWithTenant->count() > 0) {
                    $this->info("First result with tenant: " . json_encode($controllerResultWithTenant->first()));
                }
            } else {
                $this->error("No active connection found for user!");
            }
        }
        
        if ($invoicesWithRfm === 0) {
            $this->error('No RFM data found! You need to sync invoices first.');
            $this->info('Go to /invoices and click "Sync Invoices" to calculate RFM scores.');
        } elseif ($totalRfmReports === 0) {
            $this->error('No RFM reports found! You need to sync RFM data.');
            $this->info('Go to /rfm and click "Sync RFM Data" to create RFM reports.');
        } else {
            $this->info('RFM data found! Charts should work.');
        }
        
        return 0;
    }
}
