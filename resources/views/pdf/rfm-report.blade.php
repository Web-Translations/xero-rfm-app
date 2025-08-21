<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFM Analysis Report - {{ $reportData['organisation'] ?? 'Company' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: #ffffff;
            font-size: 14px;
        }
        
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            position: relative;
        }
        
        .header {
            background: #667eea;
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        

        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .header .subtitle {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .header-meta {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .meta-item {
            text-align: center;
        }
        
        .meta-label {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        
        .meta-value {
            font-size: 16px;
            font-weight: 600;
        }
        
        .content {
            padding: 25px 40px;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section.page-break {
            margin-top: 0;
            padding-top: 10px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 18px;
            margin-top: 0;
            padding-bottom: 8px;
            border-bottom: 3px solid #1e40af;
            position: relative;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            bottom: -3px;
            width: 60px;
            height: 3px;
            background: #1e40af;
        }
        
        .section-subtitle {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 14px;
            margin-top: 0;
            font-style: italic;
        }
        
        .subsection-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .report-intro {
            background: #f8fafc;
            border-left: 4px solid #1e40af;
            padding: 16px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }
        
        .report-intro p {
            margin-bottom: 12px;
            line-height: 1.7;
            color: #374151;
        }
        
        .report-intro p:last-child {
            margin-bottom: 0;
        }
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            position: relative;
        }
        
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }
        
        .kpi-card.revenue { --accent-color: #1e40af; }
        .kpi-card.customers { --accent-color: #059669; }
        .kpi-card.rfm { --accent-color: #7c3aed; }
        .kpi-card.aov { --accent-color: #d97706; }
        
        .kpi-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .kpi-change {
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .kpi-change.positive { color: #059669; }
        .kpi-change.negative { color: #dc2626; }
        .kpi-change.neutral { color: #64748b; }
        
        .insights-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .insight-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid var(--insight-color);
        }
        
        .insight-card.danger { --insight-color: #dc2626; }
        .insight-card.warning { --insight-color: #d97706; }
        .insight-card.info { --insight-color: #1e40af; }
        .insight-card.success { --insight-color: #059669; }
        
        .insight-text {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .insight-meta {
            display: flex;
            gap: 8px;
        }
        
        .insight-tag {
            background: #f3f4f6;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .data-table th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background: #fafbfc;
        }
        
        .data-table tr:hover {
            background: #f1f5f9;
        }
        
        .metric-value {
            font-weight: 600;
            color: #1e293b;
        }
        
        .change-indicator {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .change-up { color: #059669; }
        .change-down { color: #dc2626; }
        .change-neutral { color: #64748b; }
        
        .rfm-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            min-width: 40px;
        }
        
        .rfm-high { background: #d1fae5; color: #065f46; }
        .rfm-medium { background: #fef3c7; color: #92400e; }
        .rfm-low { background: #fee2e2; color: #991b1b; }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Specific spacing adjustments for page breaks */
        .section.page-break .section-title {
            margin-top: 0;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 16px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 16px;
        }
        
        .mt-4 {
            margin-top: 24px;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }
        
        @media print {
            .page { margin: 0; }
            .header { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>RFM Analysis Report</h1>
                <div class="subtitle">{{ $reportData['organisation'] ?? 'Company Name' }}</div>
                
                <div class="header-meta">
                    <div class="meta-item">
                        <div class="meta-label">Analysis Date</div>
                        <div class="meta-value">{{ \Carbon\Carbon::parse($reportData['date'] ?? now())->format('M j, Y') }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Period</div>
                        <div class="meta-value">{{ $reportData['rfm_window'] ?? 12 }} months</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Generated</div>
                        <div class="meta-value">{{ now()->format('M j, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Report Introduction -->
            <div class="section">
                <div class="report-intro">
                    <p><strong>Report Overview:</strong> This comprehensive RFM (Recency, Frequency, Monetary) analysis report provides detailed insights into customer behavior, revenue patterns, and business performance for {{ $reportData['organisation'] ?? 'your organisation' }}. The analysis covers a {{ $reportData['rfm_window'] ?? 12 }}-month period ending {{ \Carbon\Carbon::parse($reportData['date'] ?? now())->format('F j, Y') }}.</p>
                    
                    <p><strong>Methodology:</strong> This report utilizes the RFM framework to segment customers based on their purchasing behavior. Recency measures how recently a customer made a purchase, Frequency tracks how often they purchase, and Monetary value represents the total amount spent. These three dimensions are combined to create a comprehensive customer value score.</p>
                    
                    <p><strong>Key Findings:</strong> The analysis reveals critical insights about customer retention, revenue concentration, and growth opportunities that can inform strategic business decisions and marketing initiatives.</p>
                </div>
            </div>

            <!-- Executive Summary -->
            <div class="section">
                <h2 class="section-title">Executive Summary</h2>
                <div class="section-subtitle">Key performance indicators and business metrics for the analysis period</div>
                
                <div class="kpi-grid">
                    <div class="kpi-card revenue">
                        <div class="kpi-label">Total Revenue</div>
                        <div class="kpi-value">£{{ number_format($reportData['kpis']['current_period']['total_revenue'] ?? 0) }}</div>
                        @if(isset($reportData['kpis']['analysis']['revenue_change']))
                            <div class="kpi-change {{ $reportData['kpis']['analysis']['revenue_change'] >= 0 ? 'positive' : 'negative' }}">
                                {{ $reportData['kpis']['analysis']['revenue_change'] >= 0 ? '▲' : '▼' }} 
                                {{ abs(round($reportData['kpis']['analysis']['revenue_change'], 1)) }}% vs previous period
                            </div>
                        @endif
                    </div>
                    
                    <div class="kpi-card customers">
                        <div class="kpi-label">Active Customers</div>
                        <div class="kpi-value">{{ number_format($reportData['kpis']['current_period']['active_customers'] ?? 0) }}</div>
                        @if(isset($reportData['kpis']['analysis']['customer_change']))
                            <div class="kpi-change {{ $reportData['kpis']['analysis']['customer_change'] >= 0 ? 'positive' : 'negative' }}">
                                {{ $reportData['kpis']['analysis']['customer_change'] >= 0 ? '▲' : '▼' }} 
                                {{ abs(round($reportData['kpis']['analysis']['customer_change'], 1)) }}% vs previous period
                            </div>
                        @endif
                    </div>
                    
                    <div class="kpi-card rfm">
                        <div class="kpi-label">Average RFM Score</div>
                        <div class="kpi-value">{{ $reportData['kpis']['current_period']['average_rfm'] ?? 'N/A' }}</div>
                        @if(isset($reportData['kpis']['analysis']['rfm_change']))
                            <div class="kpi-change {{ $reportData['kpis']['analysis']['rfm_change'] >= 0 ? 'positive' : 'negative' }}">
                                {{ $reportData['kpis']['analysis']['rfm_change'] >= 0 ? '▲' : '▼' }} 
                                {{ abs(round($reportData['kpis']['analysis']['rfm_change'], 1)) }}% vs previous period
                            </div>
                        @endif
                    </div>
                    
                    <div class="kpi-card aov">
                        <div class="kpi-label">Average Order Value</div>
                        <div class="kpi-value">£{{ number_format($reportData['kpis']['current_period']['average_order_value'] ?? 0) }}</div>
                        @if(isset($reportData['kpis']['analysis']['aov_change']))
                            <div class="kpi-change {{ $reportData['kpis']['analysis']['aov_change'] >= 0 ? 'positive' : 'negative' }}">
                                {{ $reportData['kpis']['analysis']['aov_change'] >= 0 ? '▲' : '▼' }} 
                                {{ abs(round($reportData['kpis']['analysis']['aov_change'], 1)) }}% vs previous period
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Key Business Insights -->
            @if(!empty($reportData['kpis']['insights']))
            <div class="section">
                <h2 class="section-title">Key Business Insights</h2>
                <div class="section-subtitle">Critical observations and actionable recommendations based on customer behavior analysis</div>
                
                <div class="insights-grid">
                    @foreach(array_slice($reportData['kpis']['insights'], 0, 6) as $insight)
                        <div class="insight-card {{ $insight['type'] }}">
                            <div class="insight-text">{{ $insight['message'] }}</div>
                            <div class="insight-meta">
                                <span class="insight-tag">{{ ucfirst($insight['category']) }}</span>
                                <span class="insight-tag">{{ ucfirst($insight['priority']) }} Priority</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Customer Segments -->
            @if(!empty($reportData['kpis']['segments']))
            <div class="section">
                <h2 class="section-title">Customer Segmentation Analysis</h2>
                <div class="section-subtitle">Breakdown of customer base by RFM value and purchasing behavior patterns</div>
                
                <div class="grid-3">
                    <div class="kpi-card">
                        <div class="kpi-label">High Value</div>
                        <div class="kpi-value">{{ $reportData['kpis']['segments']['high_value']['count'] ?? 0 }}</div>
                        <div class="kpi-change neutral">RFM Score: 8-10</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Mid Value</div>
                        <div class="kpi-value">{{ $reportData['kpis']['segments']['mid_value']['count'] ?? 0 }}</div>
                        <div class="kpi-change neutral">RFM Score: 5-7</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Low Value</div>
                        <div class="kpi-value">{{ $reportData['kpis']['segments']['low_value']['count'] ?? 0 }}</div>
                        <div class="kpi-change neutral">RFM Score: 2-4</div>
                    </div>
                </div>
                
                <div class="grid-3 mt-4">
                    <div class="kpi-card">
                        <div class="kpi-label">At Risk</div>
                        <div class="kpi-value">{{ $reportData['kpis']['segments']['at_risk']['count'] ?? 0 }}</div>
                        <div class="kpi-change neutral">RFM Score: 0-1</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Inactive</div>
                        <div class="kpi-value">{{ $reportData['kpis']['segments']['inactive']['count'] ?? 0 }}</div>
                        <div class="kpi-change neutral">No Recent Activity</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Revenue Concentration -->
            @if(!empty($reportData['kpis']['concentration']))
            <div class="section">
                <h2 class="section-title">Revenue Concentration Analysis</h2>
                <div class="section-subtitle">Distribution of revenue across customer segments and concentration risk assessment</div>
                
                <div class="grid-3">
                    <div class="kpi-card">
                        <div class="kpi-label">Top 10 Customers</div>
                        <div class="kpi-value">{{ round($reportData['kpis']['concentration']['top_10_share'] ?? 0, 1) }}%</div>
                        <div class="kpi-change neutral">Revenue Share</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Top 50 Customers</div>
                        <div class="kpi-value">{{ round($reportData['kpis']['concentration']['top_50_share'] ?? 0, 1) }}%</div>
                        <div class="kpi-change neutral">Revenue Share</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Customers to 80%</div>
                        <div class="kpi-value">{{ $reportData['kpis']['concentration']['customers_to_80_percent'] ?? 0 }}</div>
                        <div class="kpi-change neutral">Revenue Concentration</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Top Customers Summary -->
            @if(!empty($reportData['kpis']['segments']))
            <div class="section">
                <h2 class="section-title">Customer Performance Overview</h2>
                <div class="section-subtitle">Summary of high-value customer metrics and overall customer base performance</div>
                
                <div class="grid-2">
                    <div class="kpi-card">
                        <div class="kpi-label">High Value Customers</div>
                        <div class="kpi-value">{{ $reportData['kpis']['segments']['high_value']['count'] ?? 0 }}</div>
                        <div class="kpi-change neutral">{{ isset($reportData['kpis']['segments']['high_value']['avg_rfm']) ? 'Avg RFM: ' . $reportData['kpis']['segments']['high_value']['avg_rfm'] : 'RFM Score 8-10' }}</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Total Active Customers</div>
                        <div class="kpi-value">{{ $reportData['kpis']['current_period']['active_customers'] ?? 0 }}</div>
                        <div class="kpi-change neutral">Generating Revenue</div>
                    </div>
                </div>
            </div>
            @endif



            <!-- Risk Analysis -->
            @if(!empty($reportData['kpis']['risk_analysis']))
            <div class="section">
                <h2 class="section-title">Risk Assessment and Analysis</h2>
                <div class="section-subtitle">Identification of potential business risks and areas requiring attention</div>
                
                <div class="insights-grid">
                    @foreach(array_slice($reportData['kpis']['risk_analysis'], 0, 4) as $risk)
                        <div class="insight-card warning">
                            <div class="insight-text">{{ $risk['title'] }}</div>
                            <div class="insight-meta">
                                <span class="insight-tag">Risk</span>
                                <span class="insight-tag">{{ $risk['severity'] ?? 'Medium' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Growth Opportunities -->
            @if(!empty($reportData['kpis']['opportunities']))
            <div class="section">
                <h2 class="section-title">Strategic Growth Opportunities</h2>
                <div class="section-subtitle">Identified opportunities for revenue growth and customer base expansion</div>
                
                <div class="insights-grid">
                    @foreach(array_slice($reportData['kpis']['opportunities'], 0, 4) as $opportunity)
                        <div class="insight-card success">
                            <div class="insight-text">{{ $opportunity['title'] }}</div>
                            <div class="insight-meta">
                                <span class="insight-tag">Opportunity</span>
                                <span class="insight-tag">{{ $opportunity['potential_impact'] ?? 'High' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Historical Trends -->
            @if(!empty($reportData['kpis']['historical_trends']))
            <div class="section page-break">
                <h2 class="section-title">Historical Performance Trends</h2>
                <div class="section-subtitle">Analysis of performance metrics over time to identify patterns and trends</div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Total Customers</th>
                            <th>Avg RFM</th>
                            <th>High Value</th>
                            <th>At Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($reportData['kpis']['historical_trends'], 0, 6) as $trend)
                            <tr>
                                <td class="font-medium">{{ $trend['formatted_date'] ?? \Carbon\Carbon::parse($trend['date'])->format('M j, Y') }}</td>
                                <td class="text-center">{{ $trend['total_customers'] ?? 0 }}</td>
                                <td class="text-center">{{ $trend['average_rfm'] ?? 0 }}</td>
                                <td class="text-center">{{ $trend['high_value_customers'] ?? 0 }}</td>
                                <td class="text-center">{{ $trend['at_risk_customers'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Customer Movement -->
            @if(!empty($reportData['kpis']['customer_movement_details']))
            <div class="section">
                <h2 class="section-title">Customer Movement Analysis</h2>
                <div class="section-subtitle">Analysis of how customer base composition has changed over the analysis period</div>
                
                <div class="grid-2">
                    <div class="kpi-card">
                        <div class="kpi-label">Retained Customers</div>
                        <div class="kpi-value">{{ $reportData['kpis']['customer_movement_details']['retained'] ?? 0 }}</div>
                        <div class="kpi-change neutral">Stayed active</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">New Customers</div>
                        <div class="kpi-value">{{ $reportData['kpis']['customer_movement_details']['new'] ?? 0 }}</div>
                        <div class="kpi-change neutral">First time active</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Returned Customers</div>
                        <div class="kpi-value">{{ $reportData['kpis']['customer_movement_details']['returned'] ?? 0 }}</div>
                        <div class="kpi-change neutral">Came back active</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Lost Customers</div>
                        <div class="kpi-value">{{ $reportData['kpis']['customer_movement_details']['lost'] ?? 0 }}</div>
                        <div class="kpi-change negative">Became inactive</div>
                    </div>
                </div>
                
                @if(isset($reportData['kpis']['customer_movement_details']['retention_rate']))
                <div class="kpi-card" style="margin-top: 20px;">
                    <div class="kpi-label">Customer Retention Rate</div>
                    <div class="kpi-value">{{ number_format($reportData['kpis']['customer_movement_details']['retention_rate'], 1) }}%</div>
                    <div class="kpi-change neutral">% stayed active</div>
                </div>
                @endif
            </div>
            @endif

            <!-- Top Ranking Changes -->
            @if(!empty($reportData['kpis']['customer_movement_details']['ranking_changes']))
            <div class="section">
                <h2 class="section-title">Customer Ranking Changes</h2>
                <div class="section-subtitle">Analysis of customers who have improved or declined in RFM ranking over the period</div>
                
                <div class="grid-2">
                    <div>
                        <h3 class="subsection-title" style="color: #059669;">📈 Top Ranking Improvers</h3>
                        @foreach(array_slice($reportData['kpis']['customer_movement_details']['ranking_changes'], 0, 5) as $change)
                            @if(strpos($change['rank_change'] ?? '', 'Up') !== false || strpos($change['rank_change'] ?? '', 'New') !== false)
                            <div class="insight-card success" style="margin-bottom: 8px;">
                                <div class="insight-text">{{ $change['client_name'] ?? 'N/A' }}</div>
                                <div class="insight-meta">
                                    <span class="insight-tag">{{ $change['rank_change'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    <div>
                        <h3 class="subsection-title" style="color: #dc2626;">📉 Top Ranking Decliners</h3>
                        @foreach(array_slice($reportData['kpis']['customer_movement_details']['ranking_changes'], 0, 5) as $change)
                            @if(strpos($change['rank_change'] ?? '', 'Down') !== false)
                            <div class="insight-card warning" style="margin-bottom: 8px;">
                                <div class="insight-text">{{ $change['client_name'] ?? 'N/A' }}</div>
                                <div class="insight-meta">
                                    <span class="insight-tag">{{ $change['rank_change'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Detailed RFM Scores -->
            @if(!empty($reportData['kpis']['detailed_rfm_scores']))
            <div class="section page-break">
                <h2 class="section-title">Detailed RFM Scores</h2>
                <div class="section-subtitle">Individual customer RFM analysis showing recency, frequency, and monetary scores</div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>R</th>
                            <th>F</th>
                            <th>M</th>
                            <th>RFM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($reportData['kpis']['detailed_rfm_scores'], 0, 15) as $score)
                            <tr>
                                <td class="font-medium">{{ $score['client_name'] ?? 'N/A' }}</td>
                                <td class="text-center">{{ number_format($score['r_score'] ?? 0, 1) }}</td>
                                <td class="text-center">{{ $score['f_score'] ?? 0 }}</td>
                                <td class="text-center">{{ number_format($score['m_score'] ?? 0, 1) }}</td>
                                <td class="text-center">
                                    <span class="rfm-badge {{ 
                                        ($score['rfm_score'] ?? 0) >= 8 ? 'rfm-high' : 
                                        (($score['rfm_score'] ?? 0) >= 5 ? 'rfm-medium' : 'rfm-low') 
                                    }}">
                                        {{ number_format($score['rfm_score'] ?? 0, 1) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="text-align: center; margin-top: 12px; font-size: 12px; color: #64748b;">
                    Showing top {{ min(15, count($reportData['kpis']['detailed_rfm_scores'])) }} of {{ count($reportData['kpis']['detailed_rfm_scores']) }} customers
                </div>
            </div>
            @endif

            <!-- Recently Lost Customers -->
            @if(!empty($reportData['kpis']['recently_lost_customers']))
            <div class="section">
                <h2 class="section-title">Recently Lost Customers</h2>
                <div class="section-subtitle">Customers who became inactive during the analysis period - potential re-engagement opportunities</div>
                
                <div class="insights-grid">
                    @foreach(array_slice($reportData['kpis']['recently_lost_customers'], 0, 6) as $lost)
                        <div class="insight-card danger">
                            <div class="insight-text">{{ $lost['client_name'] ?? 'N/A' }}</div>
                            <div class="insight-meta">
                                <span class="insight-tag">Lost</span>
                                <span class="insight-tag">Previous RFM: {{ $lost['previous_rfm'] ?? 'N/A' }}</span>
                                <span class="insight-tag">Inactive for: {{ $lost['months_inactive'] ?? 0 }} month(s)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if(count($reportData['kpis']['recently_lost_customers']) > 0)
                <div class="insight-card info" style="margin-top: 16px;">
                    <div class="insight-text">Re-engagement Opportunity</div>
                    <div class="insight-meta">
                        <span class="insight-tag">{{ count($reportData['kpis']['recently_lost_customers']) }} customers recently became inactive</span>
                        <span class="insight-tag">Prime candidates for re-engagement campaigns</span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Conclusion -->
            <div class="section page-break">
                <h2 class="section-title">Conclusion and Recommendations</h2>
                <div class="section-subtitle">Summary of key findings and strategic recommendations for business improvement</div>
                
                <div class="report-intro">
                    <p><strong>Executive Summary:</strong> This RFM analysis has identified several critical areas for attention and opportunity within {{ $reportData['organisation'] ?? 'your organisation' }}'s customer base. The analysis reveals both strengths to leverage and challenges that require strategic intervention.</p>
                    
                    <p><strong>Key Recommendations:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li style="margin-bottom: 8px;"><strong>Customer Retention:</strong> Focus on retaining high-value customers through personalized engagement strategies and loyalty programs.</li>
                        <li style="margin-bottom: 8px;"><strong>Revenue Diversification:</strong> Address revenue concentration risks by expanding the customer base and reducing dependency on top customers.</li>
                        <li style="margin-bottom: 8px;"><strong>Customer Re-engagement:</strong> Implement targeted campaigns to reactivate recently lost customers and improve overall retention rates.</li>
                        <li style="margin-bottom: 8px;"><strong>Performance Monitoring:</strong> Establish regular RFM analysis cycles to track progress and identify emerging trends.</li>
                    </ul>
                    
                    <p><strong>Next Steps:</strong> This report should be reviewed by key stakeholders and used to inform strategic planning, marketing initiatives, and customer relationship management strategies. Regular monitoring of these metrics will help track progress and identify new opportunities for growth.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Confidential Business Report - Generated on {{ now()->format('F j, Y \a\t g:i A') }} | RFM Analysis System</div>
        </div>
    </div>
</body>
</html>
