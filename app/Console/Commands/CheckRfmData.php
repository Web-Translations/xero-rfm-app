<?php

namespace App\Console\Commands;

use App\Models\XeroInvoice;
use App\Models\RfmReport;
use Illuminate\Console\Command;

class CheckRfmData extends Command
{
    protected $signature = 'rfm:check-data';
    protected $description = 'Check if RFM data exists in the database';

    public function handle()
    {
        $this->info('Checking RFM data...');
        
        $totalInvoices = XeroInvoice::count();
        $invoicesWithRfm = XeroInvoice::whereNotNull('rfm_score')->count();
        $invoicesWithR = XeroInvoice::whereNotNull('r_score')->count();
        $invoicesWithF = XeroInvoice::whereNotNull('f_score')->count();
        $invoicesWithM = XeroInvoice::whereNotNull('m_score')->count();
        
        $this->info("Total invoices: {$totalInvoices}");
        $this->info("Invoices with RFM scores: {$invoicesWithRfm}");
        $this->info("Invoices with R scores: {$invoicesWithR}");
        $this->info("Invoices with F scores: {$invoicesWithF}");
        $this->info("Invoices with M scores: {$invoicesWithM}");
        
        // Check RfmReport data (what charts use)
        $totalRfmReports = RfmReport::count();
        $this->info("Total RFM reports: {$totalRfmReports}");
        
        // Show sample data
        if ($totalRfmReports > 0) {
            $this->info("\nSample RFM Report data:");
            $sample = RfmReport::first();
            $this->info("Client: {$sample->client_name}");
            $this->info("RFM Score: {$sample->rfm_score}");
            $this->info("Date: {$sample->snapshot_date}");
            
            // Show unique clients
            $uniqueClients = RfmReport::distinct('client_name')->pluck('client_name');
            $this->info("\nUnique clients in RFM reports: " . $uniqueClients->count());
            $this->info("Client names: " . $uniqueClients->implode(', '));
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
