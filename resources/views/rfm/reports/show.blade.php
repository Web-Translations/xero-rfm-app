<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Report</h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        <!-- Navigation -->
        <div class="flex justify-between items-center">
            <a href="{{ route('rfm.reports.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Report Builder
            </a>
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Comprehensive RFM Analysis
                </div>
                <a href="{{ route('rfm.reports.pdf', [
                    'snapshot_date' => $currentSnapshotDate,
                    'rfm_window' => $rfmWindow,
                    'comparison_period' => $comparisonPeriod
                ]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                   onclick="console.log('PDF download clicked', this.href);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>

        <!-- Report Header -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-8 border border-indigo-200 dark:border-indigo-800">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">RFM Analysis Report</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        Analysis Date: {{ \Carbon\Carbon::parse($currentSnapshotDate)->format('F j, Y') }}
                        @if($comparisonSnapshotDate)
                            • Comparison: {{ \Carbon\Carbon::parse($comparisonSnapshotDate)->format('F j, Y') }}
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Generated</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ now()->format('M j, Y \a\t g:i A') }}</div>
                </div>
            </div>
            
            <!-- Report Configuration -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Analysis Window:</span>
                    <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ $rfmWindow }} months</span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Comparison:</span>
                    <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($comparisonPeriod) }}</span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Organisation:</span>
                    <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ $activeConnection->org_name }}</span>
                </div>
            </div>
        </div>

        <!-- Report Overview -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">About This Report</h3>
                    <p class="text-blue-800 dark:text-blue-200 text-sm leading-relaxed">
                        This comprehensive RFM report analyzes your customer base using Recency, Frequency, and Monetary methodology. 
                        It provides actionable insights into customer behavior, revenue patterns, and business performance trends. 
                        Use this data to identify growth opportunities, assess risks, and make informed strategic decisions.
                    </p>
                </div>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Executive Summary</h2>
                        <p class="text-gray-600 dark:text-gray-400">Key performance indicators and business metrics</p>
                    </div>
                </div>
                <!-- AI Insight Button -->
                <button 
                    id="ai-insights-btn-executive-summary"
                    data-section="executive-summary"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Insights
                </button>
            </div>
            
            <!-- Hidden KPIs Data for AI -->
            <script type="application/json" id="kpis-data">
                {!! json_encode($kpis, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
            </script>
            
            <!-- AI Insight Display Area -->
            <div id="ai-insight-executive-summary" class="hidden mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">AI-Generated Insights</h4>
                            <div id="ai-content-executive-summary" class="text-blue-800 dark:text-blue-200">
                                <div class="animate-pulse">
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Revenue</p>
                            <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">£{{ number_format($kpis['current_period']['total_revenue']) }}</p>
                        </div>
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-xl">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    @if(isset($kpis['analysis']['revenue_change']))
                        <div class="flex items-center gap-2">
                                                    <span class="text-lg {{ $kpis['analysis']['revenue_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $kpis['analysis']['revenue_change'] >= 0 ? '▲' : '▼' }} {{ abs(round((float)$kpis['analysis']['revenue_change'], 1)) }}%
                        </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">vs previous period</span>
                        </div>
                    @endif
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-2xl p-6 border border-green-200 dark:border-green-800 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-green-600 dark:text-green-400 font-medium">Active Customers</p>
                            <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ number_format($kpis['current_period']['active_customers']) }}</p>
                        </div>
                        <div class="p-2 bg-green-100 dark:bg-green-800 rounded-xl">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    @if(isset($kpis['analysis']['customer_change']))
                        <div class="flex items-center gap-2">
                                                    <span class="text-lg {{ $kpis['analysis']['customer_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $kpis['analysis']['customer_change'] >= 0 ? '▲' : '▼' }} {{ abs(round((float)$kpis['analysis']['customer_change'], 1)) }}%
                        </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">vs previous period</span>
                        </div>
                    @endif
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-800 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">Average RFM Score</p>
                            <p class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{ $kpis['current_period']['average_rfm'] }}</p>
                        </div>
                        <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-xl">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    @if(isset($kpis['analysis']['rfm_change']))
                        <div class="flex items-center gap-2">
                                                    <span class="text-lg {{ $kpis['analysis']['rfm_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $kpis['analysis']['rfm_change'] >= 0 ? '▲' : '▼' }} {{ abs(round((float)$kpis['analysis']['rfm_change'], 1)) }}%
                        </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">vs previous period</span>
                        </div>
                    @endif
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-2xl p-6 border border-orange-200 dark:border-orange-800 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Average Order Value</p>
                            <p class="text-3xl font-bold text-orange-900 dark:text-orange-100">£{{ number_format($kpis['current_period']['average_order_value']) }}</p>
                        </div>
                        <div class="p-2 bg-orange-100 dark:bg-orange-800 rounded-xl">
                            <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                    @if(isset($kpis['analysis']['aov_change']))
                        <div class="flex items-center gap-2">
                                                    <span class="text-lg {{ $kpis['analysis']['aov_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $kpis['analysis']['aov_change'] >= 0 ? '▲' : '▼' }} {{ abs(round((float)$kpis['analysis']['aov_change'], 1)) }}%
                        </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">vs previous period</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Key Insights -->
            @if(!empty($kpis['insights']))
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Key Business Insights
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(array_slice($kpis['insights'], 0, 4) as $insight)
                            <div class="flex items-start gap-3 p-4 rounded-lg border 
                                {{ $insight['type'] === 'danger' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : '' }}
                                {{ $insight['type'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800' : '' }}
                                {{ $insight['type'] === 'info' ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : '' }}">
                                <div class="p-2 rounded-full 
                                    {{ $insight['type'] === 'danger' ? 'bg-red-100 dark:bg-red-900' : '' }}
                                    {{ $insight['type'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900' : '' }}
                                    {{ $insight['type'] === 'info' ? 'bg-blue-100 dark:bg-blue-900' : '' }}">
                                    <svg class="w-5 h-5 
                                        {{ $insight['type'] === 'danger' ? 'text-red-600 dark:text-red-400' : '' }}
                                        {{ $insight['type'] === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : '' }}
                                        {{ $insight['type'] === 'info' ? 'text-blue-600 dark:text-blue-400' : '' }}" 
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $insight['message'] }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $insight['type'] === 'danger' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                            {{ $insight['type'] === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                            {{ $insight['type'] === 'info' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}">
                                            {{ ucfirst($insight['category']) }}
                                        </span>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ ucfirst($insight['priority']) }} Priority
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Customers Who Became Active -->
        @if(!empty(array_filter($kpis['customer_movement_details']['ranking_changes'], fn($c) => $c['rank_change'] === 'New Active')))
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Customers Who Became Active</h2>
                    <p class="text-gray-600 dark:text-gray-400">Recently re-engaged customers</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach(array_slice(array_filter($kpis['customer_movement_details']['ranking_changes'], fn($c) => $c['rank_change'] === 'New Active'), 0, 6) as $newActive)
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800 hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm leading-tight">{{ $newActive['client_name'] }}</h3>
                        </div>
                        <div class="ml-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="font-medium">RFM Change:</span> {{ $newActive['previous_rfm'] }} → {{ $newActive['current_rfm'] }}
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="font-medium">Improvement:</span> +{{ $newActive['rfm_change'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Customer Segments -->
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Customer Segments</h2>
                        <p class="text-gray-600 dark:text-gray-400">Breakdown of your customer base by RFM value</p>
                    </div>
                </div>
                <!-- AI Insight Button -->
                <button 
                    id="ai-insights-btn-customer-segments"
                    data-section="customer-segments"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Insights
                </button>
            </div>
            
            <!-- AI Insight Display Area -->
            <div id="ai-insight-customer-segments" class="hidden mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">AI-Generated Insights</h4>
                            <div id="ai-content-customer-segments" class="text-blue-800 dark:text-blue-200">
                                <div class="animate-pulse">
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <!-- Active Customer Segments -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-2xl p-6 border border-green-200 dark:border-green-800 text-center shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $kpis['segments']['high_value']['count'] }}</div>
                        <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">High Value</h3>
                        <p class="text-sm text-green-700 dark:text-green-300 mb-2">RFM Score: 8-10</p>
                        <p class="text-xs text-green-600 dark:text-green-400">Avg: {{ $kpis['segments']['high_value']['avg_rfm'] }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800 text-center shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">{{ $kpis['segments']['mid_value']['count'] }}</div>
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Mid Value</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">RFM Score: 5-7</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">Avg: {{ $kpis['segments']['mid_value']['avg_rfm'] }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-2xl p-6 border border-yellow-200 dark:border-yellow-800 text-center shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-4xl font-bold text-yellow-600 dark:text-yellow-400 mb-2">{{ $kpis['segments']['low_value']['count'] }}</div>
                        <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Low Value</h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-2">RFM Score: 2-4</p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400">Avg: {{ $kpis['segments']['low_value']['avg_rfm'] }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-2xl p-6 border border-red-200 dark:border-red-800 text-center shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-4xl font-bold text-red-600 dark:text-red-400 mb-2">{{ $kpis['segments']['at_risk']['count'] }}</div>
                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">At Risk</h3>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-2">RFM Score: 0-1</p>
                        <p class="text-xs text-red-600 dark:text-red-400">Avg: {{ $kpis['segments']['at_risk']['avg_rfm'] }}</p>
                    </div>
                </div>

                <!-- Inactive Customers - Full Width Highlight -->
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900/30 dark:to-slate-800/30 rounded-2xl p-8 border-2 border-slate-300 dark:border-slate-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-slate-200 dark:bg-slate-700 rounded-xl">
                                <svg class="w-8 h-8 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $kpis['segments']['inactive']['count'] ?? 0 }}</h3>
                                <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">Inactive Customers</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">RFM Score: 0 • No Recent Activity</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-slate-600 dark:text-slate-400">
                                {{ round((($kpis['segments']['inactive']['count'] ?? 0) / ($kpis['segments']['high_value']['count'] + $kpis['segments']['mid_value']['count'] + $kpis['segments']['low_value']['count'] + $kpis['segments']['at_risk']['count'] + ($kpis['segments']['inactive']['count'] ?? 0))) * 100, 1) }}%
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400">of total customers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Concentration -->
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Revenue Concentration</h2>
                        <p class="text-gray-600 dark:text-gray-400">How your revenue is distributed across customers</p>
                    </div>
                </div>
                <!-- AI Insight Button -->
                <button 
                    id="ai-insights-btn-revenue-concentration"
                    data-section="revenue-concentration"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Insights
                </button>
            </div>
            
            <!-- AI Insight Display Area -->
            <div id="ai-insight-revenue-concentration" class="hidden mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">AI-Generated Insights</h4>
                            <div id="ai-content-revenue-concentration" class="text-blue-800 dark:text-blue-200">
                                <div class="animate-pulse">
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-5xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ round($kpis['concentration']['top_10_share'], 1) }}%</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Top 10 Customers</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Revenue Share</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ round($kpis['concentration']['top_50_share'], 1) }}%</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Top 50 Customers</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Revenue Share</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ $kpis['concentration']['customers_to_80_percent'] }}</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Customers to 80%</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Revenue Concentration</p>
                </div>
            </div>
        </div>

        <!-- Customer Movement -->
        @if($comparisonSnapshotDate)
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Customer Movement</h2>
                        <p class="text-gray-600 dark:text-gray-400">How your customer base has changed over time</p>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Based on active customers (RFM > 0)</span>
                    </div>
                </div>
                <!-- AI Insight Button -->
                <button 
                    id="ai-insights-btn-customer-movement"
                    data-section="customer-movement"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Insights
                </button>
            </div>
            
            <!-- AI Insight Display Area -->
            <div id="ai-insight-customer-movement" class="hidden mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">AI-Generated Insights</h4>
                            <div id="ai-content-customer-movement" class="text-blue-800 dark:text-blue-200">
                                <div class="animate-pulse">
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                <div class="text-center p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">{{ $kpis['current_period']['customer_movement']['retained_customers'] }}</div>
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Retained</h3>
                    <p class="text-xs text-blue-700 dark:text-blue-300">Stayed active</p>
                </div>
                <div class="text-center p-6 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                    <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $kpis['current_period']['customer_movement']['new_customers'] }}</div>
                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">New</h3>
                    <p class="text-xs text-green-700 dark:text-green-300">First time active</p>
                </div>
                <div class="text-center p-6 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800">
                    <div class="text-4xl font-bold text-emerald-600 dark:text-emerald-400 mb-2">{{ $kpis['current_period']['customer_movement']['returned_customers'] }}</div>
                    <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100 mb-2">Returned</h3>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300">Came back active</p>
                </div>
                <div class="text-center p-6 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                    <div class="text-4xl font-bold text-red-600 dark:text-red-400 mb-2">{{ $kpis['current_period']['customer_movement']['lost_customers'] }}</div>
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">Lost</h3>
                    <p class="text-xs text-red-700 dark:text-red-300">Became inactive</p>
                </div>
                <div class="text-center p-6 bg-purple-50 dark:bg-purple-900/20 rounded-xl border border-purple-200 dark:border-purple-800">
                    <div class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">{{ round($kpis['current_period']['customer_movement']['retention_rate'], 1) }}%</div>
                    <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-2">Retention</h3>
                    <p class="text-xs text-purple-700 dark:text-purple-300">% stayed active</p>
                </div>
            </div>
            

        </div>
        @endif

        <!-- Top Movers -->
        @if($comparisonSnapshotDate && (!empty($kpis['movement']['improvers']) || !empty($kpis['movement']['decliners'])))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Improvers -->
            @if(!empty($kpis['movement']['improvers']))
            <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Top Improvers
                </h3>
                <div class="space-y-3">
                    @foreach(array_slice($kpis['movement']['improvers'], 0, 5) as $improver)
                    <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $improver['client_name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $improver['previous_rfm'] }} → {{ $improver['current_rfm'] }}</p>
                        </div>
                        <span class="text-green-600 dark:text-green-400 font-bold text-lg">+{{ $improver['change'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Top Decliners -->
            @if(!empty($kpis['movement']['decliners']))
            <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6 6"></path>
                    </svg>
                    Top Decliners
                </h3>
                <div class="space-y-3">
                    @foreach(array_slice($kpis['movement']['decliners'], 0, 5) as $decliner)
                    <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $decliner['client_name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $decliner['previous_rfm'] }} → {{ $decliner['current_rfm'] }}</p>
                        </div>
                        <span class="text-red-600 dark:text-red-400 font-bold text-lg">{{ $decliner['change'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Detailed RFM Data -->
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detailed RFM Scores</h2>
                    <p class="text-gray-600 dark:text-gray-400">Individual customer RFM analysis</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-gray-100">Client</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">R</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">F</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">M</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">RFM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($currentRfmData->take(10) as $report)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-gray-100">{{ $report->client_name }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-medium">
                                    {{ number_format($report->r_score, 1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 font-medium">
                                    {{ $report->f_score }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 font-medium">
                                    {{ number_format($report->m_score, 1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $rfm = (float) $report->rfm_score;
                                    $rfmClass = $rfm >= 7.0
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : ($rfm >= 5.0
                                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                            : ($rfm >= 3.0
                                                ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'));
                                @endphp
                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $rfmClass }}">
                                    {{ number_format($rfm, 1) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($currentRfmData->count() > 10)
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Showing top 10 of {{ $currentRfmData->count() }} customers</p>
                </div>
            @endif
        </div>

        <!-- Risk Analysis -->
        @if(!empty($kpis['risk_analysis']))
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Risk Assessment</h2>
                        <p class="text-gray-600 dark:text-gray-400">Identify potential business risks and areas of concern</p>
                    </div>
                </div>
                <!-- AI Insight Button -->
                <button 
                    id="ai-insights-btn-risk-assessment"
                    data-section="risk-assessment"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Insights
                </button>
            </div>
            
            <!-- AI Insight Display Area -->
            <div id="ai-insight-risk-assessment" class="hidden mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">AI-Generated Insights</h4>
                            <div id="ai-content-risk-assessment" class="text-blue-800 dark:text-blue-200">
                                <div class="animate-pulse">
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($kpis['risk_analysis'] as $risk)
                <div class="p-6 rounded-xl border 
                    {{ $risk['severity'] === 'high' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : '' }}
                    {{ $risk['severity'] === 'medium' ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800' : '' }}
                    {{ $risk['severity'] === 'low' ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-full 
                            {{ $risk['severity'] === 'high' ? 'bg-red-100 dark:bg-red-900' : '' }}
                            {{ $risk['severity'] === 'medium' ? 'bg-yellow-100 dark:bg-yellow-900' : '' }}
                            {{ $risk['severity'] === 'low' ? 'bg-blue-100 dark:bg-blue-900' : '' }}">
                            <svg class="w-5 h-5 
                                {{ $risk['severity'] === 'high' ? 'text-red-600 dark:text-red-400' : '' }}
                                {{ $risk['severity'] === 'medium' ? 'text-yellow-600 dark:text-yellow-400' : '' }}
                                {{ $risk['severity'] === 'low' ? 'text-blue-600 dark:text-blue-400' : '' }}" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $risk['title'] }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $risk['description'] }}</p>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Impact:</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100 ml-2">{{ $risk['impact'] }}</span>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Recommendation:</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100 ml-2">{{ $risk['recommendation'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Growth Opportunities -->
        @if(!empty($kpis['opportunities']))
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Growth Opportunities</h2>
                        <p class="text-gray-600 dark:text-gray-400">Identify areas for business growth and improvement</p>
                    </div>
                </div>
                <!-- AI Insight Button -->
                <button 
                    id="ai-insights-btn-growth-opportunities"
                    data-section="growth-opportunities"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Insights
                </button>
            </div>
            
            <!-- AI Insight Display Area -->
            <div id="ai-insight-growth-opportunities" class="hidden mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">AI-Generated Insights</h4>
                            <div id="ai-content-growth-opportunities" class="text-blue-800 dark:text-blue-200">
                                <div class="animate-pulse">
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($kpis['opportunities'] as $opportunity)
                <div class="p-6 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ $opportunity['title'] }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $opportunity['description'] }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Potential Impact:</span>
                            <span class="text-sm font-medium text-green-700 dark:text-green-300 ml-2">{{ $opportunity['potential_impact'] }}</span>
                        </div>
                        
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 block">Action Items:</span>
                            <ul class="space-y-1">
                                @foreach($opportunity['action_items'] as $action)
                                <li class="text-sm text-gray-700 dark:text-gray-300 flex items-start gap-2">
                                    <span class="text-green-500 mt-1">•</span>
                                    {{ $action }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Enhanced Customer Movement -->
        @if($comparisonSnapshotDate && !empty($kpis['customer_movement_details']['ranking_changes']))
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Customer Ranking Changes</h2>
                    <p class="text-gray-600 dark:text-gray-400">Track how customer positions have changed over time</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Improvers -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Top Ranking Improvers
                    </h3>
                    <div class="space-y-3">
                        @foreach(array_slice(array_filter($kpis['customer_movement_details']['ranking_changes'], fn($c) => (is_numeric($c['rank_change']) && $c['rank_change'] > 0) || $c['rank_change'] === 'New Active'), 0, 5) as $improver)
                        <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $improver['client_name'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($improver['rank_change'] === 'New Active')
                                        Became Active (RFM: {{ $improver['previous_rfm'] }} → {{ $improver['current_rfm'] }})
                                    @elseif($improver['previous_rank'] !== 'N/A' && $improver['current_rank'] !== 'N/A')
                                        Rank {{ $improver['previous_rank'] }} → {{ $improver['current_rank'] }}
                                        (RFM: {{ $improver['previous_rfm'] }} → {{ $improver['current_rfm'] }})
                                    @else
                                        Became Active (RFM: {{ $improver['previous_rfm'] }} → {{ $improver['current_rfm'] }})
                                    @endif
                                </p>
                            </div>
                            <span class="text-green-600 dark:text-green-400 font-bold text-lg">
                                @if($improver['rank_change'] === 'New Active')
                                    <span class="px-3 py-1 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300 font-bold text-sm rounded-full">New Active</span>
                                @elseif(is_numeric($improver['rank_change']))
                                    +{{ $improver['rank_change'] }}
                                @else
                                    {{ $improver['rank_change'] }}
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Decliners -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6 6"></path>
                        </svg>
                        Top Ranking Decliners
                    </h3>
                    <div class="space-y-3">
                        @foreach(array_slice(array_filter($kpis['customer_movement_details']['ranking_changes'], fn($c) => is_numeric($c['rank_change']) && $c['rank_change'] < 0), 0, 5) as $decliner)
                        <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $decliner['client_name'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($decliner['previous_rank'] !== 'N/A' && $decliner['current_rank'] !== 'N/A')
                                        Rank {{ $decliner['previous_rank'] }} → {{ $decliner['current_rank'] }}
                                    @else
                                        Became Inactive
                                    @endif
                                    (RFM: {{ $decliner['previous_rfm'] }} → {{ $decliner['current_rfm'] }})
                                </p>
                            </div>
                            <span class="text-red-600 dark:text-red-400 font-bold text-lg">{{ $decliner['rank_change'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Historical Trends -->
        @if(!empty($kpis['historical_trends']))
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Historical Trends</h2>
                        <p class="text-gray-600 dark:text-gray-400">Track performance over the last 6 periods</p>
                    </div>
                </div>
                <!-- AI Insight Button -->
                <button 
                    id="ai-insights-btn-historical-trends"
                    data-section="historical-trends"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Insights
                </button>
            </div>
            
            <!-- AI Insight Display Area -->
            <div id="ai-insight-historical-trends" class="hidden mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">AI-Generated Insights</h4>
                            <div id="ai-content-historical-trends" class="text-blue-800 dark:text-blue-200">
                                <div class="animate-pulse">
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
                                    <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold text-gray-900 dark:text-gray-100">Period</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">Total Customers</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">Avg RFM</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">High Value</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-gray-100">At Risk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($kpis['historical_trends'] as $trend)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                {{ $trend['formatted_date'] ?? \Carbon\Carbon::parse($trend['date'])->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-medium">
                                    {{ $trend['total_customers'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 font-medium">
                                    {{ $trend['average_rfm'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 font-medium">
                                    {{ $trend['high_value_customers'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 font-medium">
                                    {{ $trend['at_risk_customers'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Top 50 Movements -->
        @if($comparisonSnapshotDate && (!empty($kpis['customer_movement_details']['top_50_movements']['moved_in']) || !empty($kpis['customer_movement_details']['top_50_movements']['fell_out'])))
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Top 50 Customer Movements</h2>
                    <p class="text-gray-600 dark:text-gray-400">Track customers entering and leaving your top 50</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Moved into Top 50 -->
                @if(!empty($kpis['customer_movement_details']['top_50_movements']['moved_in']))
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Moved into Top 50
                    </h3>
                    <div class="space-y-3">
                        @foreach($kpis['customer_movement_details']['top_50_movements']['moved_in'] as $movedIn)
                        <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $movedIn['client_name'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Current RFM: {{ $movedIn['current_rfm'] }} | Rank: {{ $movedIn['current_rank'] }}
                                </p>
                            </div>
                            <span class="text-green-600 dark:text-green-400 font-bold">↑</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Fell out of Top 50 -->
                @if(!empty($kpis['customer_movement_details']['top_50_movements']['fell_out']))
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6 6"></path>
                        </svg>
                        Fell out of Top 50
                    </h3>
                    <div class="space-y-3">
                        @foreach($kpis['customer_movement_details']['top_50_movements']['fell_out'] as $fellOut)
                        <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $fellOut['client_name'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Previous RFM: {{ $fellOut['previous_rfm'] }} | Previous Rank: {{ $fellOut['previous_rank'] }}
                                </p>
                            </div>
                            <span class="text-red-600 dark:text-red-400 font-bold">↓</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Recently Lost Customers Alert -->
        @if(!empty($kpis['customer_movement_details']['recently_lost_customers']))
        <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Recently Lost Customers</h2>
                    <p class="text-gray-600 dark:text-gray-400">Customers who were active but became inactive</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($kpis['customer_movement_details']['recently_lost_customers'] as $customer)
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl p-4 border border-orange-200 dark:border-orange-800 hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm leading-tight">{{ $customer['client_name'] }}</h3>
                        </div>
                        <div class="ml-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-800 text-orange-800 dark:text-orange-200">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Lost
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-1 mb-1">
                            <span class="font-medium">Previous RFM:</span> {{ $customer['previous_rfm'] }}
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="font-medium">Inactive for:</span> {{ $customer['months_inactive'] }} month{{ $customer['months_inactive'] != 1 ? 's' : '' }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if(count($kpis['customer_movement_details']['recently_lost_customers']) > 0)
            <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="font-medium text-amber-900 dark:text-amber-100 mb-1">Re-engagement Opportunity</h4>
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            {{ count($kpis['customer_movement_details']['recently_lost_customers']) }} customers recently became inactive. 
                            These are prime candidates for re-engagement campaigns as they were previously active customers.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</x-app-layout>
