{{-- resources/views/rfm/analysis/business.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Business Analytics Dashboard
        </h2>
    </x-slot>

    @push('styles')
    <style>
        .business-card { border-radius: 12px; }
        .business-chart-container { padding: 16px; border-radius: 10px; }
        .segment-champion { background-color: #10b981; color: white; }
        .segment-loyal { background-color: #3b82f6; color: white; }
        .segment-at-risk { background-color: #f59e0b; color: white; }
        .segment-lost { background-color: #ef4444; color: white; }
        .segment-cold { background-color: #6b7280; color: white; }
        .segment-promising { background-color: #8b5cf6; color: white; }
        .segment-new { background-color: #06b6d4; color: white; }
        .segment-regular { background-color: #84cc16; color: white; }
        .segment-slipping { background-color: #f97316; color: white; }
    </style>
    @endpush

    <div class="p-6 space-y-6">
        {{-- Key Metrics Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 shadow business-card p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Customers</h3>
                <p class="text-2xl font-bold text-blue-600" id="totalCustomers">-</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow business-card p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Average RFM Score</h3>
                <p class="text-2xl font-bold text-green-600" id="avgRfmScore">-</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow business-card p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Champions (Top 20%)</h3>
                <p class="text-2xl font-bold text-purple-600" id="championsCount">-</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow business-card p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">At Risk Customers</h3>
                <p class="text-2xl font-bold text-red-600" id="atRiskCount">-</p>
            </div>
        </div>

        {{-- RFM Segmentation Matrix --}}
        <div class="bg-white dark:bg-gray-800 shadow business-card">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Segmentation Matrix</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">RFM-based customer segmentation for targeted marketing</p>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div class="text-center font-semibold p-2">High Value</div>
                    <div class="text-center font-semibold p-2">Medium Value</div>
                    <div class="text-center font-semibold p-2">Low Value</div>
                    
                    <div class="segment-champion text-center p-3 rounded">
                        <div class="font-bold">Champions</div>
                        <div id="champions">-</div>
                    </div>
                    <div class="segment-loyal text-center p-3 rounded">
                        <div class="font-bold">Loyal</div>
                        <div id="loyal">-</div>
                    </div>
                    <div class="segment-at-risk text-center p-3 rounded">
                        <div class="font-bold">At Risk</div>
                        <div id="atRisk">-</div>
                    </div>
                    
                    <div class="segment-promising text-center p-3 rounded">
                        <div class="font-bold">Promising</div>
                        <div id="promising">-</div>
                    </div>
                    <div class="segment-regular text-center p-3 rounded">
                        <div class="font-bold">Regular</div>
                        <div id="regular">-</div>
                    </div>
                    <div class="segment-slipping text-center p-3 rounded">
                        <div class="font-bold">Slipping</div>
                        <div id="slipping">-</div>
                    </div>
                    
                    <div class="segment-new text-center p-3 rounded">
                        <div class="font-bold">New</div>
                        <div id="new">-</div>
                    </div>
                    <div class="segment-cold text-center p-3 rounded">
                        <div class="font-bold">Cold</div>
                        <div id="cold">-</div>
                    </div>
                    <div class="segment-lost text-center p-3 rounded">
                        <div class="font-bold">Lost</div>
                        <div id="lost">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Revenue Trend Analysis --}}
            <div class="bg-white dark:bg-gray-800 shadow business-card">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue vs RFM Score Trend</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Correlation between RFM scores and revenue performance</p>
                </div>
                <div class="business-chart-container" style="min-height: 400px;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>

            {{-- Customer Lifetime Value Distribution --}}
            <div class="bg-white dark:bg-gray-800 shadow business-card">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Lifetime Value Distribution</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Distribution of customer value across your customer base</p>
                </div>
                <div class="business-chart-container" style="min-height: 400px;">
                    <canvas id="clvDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Average Order Value by Segment --}}
            <div class="bg-white dark:bg-gray-800 shadow business-card">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Average Order Value by Segment</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Which customer segments generate the highest value per order</p>
                </div>
                <div class="business-chart-container" style="min-height: 400px;">
                    <canvas id="aovBySegmentChart"></canvas>
                </div>
            </div>

            {{-- Purchase Frequency Analysis --}}
            <div class="bg-white dark:bg-gray-800 shadow business-card">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Purchase Frequency Heatmap</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Seasonal patterns in customer purchasing behavior</p>
                </div>
                <div class="business-chart-container" style="min-height: 400px;">
                    <canvas id="purchaseFrequencyChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Customer Cohort Analysis --}}
        <div class="bg-white dark:bg-gray-800 shadow business-card">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Retention Cohort Analysis</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">How well you retain customers over time by acquisition period</p>
            </div>
            <div class="business-chart-container" style="min-height: 400px;">
                <canvas id="cohortChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        // Real RFM data passed from controller
        const RFM_DATA = @json($rfmData ?? []);
        console.log('RFM data for business analytics:', RFM_DATA);
        
        // Business analytics functions
        function segmentCustomer(rfmScore, recency, frequency, monetary) {
            // RFM Segmentation logic
            if (rfmScore >= 8 && recency >= 7 && frequency >= 7 && monetary >= 7) return 'Champions';
            if (rfmScore >= 6 && recency >= 5 && frequency >= 5 && monetary >= 5) return 'Loyal';
            if (rfmScore >= 4 && recency >= 4 && frequency >= 4 && monetary >= 4) return 'At Risk';
            if (rfmScore >= 6 && recency >= 6 && frequency >= 4 && monetary >= 4) return 'Promising';
            if (rfmScore >= 5 && recency >= 4 && frequency >= 5 && monetary >= 4) return 'Regular';
            if (rfmScore >= 4 && recency >= 3 && frequency >= 4 && monetary >= 3) return 'Slipping';
            if (rfmScore >= 5 && recency >= 7 && frequency >= 3 && monetary >= 3) return 'New';
            if (rfmScore >= 3 && recency >= 2 && frequency >= 3 && monetary >= 2) return 'Cold';
            return 'Lost';
        }
        
        function calculateCLV(rfmScore, frequency, monetary, avgOrderValue = 500) {
            const retentionRate = Math.min(0.9, 0.5 + (rfmScore / 20));
            const avgLifespan = 1 / (1 - retentionRate);
            const avgOrderFrequency = frequency / 12;
            return avgOrderValue * avgOrderFrequency * avgLifespan;
        }
        
        function processBusinessData() {
            if (!RFM_DATA || RFM_DATA.length === 0) {
                console.log('No RFM data available for business analytics');
                return null;
            }
            
            // Group by client and calculate business metrics
            const clientMetrics = {};
            
            RFM_DATA.forEach(item => {
                const clientName = item.client_name || `Client ${item.client_id}`;
                if (!clientMetrics[clientName]) {
                    clientMetrics[clientName] = {
                        name: clientName,
                        rfmScores: [],
                        recencyScores: [],
                        frequencyScores: [],
                        monetaryScores: [],
                        dates: [],
                        totalRevenue: 0
                    };
                }
                
                clientMetrics[clientName].rfmScores.push(parseFloat(item.rfm_score));
                clientMetrics[clientName].recencyScores.push(parseFloat(item.r_score));
                clientMetrics[clientName].frequencyScores.push(parseFloat(item.f_score));
                clientMetrics[clientName].monetaryScores.push(parseFloat(item.m_score));
                clientMetrics[clientName].dates.push(new Date(item.date));
                
                // Estimate revenue based on monetary score
                const estimatedRevenue = parseFloat(item.m_score) * 1000;
                clientMetrics[clientName].totalRevenue += estimatedRevenue;
            });
            
            // Calculate business metrics
            const segments = {};
            const revenueData = [];
            const clvData = [];
            const aovData = {};
            const purchaseFrequency = {};
            
            Object.values(clientMetrics).forEach(client => {
                const latestIndex = client.rfmScores.length - 1;
                const latestRFM = client.rfmScores[latestIndex];
                const latestRecency = client.recencyScores[latestIndex];
                const latestFrequency = client.frequencyScores[latestIndex];
                const latestMonetary = client.monetaryScores[latestIndex];
                
                // Segment customer
                const segment = segmentCustomer(latestRFM, latestRecency, latestFrequency, latestMonetary);
                segments[segment] = (segments[segment] || 0) + 1;
                
                // Revenue trend data
                revenueData.push({
                    client: client.name,
                    rfmScore: latestRFM,
                    revenue: client.totalRevenue,
                    date: client.dates[latestIndex]
                });
                
                // CLV data
                const clv = calculateCLV(latestRFM, latestFrequency, latestMonetary);
                clvData.push({
                    client: client.name,
                    clv: clv,
                    rfmScore: latestRFM
                });
                
                // AOV by segment
                const aov = client.totalRevenue / client.rfmScores.length;
                if (!aovData[segment]) aovData[segment] = [];
                aovData[segment].push(aov);
                
                // Purchase frequency by month
                client.dates.forEach(date => {
                    const monthKey = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
                    if (!purchaseFrequency[monthKey]) purchaseFrequency[monthKey] = 0;
                    purchaseFrequency[monthKey]++;
                });
            });
            
            return {
                segments,
                revenueData,
                clvData,
                aovData,
                purchaseFrequency,
                totalCustomers: Object.keys(clientMetrics).length,
                avgRfmScore: Object.values(clientMetrics).reduce((sum, client) => 
                    sum + client.rfmScores[client.rfmScores.length - 1], 0) / Object.keys(clientMetrics).length
            };
        }
        
        // Initialize charts
        function createSegmentationMatrix(data) {
            // Update segment counts
            Object.keys(data.segments).forEach(segment => {
                const element = document.getElementById(segment.toLowerCase().replace(' ', ''));
                if (element) {
                    element.textContent = data.segments[segment];
                }
            });
            
            // Update summary metrics
            document.getElementById('totalCustomers').textContent = data.totalCustomers;
            document.getElementById('avgRfmScore').textContent = data.avgRfmScore.toFixed(1);
            document.getElementById('championsCount').textContent = data.segments['Champions'] || 0;
            document.getElementById('atRiskCount').textContent = data.segments['At Risk'] || 0;
        }
        
        function createRevenueTrendChart(data) {
            const ctx = document.getElementById('revenueTrendChart').getContext('2d');
            
            // Sort by date
            const sortedData = data.revenueData.sort((a, b) => a.date - b.date);
            
            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Revenue vs RFM Score',
                        data: sortedData.map(d => ({
                            x: d.rfmScore,
                            y: d.revenue
                        })),
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgb(59, 130, 246)',
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const client = sortedData[context.dataIndex];
                                    return `${client.client}: RFM ${client.rfmScore}, Revenue £${client.revenue.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'RFM Score' },
                            min: 0, max: 10
                        },
                        y: {
                            title: { display: true, text: 'Revenue (£)' },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        function createCLVDistributionChart(data) {
            const ctx = document.getElementById('clvDistributionChart').getContext('2d');
            
            // Create CLV ranges
            const clvRanges = [
                { min: 0, max: 1000, label: '£0-1K' },
                { min: 1000, max: 5000, label: '£1K-5K' },
                { min: 5000, max: 10000, label: '£5K-10K' },
                { min: 10000, max: 25000, label: '£10K-25K' },
                { min: 25000, max: Infinity, label: '£25K+' }
            ];
            
            const clvCounts = clvRanges.map(range => 
                data.clvData.filter(d => d.clv >= range.min && d.clv < range.max).length
            );
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: clvRanges.map(r => r.label),
                    datasets: [{
                        label: 'Number of Customers',
                        data: clvCounts,
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Number of Customers' }
                        }
                    }
                }
            });
        }
        
        function createAOVBySegmentChart(data) {
            const ctx = document.getElementById('aovBySegmentChart').getContext('2d');
            
            const segments = Object.keys(data.aovData);
            const avgAOV = segments.map(segment => {
                const aovs = data.aovData[segment];
                return aovs.reduce((sum, aov) => sum + aov, 0) / aovs.length;
            });
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: segments,
                    datasets: [{
                        label: 'Average Order Value (£)',
                        data: avgAOV,
                        backgroundColor: 'rgba(147, 51, 234, 0.6)',
                        borderColor: 'rgb(147, 51, 234)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Average Order Value (£)' }
                        }
                    }
                }
            });
        }
        
        function createPurchaseFrequencyChart(data) {
            const ctx = document.getElementById('purchaseFrequencyChart').getContext('2d');
            
            const months = Object.keys(data.purchaseFrequency).sort();
            const frequencies = months.map(month => data.purchaseFrequency[month]);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months.map(month => {
                        const [year, monthNum] = month.split('-');
                        return `${monthNum}/${year.slice(2)}`;
                    }),
                    datasets: [{
                        label: 'Purchase Frequency',
                        data: frequencies,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Number of Purchases' }
                        }
                    }
                }
            });
        }
        
        function createCohortChart(data) {
            const ctx = document.getElementById('cohortChart').getContext('2d');
            
            // Simulate cohort data (in real app, this would come from actual retention data)
            const cohortData = {
                labels: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'],
                datasets: [
                    {
                        label: 'Q1 2024 Cohort',
                        data: [100, 85, 72, 65, 58, 52],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3
                    },
                    {
                        label: 'Q2 2024 Cohort',
                        data: [100, 88, 75, 68, 61],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3
                    },
                    {
                        label: 'Q3 2024 Cohort',
                        data: [100, 90, 78, 70],
                        borderColor: 'rgb(147, 51, 234)',
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        tension: 0.3
                    }
                ]
            };
            
            new Chart(ctx, {
                type: 'line',
                data: cohortData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: { display: true, text: 'Retention Rate (%)' }
                        }
                    }
                }
            });
        }
        
        // Initialize all charts
        document.addEventListener('DOMContentLoaded', function() {
            const businessData = processBusinessData();
            
            if (businessData) {
                createSegmentationMatrix(businessData);
                createRevenueTrendChart(businessData);
                createCLVDistributionChart(businessData);
                createAOVBySegmentChart(businessData);
                createPurchaseFrequencyChart(businessData);
                createCohortChart(businessData);
            } else {
                // Show empty state
                const containers = document.querySelectorAll('.business-chart-container');
                containers.forEach(container => {
                    const empty = document.createElement('div');
                    empty.textContent = 'No data available for business analytics. Ensure you have sufficient RFM data.';
                    empty.className = 'p-6 text-center text-sm text-gray-500 dark:text-gray-400';
                    container.appendChild(empty);
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
