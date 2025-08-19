<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Analysis</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        <!-- Analysis Overview -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Analysis Tools</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Deep dive into your RFM data with advanced analytics and insights
                </p>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Total Clients -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Clients</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $summaryStats['total_clients'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Average RFM Score -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg RFM Score</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summaryStats['avg_rfm_score'] ?? 0, 1) }}</p>
                    </div>
                </div>
            </div>

            <!-- High Value Clients -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">High Value</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $summaryStats['high_value_clients'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- At Risk Clients -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">At Risk</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $summaryStats['at_risk_clients'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Activity</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $summaryStats['recent_activity'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Intelligence Charts -->
        <div class="space-y-6">
            <!-- Customer Value Distribution -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Value Distribution</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Understand how your customers are distributed across value tiers
                    </p>
                </div>
                <div class="p-6">
                    <canvas id="customerValueChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Revenue vs Customer Count Correlation -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue vs RFM Score</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            See the correlation between RFM scores and revenue generation
                        </p>
                    </div>
                    <div class="p-6">
                        <canvas id="revenueCorrelationChart" width="400" height="300"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Churn Risk</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Identify customers at risk of churning based on RFM patterns
                        </p>
                    </div>
                    <div class="p-6">
                        <canvas id="churnRiskChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Customer Lifetime Value Analysis -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Lifetime Value</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Projected lifetime value of customers based on RFM scores
                        </p>
                    </div>
                    <div class="p-6">
                        <canvas id="clvChart" width="400" height="300"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Purchase Frequency Trends</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            How often different customer segments make purchases
                        </p>
                    </div>
                    <div class="p-6">
                        <canvas id="purchaseFrequencyChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Customer Segmentation Matrix -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Segmentation Matrix</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Visual representation of customer segments for targeted marketing
                    </p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">High Value</div>
                        <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">Medium Value</div>
                        <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">Low Value</div>
                        
                        <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg">
                            <div class="font-bold text-green-800 dark:text-green-200">Champions</div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="championsCount">-</div>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg">
                            <div class="font-bold text-blue-800 dark:text-blue-200">Loyal</div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="loyalCount">-</div>
                        </div>
                        <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-lg">
                            <div class="font-bold text-yellow-800 dark:text-yellow-200">At Risk</div>
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" id="atRiskCount">-</div>
                        </div>
                        
                        <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-lg">
                            <div class="font-bold text-purple-800 dark:text-purple-200">Promising</div>
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" id="promisingCount">-</div>
                        </div>
                        <div class="bg-indigo-100 dark:bg-indigo-900 p-4 rounded-lg">
                            <div class="font-bold text-indigo-800 dark:text-indigo-200">Regular</div>
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="regularCount">-</div>
                        </div>
                        <div class="bg-orange-100 dark:bg-orange-900 p-4 rounded-lg">
                            <div class="font-bold text-orange-800 dark:text-orange-200">Slipping</div>
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400" id="slippingCount">-</div>
                        </div>
                        
                        <div class="bg-cyan-100 dark:bg-cyan-900 p-4 rounded-lg">
                            <div class="font-bold text-cyan-800 dark:text-cyan-200">New</div>
                            <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400" id="newCount">-</div>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="font-bold text-gray-800 dark:text-gray-200">Cold</div>
                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400" id="coldCount">-</div>
                        </div>
                        <div class="bg-red-100 dark:bg-red-900 p-4 rounded-lg">
                            <div class="font-bold text-red-800 dark:text-red-200">Lost</div>
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400" id="lostCount">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analysis Tools Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Trend Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ml-3">Trend Analysis</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Track RFM score changes over time for individual clients or segments</p>
                <a href="{{ route('rfm.analysis.trends') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    View Trends
                </a>
            </div>

            <!-- Segment Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ml-3">Segment Analysis</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Analyze client segments based on RFM scores and behavior patterns</p>
                <a href="{{ route('rfm.analysis.segments') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    View Segments
                </a>
            </div>

            <!-- Predictive Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ml-3">Predictive Analysis</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Forecast future client behavior and churn risk based on RFM patterns</p>
                <a href="{{ route('rfm.analysis.predictive') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors">
                    View Predictions
                </a>
            </div>

            <!-- Cohort Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ml-3">Cohort Analysis</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Track how client groups behave over time and identify patterns</p>
                <a href="{{ route('rfm.analysis.cohort') }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors">
                    View Cohorts
                </a>
            </div>

            <!-- Comparative Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ml-3">Comparative Analysis</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Compare RFM performance across different time periods or segments</p>
                <a href="{{ route('rfm.analysis.comparative') }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                    Compare Periods
                </a>
            </div>

            <!-- Custom Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ml-3">Custom Analysis</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Create custom analysis queries and visualizations</p>
                <a href="#" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">
                    Coming Soon
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Real RFM data passed from controller
        const RFM_DATA = @json($rfmData ?? []);
        console.log('RFM data for business charts:', RFM_DATA);

        // Business intelligence functions
        function segmentCustomer(rfmScore, recency, frequency, monetary) {
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
                console.log('No RFM data available for business charts');
                return null;
            }

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
                
                const estimatedRevenue = parseFloat(item.m_score) * 1000;
                clientMetrics[clientName].totalRevenue += estimatedRevenue;
            });

            const segments = {};
            const revenueData = [];
            const clvData = [];
            const churnRiskData = [];
            const purchaseFrequencyData = {};

            Object.values(clientMetrics).forEach(client => {
                const latestIndex = client.rfmScores.length - 1;
                const latestRFM = client.rfmScores[latestIndex];
                const latestRecency = client.recencyScores[latestIndex];
                const latestFrequency = client.frequencyScores[latestIndex];
                const latestMonetary = client.monetaryScores[latestIndex];
                
                const segment = segmentCustomer(latestRFM, latestRecency, latestFrequency, latestMonetary);
                segments[segment] = (segments[segment] || 0) + 1;
                
                revenueData.push({
                    client: client.name,
                    rfmScore: latestRFM,
                    revenue: client.totalRevenue
                });
                
                const clv = calculateCLV(latestRFM, latestFrequency, latestMonetary);
                clvData.push({
                    client: client.name,
                    clv: clv,
                    rfmScore: latestRFM
                });

                // Calculate churn risk
                let churnRisk = 0;
                if (latestRecency > 6) churnRisk += 0.4;
                if (latestFrequency < 3) churnRisk += 0.3;
                if (latestMonetary < 0.3) churnRisk += 0.3;
                if (latestRFM < 3) churnRisk += 0.4;
                churnRisk = Math.min(churnRisk, 1);

                churnRiskData.push({
                    client: client.name,
                    risk: churnRisk,
                    rfmScore: latestRFM
                });

                // Purchase frequency by month
                client.dates.forEach(date => {
                    const monthKey = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
                    if (!purchaseFrequencyData[monthKey]) purchaseFrequencyData[monthKey] = 0;
                    purchaseFrequencyData[monthKey]++;
                });
            });

            return {
                segments,
                revenueData,
                clvData,
                churnRiskData,
                purchaseFrequencyData,
                totalCustomers: Object.keys(clientMetrics).length
            };
        }

        // Create Customer Value Distribution Chart
        function createCustomerValueChart(data) {
            const ctx = document.getElementById('customerValueChart');
            if (!ctx) return;

            const valueRanges = [
                { min: 0, max: 2, label: 'Low Value (0-2)', color: '#EF4444' },
                { min: 2, max: 4, label: 'Below Average (2-4)', color: '#F59E0B' },
                { min: 4, max: 6, label: 'Average (4-6)', color: '#3B82F6' },
                { min: 6, max: 8, label: 'High Value (6-8)', color: '#10B981' },
                { min: 8, max: 10, label: 'Premium (8-10)', color: '#8B5CF6' }
            ];

            const distribution = valueRanges.map(range => {
                return data.revenueData.filter(d => d.rfmScore >= range.min && d.rfmScore < range.max).length;
            });

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: valueRanges.map(r => r.label),
                    datasets: [{
                        data: distribution,
                        backgroundColor: valueRanges.map(r => r.color),
                        borderWidth: 2,
                        borderColor: '#1F2937'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Create Revenue vs RFM Score Correlation Chart
        function createRevenueCorrelationChart(data) {
            const ctx = document.getElementById('revenueCorrelationChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Revenue vs RFM Score',
                        data: data.revenueData.map(d => ({
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
                                    const client = data.revenueData[context.dataIndex];
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

        // Create Churn Risk Chart
        function createChurnRiskChart(data) {
            const ctx = document.getElementById('churnRiskChart');
            if (!ctx) return;

            const riskRanges = [
                { min: 0, max: 0.3, label: 'Low Risk', color: '#10B981' },
                { min: 0.3, max: 0.6, label: 'Medium Risk', color: '#F59E0B' },
                { min: 0.6, max: 1.0, label: 'High Risk', color: '#EF4444' }
            ];

            const riskDistribution = riskRanges.map(range => {
                return data.churnRiskData.filter(d => d.risk >= range.min && d.risk < range.max).length;
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: riskRanges.map(r => r.label),
                    datasets: [{
                        label: 'Number of Customers',
                        data: riskDistribution,
                        backgroundColor: riskRanges.map(r => r.color),
                        borderColor: riskRanges.map(r => r.color),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
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

        // Create Customer Lifetime Value Chart
        function createCLVChart(data) {
            const ctx = document.getElementById('clvChart');
            if (!ctx) return;

            const clvRanges = [
                { min: 0, max: 5000, label: '£0-5K' },
                { min: 5000, max: 15000, label: '£5K-15K' },
                { min: 15000, max: 30000, label: '£15K-30K' },
                { min: 30000, max: 50000, label: '£30K-50K' },
                { min: 50000, max: Infinity, label: '£50K+' }
            ];

            const clvDistribution = clvRanges.map(range => {
                return data.clvData.filter(d => d.clv >= range.min && d.clv < range.max).length;
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: clvRanges.map(r => r.label),
                    datasets: [{
                        label: 'Number of Customers',
                        data: clvDistribution,
                        backgroundColor: 'rgba(139, 92, 246, 0.6)',
                        borderColor: 'rgb(139, 92, 246)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
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

        // Create Purchase Frequency Chart
        function createPurchaseFrequencyChart(data) {
            const ctx = document.getElementById('purchaseFrequencyChart');
            if (!ctx) return;

            const months = Object.keys(data.purchaseFrequencyData).sort();
            const frequencies = months.map(month => data.purchaseFrequencyData[month]);

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

        // Update segmentation matrix
        function updateSegmentationMatrix(data) {
            Object.keys(data.segments).forEach(segment => {
                const element = document.getElementById(segment.toLowerCase().replace(' ', '') + 'Count');
                if (element) {
                    element.textContent = data.segments[segment];
                }
            });
        }

        // Initialize all charts
        document.addEventListener('DOMContentLoaded', function() {
            const businessData = processBusinessData();
            
            if (businessData) {
                createCustomerValueChart(businessData);
                createRevenueCorrelationChart(businessData);
                createChurnRiskChart(businessData);
                createCLVChart(businessData);
                createPurchaseFrequencyChart(businessData);
                updateSegmentationMatrix(businessData);
            } else {
                console.log('No business data available');
            }
        });
    </script>
    @endpush
</x-app-layout> 