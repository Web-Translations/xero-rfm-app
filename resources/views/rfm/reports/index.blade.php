<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Reports</h2>
    </x-slot>

    <div class="p-6 space-y-8">
        @if (session('status'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
            </div>
        @endif

        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-8 border border-indigo-200 dark:border-indigo-800">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full mb-4">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">RFM Analysis Reports</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                    Generate comprehensive RFM reports to understand your customer behavior, 
                    identify growth opportunities, and make data-driven decisions to improve your business performance.
                </p>
            </div>
        </div>

        <!-- Quick Start Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Executive Summary -->
            <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl transition-shadow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Executive Summary</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Perfect for board meetings and stakeholder updates</p>
                </div>
            </div>

            <!-- Customer Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl transition-shadow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Customer Analysis</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Deep dive into customer segments and behavior</p>
                </div>
            </div>

            <!-- Trend Analysis -->
            <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-xl transition-shadow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Trend Analysis</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Track performance changes over time</p>
                </div>
            </div>
        </div>

        <!-- Custom Report Builder -->
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                    Report Builder
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Create a tailored report with your specific requirements</p>
            </div>

            <div class="p-6">
                <form method="GET" action="{{ route('rfm.reports.generate') }}" class="space-y-8">
                    <!-- Step 1: What do you want to analyze? -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 text-sm font-semibold rounded-full">1</span>
                            What do you want to analyze?
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Analysis Date
                                    </span>
                                </label>
                                <select name="snapshot_date" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @if($availableSnapshots->isNotEmpty())
                                        @foreach($availableSnapshots as $date)
                                            <option value="{{ $date }}">
                                                {{ \Carbon\Carbon::parse($date)->format('F j, Y') }} Snapshot
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Choose which data snapshot to analyze</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Analysis Window
                                    </span>
                                </label>
                                <select name="rfm_window" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="6">6 Months (Recent activity focus)</option>
                                    <option value="12" selected>12 Months (Standard analysis)</option>
                                    <option value="18">18 Months (Extended view)</option>
                                    <option value="24">24 Months (Long-term trends)</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This affects how we calculate customer activity and revenue metrics. The RFM scores use your configured settings.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: How do you want to compare? -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 text-sm font-semibold rounded-full">2</span>
                            How do you want to compare?
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        Comparison Period
                                    </span>
                                </label>
                                <select name="comparison_period" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="monthly">Monthly (vs Previous Month)</option>
                                    <option value="quarterly">Quarterly (vs Previous Quarter)</option>
                                    <option value="yearly">Yearly (vs Previous Year)</option>
                                    <option value="none">No Comparison (Current data only)</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Compare with previous periods to see trends</p>
                            </div>

                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <h5 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    What's Included
                                </h5>
                                <p class="text-xs text-blue-700 dark:text-blue-300">
                                    Every report includes: Executive Summary, Customer Segments, Revenue Analysis, 
                                    Risk Assessment, Growth Opportunities, and Detailed Customer Movement Tracking. 
                                    The Analysis Window affects revenue calculations and customer activity metrics.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Current Settings -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Current RFM Settings
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-blue-700 dark:text-blue-300">Recency:</span>
                                <span class="ml-2 font-medium text-blue-900 dark:text-blue-100">{{ $config->recency_window_months }} months</span>
                            </div>
                            <div>
                                <span class="text-blue-700 dark:text-blue-300">Frequency:</span>
                                <span class="ml-2 font-medium text-blue-900 dark:text-blue-100">{{ $config->frequency_period_months }} months</span>
                            </div>
                            <div>
                                <span class="text-blue-700 dark:text-blue-300">Monetary:</span>
                                <span class="ml-2 font-medium text-blue-900 dark:text-blue-100">{{ $config->monetary_window_months }} months</span>
                            </div>
                            <div>
                                <span class="text-blue-700 dark:text-blue-300">Benchmark:</span>
                                <span class="ml-2 font-medium text-blue-900 dark:text-blue-100">
                                    @if($config->monetary_benchmark_mode === 'percentile')
                                        Top {{ $config->monetary_benchmark_percentile }}%
                                    @else
                                        £{{ number_format($config->monetary_benchmark_value, 2) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('rfm.config.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                Change RFM Settings →
                            </a>
                        </div>
                    </div>

                    <div class="flex justify-center gap-4">
                        <button type="submit" class="px-8 py-4 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition-colors flex items-center gap-2 text-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Generate Report
                        </button>
                        
                        <button type="submit" formaction="{{ route('rfm.reports.pdf.generate') }}" class="px-8 py-4 rounded-lg bg-green-600 text-white hover:bg-green-700 font-medium transition-colors flex items-center gap-2 text-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- What You'll Get -->
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">What You'll Get in Your Report</h3>
                <p class="text-gray-600 dark:text-gray-400">Comprehensive analysis to drive better decisions</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Revenue Insights</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total revenue, average order value, and revenue per customer trends</p>
                </div>
                
                <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Customer Segments</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">High-value, mid-value, low-value, and at-risk customer breakdown</p>
                </div>
                
                <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Concentration Risk</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Revenue concentration analysis and customer distribution</p>
                </div>
                
                <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Customer Movement</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">New, returned, and lost customer analysis with retention rates</p>
                </div>
                
                <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Trend Analysis</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Period-over-period performance changes and growth trends</p>
                </div>
                
                <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Actionable Insights</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Business recommendations and next steps for improvement</p>
                </div>
            </div>
        </div>

        <!-- Enhanced Features -->
        <div class="bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 rounded-xl p-8 border border-green-200 dark:border-green-800">
            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Advanced Analytics</h3>
                <p class="text-gray-600 dark:text-gray-400">Advanced analytics that go beyond basic RFM scoring</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Risk Assessment</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Identify concentration and churn risks</p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Growth Opportunities</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Spot upselling and retention chances</p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Historical Trends</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Track performance over multiple periods</p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg mb-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Ranking Changes</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Monitor customer position movements</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 