<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Analysis Dashboard</h2>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            

            <!-- Content Area -->
            <div class="space-y-6">
                <!-- Intro Card (full-width consistent) -->
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-800">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 dark:bg-indigo-900 rounded-full mb-3">
                            <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">RFM Analysis</h1>
                        <p class="text-gray-600 dark:text-gray-400">Explore trends, segmentation, and component breakdowns powered by your RFM scores.</p>
                    </div>
                </div>

                <!-- Navigation Tabs -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button onclick="showTab('overview')" id="tab-overview" 
                                class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-white dark:text-white">
                            Overview
                        </button>
                        <button onclick="showTab('client-trends')" id="tab-client-trends" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Client RFM Trends
                        </button>
                        <button onclick="showTab('rfm-breakdown')" id="tab-rfm-breakdown" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Overall RFM Breakdown
                        </button>
                        <button onclick="showTab('rfm-monthly-distribution')" id="tab-rfm-monthly-distribution" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            RFM Monthly Distribution
                        </button>
                        <button onclick="showTab('rfm-score-over-time')" id="tab-rfm-score-over-time" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            RFM Score Over Time
                        </button>
                        <button onclick="showTab('customer-retention')" id="tab-customer-retention" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Retention
                        </button>
                        <button onclick="showTab('customer-lifetime-value')" id="tab-customer-lifetime-value" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Lifetime Value
                        </button>
                        <button onclick="showTab('customer-segmentation')" id="tab-customer-segmentation" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Segmentation
                        </button>
                        <button onclick="showTab('customer-value-distribution')" id="tab-customer-value-distribution" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Value Distribution
                        </button>



                    </nav>
                </div>
            </div>


                @if(isset($hasInvoices) && !$hasInvoices)
                    <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="px-6 py-5 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Sync invoices to get started</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">You need invoice data before calculating RFM scores.</p>
                            <a href="{{ route('invoices.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-sm">Go to Invoices</a>
                        </div>
                    </div>
                @elseif(isset($hasRfm) && !$hasRfm)
                    <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="px-6 py-5 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Analysis requires RFM scores</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Calculate RFM scores to view analysis and charts.</p>
                            <a href="{{ route('rfm.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-sm">Go to RFM Scores</a>
                        </div>
                    </div>
                @endif
                @php
                    $hasData = isset($rfmData) && $rfmData->count() > 0;
                    
                    // Process RFM data for charts
                    if ($hasData) {
                        // Get all unique dates (1st of each month)
                        $allDates = $rfmData->pluck('date')
                            ->map(function($date) {
                                return \Carbon\Carbon::parse($date)->startOfMonth()->format('Y-m-01');
                            })
                            ->unique()
                            ->sort()
                            ->values();
                        
                        // Get all unique clients
                        $allClients = $rfmData->pluck('client_name')->unique()->values();
                        
                        // Group data by client and date
                        $clientData = [];
                        $palette = ['#3B82F6','#EF4444','#10B981','#F59E0B','#8B5CF6','#06B6D4','#84CC16','#F97316','#EC4899','#6366F1'];
                        
                        foreach ($allClients as $idx => $clientName) {
                            $clientRecords = $rfmData->where('client_name', $clientName);
                            $byDate = $clientRecords->groupBy(function($record) {
                                return \Carbon\Carbon::parse($record->date)->startOfMonth()->format('Y-m-01');
                            });
                            
                            $series = $allDates->map(function($date) use ($byDate) {
                                return isset($byDate[$date]) ? round($byDate[$date]->avg('rfm_score'), 2) : null;
                            })->toArray();
                            
                            $avgScore = collect($series)->filter(fn($v) => $v !== null)->avg();
                            
                            $clientData[] = [
                                'name' => $clientName,
                                'data' => $series,
                                'avg' => $avgScore ?? 0,
                                'color' => $palette[$idx % count($palette)]
                            ];
                        }
                        
                        // Sort by average score (top performers first)
                        $clientData = collect($clientData)->sortByDesc('avg')->values()->toArray();
                    }
                @endphp

                <!-- Overview Tab -->
                <div id="tab-content-overview" class="tab-content">
                    
                    
                    
                    <!-- Getting Started Guide -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                        @php
                            $monthsCount = isset($allDates) ? $allDates->count() : 0;
                            $clientCount = isset($allClients) ? $allClients->count() : 0;
                            $recTab = 'client-trends';
                            $recLabel = 'Client RFM Trends';
                            if ($clientCount < 8) { $recTab = 'rfm-breakdown'; $recLabel = 'Overall RFM Breakdown'; }
                            elseif ($monthsCount < 4) { $recTab = 'customer-segmentation'; $recLabel = 'Customer Segmentation'; }
                        @endphp
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center justify-between">
                            <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Getting Started Guide
                            <span class="text-xs text-gray-500 dark:text-gray-400">Follow the steps, or jump straight in</span>
                        </h3>
                        <div class="mb-4 rounded-md bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-blue-900 dark:text-blue-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Recommended first view: <strong>{{ $recLabel }}</strong></span>
                            </div>
                            <button onclick="showTab('{{ $recTab }}')" class="text-xs px-3 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Open</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3" id="gs-step-1">
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400 step-index">1</span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Client RFM Trends</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Explore individual client performance over time. Spot improving or declining customers.</p>
                                        <button data-step-key="client-trends" onclick="showTab('client-trends')" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Open trends ↗</button>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3" id="gs-step-2">
                                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-green-600 dark:text-green-400 step-index">2</span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Overall RFM Breakdown</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">See distribution across recency, frequency, and monetary to understand the base.</p>
                                        <button data-step-key="rfm-breakdown" onclick="showTab('rfm-breakdown')" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Open breakdown ↗</button>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3" id="gs-step-3">
                                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-purple-600 dark:text-purple-400 step-index">3</span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Customer Segmentation</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Group customers by behavior and value to target actions that matter.</p>
                                        <button data-step-key="customer-segmentation" onclick="showTab('customer-segmentation')" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Open segments ↗</button>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3" id="gs-step-4">
                                    <div class="flex-shrink-0 w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-orange-600 dark:text-orange-400 step-index">4</span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Advanced Analysis</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Dive into trends and comparisons over time to track movement and impact.</p>
                                        <button data-step-key="trends" onclick="showTab('trends')" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Open trends & comparisons ↗</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats Section -->
                    @if($hasData)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-4">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers (lifetime, connected org)</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ isset($lifetimeClientCount) ? $lifetimeClientCount : (isset($clientCount) ? $clientCount : $allClients->count()) }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg mr-4">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Data Period</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($allDates->first())->format('M Y') }} - {{ \Carbon\Carbon::parse($allDates->last())->format('M Y') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg mr-4">
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 21h14M7 13h10" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Months Covered</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $monthsCount }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg mr-4">
                                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Data Points</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $rfmData->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    

                    


                </div>
                <!-- Client RFM Trends Tab -->
                <div id="tab-content-client-trends" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Client RFM Trends</h3>
                            </div>
                            <div class="flex space-x-4">
                                <!-- Date Range Controls -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Start Date:</label>
                                    <input type="date" id="startDate" onchange="updateDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->first() : '' }}">
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">End Date:</label>
                                    <input type="date" id="endDate" onchange="updateDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->last() : '' }}">
                                </div>
                                
                                <!-- Quick Preset Buttons -->
                                <div class="flex items-center space-x-1">
                                    <button onclick="setDateRange('6m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        6M
                                    </button>
                                    <button onclick="setDateRange('12m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        12M
                                    </button>
                                    <button onclick="setDateRange('24m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        24M
                                    </button>
                                    <button onclick="setDateRange('all')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        All
                                    </button>
                                </div>
                                
                                <!-- Client Selection -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Show:</label>
                                                                         <select id="clientLimit" onchange="updateClientLimit()" class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                         <option value="5">Top 5</option>
                                         <option value="10" selected>Top 10</option>
                                         <option value="15">Top 15</option>
                                         <option value="20">Top 20</option>
                                         <option value="all">All clients</option>
                                         <option value="custom">Custom selection</option>
                                     </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chart-container" style="height: 400px; position: relative;">
                            <canvas id="clientTrendsChart"></canvas>
                        </div>
                        
                                                 <!-- Client Legend -->
                         <div class="mt-6">
                             <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Client Legend</h4>
                             <div id="clientLegend" class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                 <!-- Legend items will be populated by JavaScript -->
                             </div>
                         </div>
                         
                         <!-- Client Selection Panel -->
                         <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                             <div class="flex items-center justify-between mb-4">
                                 <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Client Selection</h4>
                                 <button onclick="toggleClientPanel()" id="toggleClientPanel" 
                                         class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                                     Show Advanced Selection
                                 </button>
                             </div>
                             
                             <!-- Advanced Client Selection (Hidden by default) -->
                             <div id="advancedClientPanel" class="hidden">
                                 <!-- Search and Filter Controls -->
                                 <div class="flex flex-wrap gap-4 mb-4">
                                     <!-- Search Box -->
                                     <div class="flex-1 min-w-64">
                                         <input type="text" id="clientSearch" placeholder="Search clients..." 
                                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                onkeyup="filterClientList()">
                                     </div>
                                     
                                     <!-- Quick Actions -->
                                     <div class="flex gap-2">
                                         <button onclick="selectAllClients()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                                             Select All
                                         </button>
                                         <button onclick="deselectAllClients()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm">
                                             Deselect All
                                         </button>
                                         <button onclick="selectTopClients()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                                             Top 10
                                         </button>
                                     </div>
                                 </div>
                                 
                                 <!-- Client List -->
                                 <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 max-h-64 overflow-y-auto">
                                     <div id="clientList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                         <!-- Client checkboxes will be populated by JavaScript -->
                                     </div>
                                 </div>
                                 
                                 <!-- Selected Count -->
                                 <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                     <span id="selectedCount">0</span> of <span id="totalCount">0</span> clients selected
                                 </div>
                             </div>
                         </div>
                    </div>
                </div>

                                 <!-- Overall RFM Breakdown Tab -->
                 <div id="tab-content-rfm-breakdown" class="tab-content" style="display: none;">
                     <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                                                   <div class="flex justify-between items-center mb-6">
                                                          <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Overall RFM Breakdown</h3>
                            </div>
                              <div class="flex space-x-4">
                                 <!-- Date Range Controls -->
                                 <div class="flex items-center space-x-2">
                                     <label class="text-sm text-gray-600 dark:text-gray-400">Start Date:</label>
                                     <input type="date" id="rfmStartDate" onchange="updateRfmDateRange()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                            min="{{ $hasData ? $allDates->first() : '' }}" 
                                            max="{{ $hasData ? $allDates->last() : '' }}"
                                            value="{{ $hasData ? $allDates->first() : '' }}">
                                 </div>
                                 
                                 <div class="flex items-center space-x-2">
                                     <label class="text-sm text-gray-600 dark:text-gray-400">End Date:</label>
                                     <input type="date" id="rfmEndDate" onchange="updateRfmDateRange()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                            min="{{ $hasData ? $allDates->first() : '' }}" 
                                            max="{{ $hasData ? $allDates->last() : '' }}"
                                            value="{{ $hasData ? $allDates->last() : '' }}">
                                 </div>
                                 
                                 <!-- Quick Preset Buttons -->
                                 <div class="flex items-center space-x-1">
                                     <button onclick="setRfmDateRange('6m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                         6M
                                     </button>
                                     <button onclick="setRfmDateRange('12m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                         12M
                                     </button>
                                     <button onclick="setRfmDateRange('24m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                         24M
                                     </button>
                                     <button onclick="setRfmDateRange('all')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                         All
                                     </button>
                                 </div>
                             </div>
                         </div>
                         <div class="chart-container" style="height: 400px; position: relative;">
                             <canvas id="rfmBreakdownChart"></canvas>
                         </div>
                         
                         <!-- Top Companies by RFM Component Table -->
                         <div class="mt-8">
                             <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Top Companies by RFM Component</h4>
                             <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                 <!-- Most Recent Companies (R Score) -->
                                 <div class="space-y-4">
                                     <div class="flex items-center space-x-2">
                                         <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                                         <h5 class="text-md font-semibold text-gray-900 dark:text-gray-100">Most Recent (R Score)</h5>
                                     </div>
                                     <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                         <div id="topRecentCompaniesMain" class="space-y-3">
                                             <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                                 <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-500 mx-auto mb-2"></div>
                                                 Loading...
                                             </div>
                                         </div>
                     </div>
                 </div>

                                 <!-- Most Frequent Companies (F Score) -->
                                 <div class="space-y-4">
                                     <div class="flex items-center space-x-2">
                                         <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                                         <h5 class="text-md font-semibold text-gray-900 dark:text-gray-100">Most Frequent (F Score)</h5>
                                     </div>
                                     <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                         <div id="topFrequentCompaniesMain" class="space-y-3">
                                             <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                                 <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500 mx-auto mb-2"></div>
                                                 Loading...
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 <!-- Highest Monetary Companies (M Score) -->
                                 <div class="space-y-4">
                                     <div class="flex items-center space-x-2">
                                         <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                                         <h5 class="text-md font-semibold text-gray-900 dark:text-gray-100">Highest Value (M Score)</h5>
                                     </div>
                                     <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                         <div id="topMonetaryCompaniesMain" class="space-y-3">
                                             <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                                 <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-yellow-500 mx-auto mb-2"></div>
                                                 Loading...
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>



                <!-- RFM Monthly Distribution Tab -->
                <div id="tab-content-rfm-monthly-distribution" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score Distribution by Month</h3>
                            </div>
                            <div class="flex space-x-4">
                                <!-- Date Range Controls -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Start Date:</label>
                                    <input type="date" id="monthlyDistStartDate" onchange="updateMonthlyDistDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->first() : '' }}">
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">End Date:</label>
                                    <input type="date" id="monthlyDistEndDate" onchange="updateMonthlyDistDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->last() : '' }}">
                                </div>
                                
                                <!-- Quick Preset Buttons -->
                                <div class="flex items-center space-x-1">
                                    <button onclick="setMonthlyDistDateRange('6m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        6M
                                    </button>
                                    <button onclick="setMonthlyDistDateRange('12m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        12M
                                    </button>
                                    <button onclick="setMonthlyDistDateRange('24m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        24M
                                    </button>
                                    <button onclick="setMonthlyDistDateRange('all')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        All
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Stats Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Distribution Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="rfmMonthlyDistChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Monthly Statistics -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-indigo-800 dark:text-indigo-200 mb-2">Peak Performance Month</h4>
                                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="peakMonth">-</div>
                                    <p class="text-sm text-indigo-700 dark:text-indigo-300">Highest avg RFM score</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-emerald-800 dark:text-emerald-200 mb-2">Most Active Month</h4>
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="mostActiveMonth">-</div>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">Most customer records</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/20 dark:to-rose-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-rose-800 dark:text-rose-200 mb-2">Seasonal Trend</h4>
                                    <div class="text-2xl font-bold text-rose-600 dark:text-rose-400" id="seasonalTrend">-</div>
                                    <p class="text-sm text-rose-700 dark:text-rose-300">Performance pattern</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/20 dark:to-violet-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-violet-800 dark:text-violet-200 mb-2">Consistency Score</h4>
                                    <div class="text-2xl font-bold text-violet-600 dark:text-violet-400" id="consistencyScore">-</div>
                                    <p class="text-sm text-violet-700 dark:text-violet-300">Score stability</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Monthly Performance Insights -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Monthly Performance Insights</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Best Month:</span>
                                        <span class="font-semibold text-indigo-600 dark:text-indigo-400" id="bestMonth">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Worst Month:</span>
                                        <span class="font-semibold text-rose-600 dark:text-rose-400" id="worstMonth">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Avg Monthly Score:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" id="avgMonthlyScore">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Score Range:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="scoreRange">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Growth Rate:</span>
                                        <span class="font-semibold text-violet-600 dark:text-violet-400" id="monthlyGrowthRate">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Volatility:</span>
                                        <span class="font-semibold text-yellow-600 dark:text-yellow-400" id="volatility">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Monthly Breakdown Table -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Monthly RFM Score Breakdown</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Month</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg RFM Score</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customers</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">High Value %</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthlyBreakdownTable" class="divide-y divide-gray-200 dark:divide-gray-600">
                                        <!-- Table content will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                                         </div>
                 </div>
                <!-- RFM Score Over Time Tab -->
                <div id="tab-content-rfm-score-over-time" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score Distribution Over Time</h3>
                            </div>
                            <div class="flex space-x-4">
                                <!-- Time Period Controls -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Time Period:</label>
                                    <select id="timePeriodSelect" onchange="updateTimePeriodHistogram()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                                
                                <!-- Score Range Filter -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Score Range:</label>
                                    <select id="scoreRangeSelect" onchange="updateTimePeriodHistogram()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="all">All Scores</option>
                                        <option value="high">High (8-10)</option>
                                        <option value="medium">Medium (5-7)</option>
                                        <option value="low">Low (1-4)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Stats Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Time Period Histogram Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="rfmScoreOverTimeChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Time Period Statistics -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-2">Peak Period</h4>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="peakPeriod">-</div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">Highest avg score</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-green-800 dark:text-green-200 mb-2">Growth Trend</h4>
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="growthTrend">-</div>
                                    <p class="text-sm text-green-700 dark:text-green-300">Score improvement</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-orange-800 dark:text-orange-200 mb-2">Volatility</h4>
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400" id="scoreVolatility">-</div>
                                    <p class="text-sm text-orange-700 dark:text-orange-300">Score stability</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-purple-800 dark:text-purple-200 mb-2">Seasonal Pattern</h4>
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" id="seasonalPattern">-</div>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">Time-based trends</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Time Period Analysis -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Time Period Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Best Period:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="bestPeriod">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Worst Period:</span>
                                        <span class="font-semibold text-red-600 dark:text-red-400" id="worstPeriod">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Avg Score:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400" id="avgScoreOverTime">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Score Range:</span>
                                        <span class="font-semibold text-yellow-600 dark:text-yellow-400" id="scoreRangeOverTime">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Trend Direction:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" id="trendDirection">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Consistency:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="scoreConsistency">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Periods Analyzed:</span>
                                        <span class="font-semibold text-indigo-600 dark:text-indigo-400" id="periodsAnalyzed">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Records:</span>
                                        <span class="font-semibold text-rose-600 dark:text-rose-400" id="totalRecordsOverTime">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Period Breakdown -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Period Performance Breakdown</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg Score</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">High Score %</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Medium Score %</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Low Score %</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody id="periodBreakdownTable" class="divide-y divide-gray-200 dark:divide-gray-600">
                                        <!-- Table content will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Time-Based Insights -->
                        <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-blue-900 dark:text-blue-100 mb-3">Time-Based Insights</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-start space-x-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="timeInsight1">Analyzing RFM score trends over time...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="timeInsight2">Identifying seasonal patterns and trends...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="timeInsight3">Calculating performance consistency metrics...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Retention Tab -->
                <div id="tab-content-customer-retention" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Retention Analysis</h3>
                            <div class="flex space-x-4">
                                <!-- Retention Period Controls -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Retention Period:</label>
                                    <select id="retentionPeriodSelect" onchange="updateRetentionAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="3m">3 Months</option>
                                        <option value="6m" selected>6 Months</option>
                                        <option value="12m">12 Months</option>
                                    </select>
                                </div>
                                
                                <!-- Analysis Type -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Analysis:</label>
                                    <select id="retentionAnalysisType" onchange="updateRetentionAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="score-based">By RFM Score</option>
                                        <option value="time-based">By Time Period</option>
                                        <option value="segment-based">By Customer Segment</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Stats Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Retention Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="customerRetentionChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Retention Statistics -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-emerald-800 dark:text-emerald-200 mb-2">Overall Retention</h4>
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="overallRetention">-</div>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">Average retention rate</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-2">High-Value Retention</h4>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="highValueRetention">-</div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">RFM Score 8-10</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-orange-800 dark:text-orange-200 mb-2">At-Risk Customers</h4>
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400" id="atRiskCustomers">-</div>
                                    <p class="text-sm text-orange-700 dark:text-orange-300">Low retention risk</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-red-800 dark:text-red-200 mb-2">Churn Rate</h4>
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400" id="churnRate">-</div>
                                    <p class="text-sm text-red-700 dark:text-red-300">Customers lost</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Retention Analysis -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Retention Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Best Retaining Segment:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="bestRetainingSegment">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Worst Retaining Segment:</span>
                                        <span class="font-semibold text-red-600 dark:text-red-400" id="worstRetainingSegment">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Retention Trend:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="retentionTrend">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Customers:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" id="totalCustomersRetention">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Retained Customers:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400" id="retainedCustomers">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Lost Customers:</span>
                                        <span class="font-semibold text-red-600 dark:text-red-400" id="lostCustomers">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">New Customers:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="newCustomers">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Retention Score:</span>
                                        <span class="font-semibold text-purple-600 dark:text-purple-400" id="retentionScore">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Retention Breakdown -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Retention Breakdown by Segment</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer Segment</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Retention Rate</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customers</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Retained</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lost</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Risk Level</th>
                                        </tr>
                                    </thead>
                                    <tbody id="retentionBreakdownTable" class="divide-y divide-gray-200 dark:divide-gray-600">
                                        <!-- Table content will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Retention Insights -->
                        <div class="mt-6 bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-emerald-900 dark:text-emerald-100 mb-3">Retention Insights</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-start space-x-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="retentionInsight1">Analyzing customer retention patterns...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="retentionInsight2">Identifying high-risk customer segments...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="retentionInsight3">Calculating retention improvement opportunities...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Lifetime Value Tab -->
                <div id="tab-content-customer-lifetime-value" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Lifetime Value Analysis</h3>
                            </div>
                            <div class="flex space-x-4">
                                <!-- CLV Calculation Method -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">CLV Method:</label>
                                    <select id="clvMethodSelect" onchange="updateClvAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="rfm-based">RFM-Based</option>
                                        <option value="frequency-based">Frequency-Based</option>
                                        <option value="monetary-based">Monetary-Based</option>
                                    </select>
                                </div>
                                
                                <!-- Time Horizon -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Time Horizon:</label>
                                    <select id="clvTimeHorizon" onchange="updateClvAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="1y">1 Year</option>
                                        <option value="2y" selected>2 Years</option>
                                        <option value="3y">3 Years</option>
                                        <option value="5y">5 Years</option>
                                    </select>
                                </div>
                                
                                <!-- Discount Rate -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Discount Rate:</label>
                                    <select id="clvDiscountRate" onchange="updateClvAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="0.05">5%</option>
                                        <option value="0.10" selected>10%</option>
                                        <option value="0.15">15%</option>
                                        <option value="0.20">20%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Stats Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main CLV Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="customerLifetimeValueChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- CLV Statistics -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-purple-800 dark:text-purple-200 mb-2">Total CLV</h4>
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" id="totalClv">-</div>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">Predicted revenue</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-emerald-800 dark:text-emerald-200 mb-2">Average CLV</h4>
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="averageClv">-</div>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">Per customer</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-2">High-Value Customers</h4>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="highValueCustomers">-</div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">CLV > £10,000</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-orange-800 dark:text-orange-200 mb-2">ROI Potential</h4>
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400" id="roiPotential">-</div>
                                    <p class="text-sm text-orange-700 dark:text-orange-300">Investment return</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CLV Analysis -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">CLV Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Highest CLV Customer:</span>
                                        <span class="font-semibold text-purple-600 dark:text-purple-400" id="highestClvCustomer">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">CLV Range:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="clvRange">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Growth Rate:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="clvGrowthRate">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Customers:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" id="totalCustomersClv">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Premium Customers:</span>
                                        <span class="font-semibold text-purple-600 dark:text-purple-400" id="premiumCustomers">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Standard Customers:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="standardCustomers">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Low-Value Customers:</span>
                                        <span class="font-semibold text-orange-600 dark:text-orange-400" id="lowValueCustomers">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">CLV Score:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="clvScore">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CLV Breakdown -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">CLV Breakdown by Segment</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer Segment</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Average CLV</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customers</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Value</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">% of Total</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody id="clvBreakdownTable" class="divide-y divide-gray-200 dark:divide-gray-600">
                                        <!-- Table content will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- CLV Insights -->
                        <div class="mt-6 bg-gradient-to-r from-purple-50 to-emerald-50 dark:from-purple-900/20 dark:to-emerald-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-purple-900 dark:text-purple-100 mb-3">CLV Insights</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-start space-x-2">
                                    <span class="text-purple-600 dark:text-purple-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="clvInsight1">Analyzing customer lifetime value patterns...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-purple-600 dark:text-purple-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="clvInsight2">Identifying high-value customer opportunities...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-purple-600 dark:text-purple-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="clvInsight3">Calculating investment prioritization strategies...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Customer Segmentation Tab -->
                <div id="tab-content-customer-segmentation" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Segmentation & Behavior Analysis</h3>
                            </div>
                            <div class="flex space-x-4">
                                <!-- Segmentation Method -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Method:</label>
                                    <select id="segmentationMethodSelect" onchange="updateSegmentationAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="rfm-score">RFM Score</option>
                                        <option value="behavior-pattern">Behavior Pattern</option>
                                        <option value="value-tier">Value Tier</option>
                                        <option value="engagement-level">Engagement Level</option>
                                    </select>
                                </div>
                                
                                <!-- Time Period -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Period:</label>
                                    <select id="segmentationTimePeriod" onchange="updateSegmentationAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="3m">3 Months</option>
                                        <option value="6m" selected>6 Months</option>
                                        <option value="12m">12 Months</option>
                                    </select>
                                </div>
                                
                                <!-- Chart Type -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Chart:</label>
                                    <select id="segmentationChartType" onchange="updateSegmentationAnalysis()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="pie">Pie Chart</option>
                                        <option value="bar">Bar Chart</option>
                                        <option value="doughnut" selected>Doughnut Chart</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Stats Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Segmentation Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="customerSegmentationChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Segmentation Statistics -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-amber-800 dark:text-amber-200 mb-2">Total Segments</h4>
                                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400" id="totalSegments">-</div>
                                    <p class="text-sm text-amber-700 dark:text-amber-300">Active segments</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-teal-800 dark:text-teal-200 mb-2">Largest Segment</h4>
                                    <div class="text-2xl font-bold text-teal-600 dark:text-teal-400" id="largestSegment">-</div>
                                    <p class="text-sm text-teal-700 dark:text-teal-300">By customer count</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/20 dark:to-rose-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-rose-800 dark:text-rose-200 mb-2">Highest Value</h4>
                                    <div class="text-2xl font-bold text-rose-600 dark:text-rose-400" id="highestValueSegment">-</div>
                                    <p class="text-sm text-rose-700 dark:text-rose-300">By average value</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/20 dark:to-violet-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-violet-800 dark:text-violet-200 mb-2">Segmentation Score</h4>
                                    <div class="text-2xl font-bold text-violet-600 dark:text-violet-400" id="segmentationScore">-</div>
                                    <p class="text-sm text-violet-700 dark:text-violet-300">Quality metric</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segmentation Analysis -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Segmentation Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Most Active Segment:</span>
                                        <span class="font-semibold text-amber-600 dark:text-amber-400" id="mostActiveSegment">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Average RFM Score:</span>
                                        <span class="font-semibold text-teal-600 dark:text-teal-400" id="avgRfmScoreSegmentation">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Segment Diversity:</span>
                                        <span class="font-semibold text-rose-600 dark:text-rose-400" id="segmentDiversity">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Customers:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" id="totalCustomersSegmentation">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Premium Customers:</span>
                                        <span class="font-semibold text-violet-600 dark:text-violet-400" id="premiumCustomersSegmentation">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">At-Risk Customers:</span>
                                        <span class="font-semibold text-orange-600 dark:text-orange-400" id="atRiskCustomersSegmentation">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">New Customers:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="newCustomersSegmentation">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Loyal Customers:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400" id="loyalCustomersSegmentation">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segment Breakdown -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Segment Breakdown</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Segment</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customers</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">% of Total</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg RFM Score</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg Value</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="segmentBreakdownTable" class="divide-y divide-gray-200 dark:divide-gray-600">
                                        <!-- Table content will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Segmentation Insights -->
                        <div class="mt-6 bg-gradient-to-r from-amber-50 to-violet-50 dark:from-amber-900/20 dark:to-violet-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-amber-900 dark:text-amber-100 mb-3">Segmentation Insights</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-start space-x-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="segmentationInsight1">Analyzing customer segmentation patterns...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="segmentationInsight2">Identifying key customer segments and behaviors...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="segmentationInsight3">Calculating segment-specific strategies and recommendations...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Value Distribution Tab -->
                <div id="tab-content-customer-value-distribution" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Value Distribution Analysis</h3>
                            </div>
                            <div class="flex space-x-4">
                                <!-- Value Distribution Method -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Method:</label>
                                    <select id="valueDistributionMethod" onchange="updateValueDistribution()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="rfm-score">RFM Score</option>
                                        <option value="monetary-value">Monetary Value</option>
                                        <option value="frequency">Purchase Frequency</option>
                                        <option value="recency">Recency</option>
                                    </select>
                                </div>
                                
                                <!-- Distribution Type -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Type:</label>
                                    <select id="valueDistributionType" onchange="updateValueDistribution()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="histogram">Histogram</option>
                                        <option value="boxplot">Box Plot</option>
                                        <option value="percentile">Percentile</option>
                                    </select>
                                </div>
                                
                                <!-- Time Period -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Period:</label>
                                    <select id="valueDistributionPeriod" onchange="updateValueDistribution()" 
                                            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="3m">3 Months</option>
                                        <option value="6m" selected>6 Months</option>
                                        <option value="12m">12 Months</option>
                                        <option value="all">All Time</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Stats Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Value Distribution Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="customerValueDistributionChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Value Distribution Statistics -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-emerald-800 dark:text-emerald-200 mb-2">Mean Value</h4>
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="meanValue">-</div>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">Average customer value</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-2">Median Value</h4>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="medianValue">-</div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">Middle value</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-purple-800 dark:text-purple-200 mb-2">Top 20%</h4>
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" id="top20Percent">-</div>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">High-value customers</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-orange-800 dark:text-orange-200 mb-2">Value Range</h4>
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400" id="valueRange">-</div>
                                    <p class="text-sm text-orange-700 dark:text-orange-300">Min to max</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Value Distribution Analysis -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Value Distribution Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Distribution Shape:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="distributionShape">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Skewness:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="distributionSkewness">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Standard Deviation:</span>
                                        <span class="font-semibold text-purple-600 dark:text-purple-400" id="standardDeviation">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Outliers:</span>
                                        <span class="font-semibold text-orange-600 dark:text-orange-400" id="outlierCount">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Gini Coefficient:</span>
                                        <span class="font-semibold text-red-600 dark:text-red-400" id="giniCoefficient">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Value Concentration:</span>
                                        <span class="font-semibold text-indigo-600 dark:text-indigo-400" id="valueConcentration">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions Panel -->
                        <div class="mt-6 bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Quick Actions
                            </h4>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <button onclick="exportChartData()" class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg border border-blue-200 dark:border-blue-700 hover:shadow-md transition-all duration-200">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Export Data</span>
                                </button>
                                
                                <button onclick="resetChartView()" class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg border border-green-200 dark:border-green-700 hover:shadow-md transition-all duration-200">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-green-700 dark:text-green-300">Reset View</span>
                                </button>
                                
                                <button onclick="toggleFullscreen()" class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg border border-purple-200 dark:border-purple-700 hover:shadow-md transition-all duration-200">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">Fullscreen</span>
                                </button>
                                
                                <button onclick="shareAnalysis()" class="flex flex-col items-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg border border-orange-200 dark:border-orange-700 hover:shadow-md transition-all duration-200">
                                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-orange-700 dark:text-orange-300">Share</span>
                                </button>
                            </div>
                            
                            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Tip:</strong> Use these quick actions to enhance your analysis workflow. Export data for external analysis, reset to default view, or share insights with your team.
                                </p>
                            </div>
                        </div>


                        <!-- Value Distribution Insights -->
                        <div class="mt-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-700">
                            <h4 class="text-md font-semibold text-amber-800 dark:text-amber-200 mb-3">Key Insights</h4>
                            <div class="space-y-2">
                                <div class="flex items-start space-x-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="valueInsight1">Analyzing customer value distribution patterns...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="valueInsight2">Identifying value concentration and inequality measures...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">•</span>
                                    <span class="text-white dark:text-white" id="valueInsight3">Calculating statistical measures and outlier detection...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global variables for chart data
        const allClientData = @json($hasData ? $clientData : []);
        const allDates = @json($hasData ? $allDates : []);
         let currentStartDate = allDates.length > 0 ? allDates[0] : '';
         let currentEndDate = allDates.length > 0 ? allDates[allDates.length - 1] : '';
         let currentClientLimit = 10;
         let clientTrendsChart = null;
         let selectedClients = new Set(); // Track selected clients
         
         // RFM Breakdown chart variables
         let rfmBreakdownChart = null;
         let rfmStartDate = allDates.length > 0 ? allDates[0] : '';
         let rfmEndDate = allDates.length > 0 ? allDates[allDates.length - 1] : '';
         

         
         // RFM Monthly Distribution chart variables
         let rfmMonthlyDistChart = null;
         let monthlyDistStartDate = allDates.length > 0 ? allDates[0] : '';
         let monthlyDistEndDate = allDates.length > 0 ? allDates[allDates.length - 1] : '';
         

         
         // RFM Score Over Time chart variables
         let rfmScoreOverTimeChart = null;
         let timePeriodType = 'monthly';
         let scoreRangeFilter = 'all';
         
         // Customer Retention chart variables
         let customerRetentionChart = null;
         let retentionPeriod = '6m';
         let retentionAnalysisType = 'score-based';
         
         // Customer Lifetime Value chart variables
         let customerLifetimeValueChart = null;
         let clvMethod = 'rfm-based';
         let clvTimeHorizon = '2y';
         let clvDiscountRate = 0.10;
         
         // Customer Segmentation chart variables
         let customerSegmentationChart = null;
         let segmentationMethod = 'rfm-score';
         let segmentationTimePeriod = '6m';
         let segmentationChartType = 'doughnut';
         
         // Customer Value Distribution chart variables
         let customerValueDistributionChart = null;
         let valueDistributionMethod = 'rfm-score';
         let valueDistributionType = 'histogram';
         let valueDistributionPeriod = '6m';
         

         

         

         


        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400', 'text-white', 'dark:text-white');
                button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            
            // Show selected tab content
            document.getElementById('tab-content-' + tabName).style.display = 'block';
            
            // Add active class to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            if (activeTab) {
                activeTab.classList.add('active', 'border-blue-500', 'text-white', 'dark:text-white');
                activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'text-blue-600', 'dark:text-blue-400');
            }
            
            // Initialize chart for the selected tab
            initializeChart(tabName);
        }



        // Date range control
        function updateDateRange() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            // Validate date range
            if (startDate && endDate && startDate > endDate) {
                alert('Start date cannot be after end date');
                return;
            }
            
            currentStartDate = startDate;
            currentEndDate = endDate;
            
            if (clientTrendsChart) {
                updateClientTrendsChart();
            }
        }

        // Set date range from preset buttons
        function setDateRange(preset) {
            const startInput = document.getElementById('startDate');
            const endInput = document.getElementById('endDate');
            
            if (allDates.length === 0) return;
            
            const lastDate = new Date(allDates[allDates.length - 1]);
            let startDate = new Date(lastDate);
            
            switch(preset) {
                case '6m':
                    startDate.setMonth(startDate.getMonth() - 6);
                    break;
                case '12m':
                    startDate.setMonth(startDate.getMonth() - 12);
                    break;
                case '24m':
                    startDate.setMonth(startDate.getMonth() - 24);
                    break;
                case 'all':
                    startDate = new Date(allDates[0]);
                    break;
            }
            
            // Ensure start date is not before the earliest available date
            const earliestDate = new Date(allDates[0]);
            if (startDate < earliestDate) {
                startDate = earliestDate;
            }
            
            startInput.value = startDate.toISOString().split('T')[0];
            endInput.value = lastDate.toISOString().split('T')[0];
            
            currentStartDate = startInput.value;
            currentEndDate = endInput.value;
            
            if (clientTrendsChart) {
                updateClientTrendsChart();
            }
        }

                 // Client limit control
         function updateClientLimit() {
             currentClientLimit = document.getElementById('clientLimit').value;
             
             // If using limit-based selection, clear custom selections
             if (currentClientLimit !== 'custom') {
                 selectedClients.clear();
                 updateCheckboxes();
                 updateSelectedCount();
             }
             
             if (clientTrendsChart) {
                 updateClientTrendsChart();
             }
         }

                 // Update client trends chart with new filters
         function updateClientTrendsChart() {
             const filteredDates = filterDatesByRange(allDates, currentStartDate, currentEndDate);
             const filteredClients = filterClientsBySelection(allClientData);
            
            const datasets = filteredClients.map(client => ({
                label: client.name,
                data: client.data.slice(0, filteredDates.length),
                borderColor: client.color,
                backgroundColor: client.color + '20',
                tension: 0.1
            }));

            const labels = filteredDates.map(date => {
                return new Date(date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });

            if (clientTrendsChart) {
                clientTrendsChart.destroy();
            }

            const ctx = document.getElementById('clientTrendsChart');
            clientTrendsChart = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            title: {
                                display: true,
                                text: 'RFM Score',
                                color: '#FFFFFF'
                            },
                            ticks: {
                                color: '#FFFFFF'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date',
                                color: '#FFFFFF'
                            },
                            ticks: {
                                color: '#FFFFFF'
                            }
                        }
                    }
                }
            });

            updateClientLegend(filteredClients);
        }

        // Filter dates based on custom date range
        function filterDatesByRange(dates, startDate, endDate) {
            if (!startDate || !endDate) return dates;
            
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            return dates.filter(date => {
                const dateObj = new Date(date);
                return dateObj >= start && dateObj <= end;
            });
        }

                 // Filter clients based on limit
         function filterClientsByLimit(clients, limit) {
             if (limit === 'all') return clients;
             return clients.slice(0, parseInt(limit));
         }
         
         // Filter clients based on selection
         function filterClientsBySelection(clients) {
             if (selectedClients.size === 0) {
                 // If no clients selected, use the limit-based filtering
                 return filterClientsByLimit(clients, currentClientLimit);
             }
             return clients.filter(client => selectedClients.has(client.name));
         }

                 // Update client legend
         function updateClientLegend(clients) {
             const legend = document.getElementById('clientLegend');
             legend.innerHTML = '';
             
             clients.forEach(client => {
                 const item = document.createElement('div');
                 item.className = 'flex items-center space-x-2';
                 item.innerHTML = `
                     <div class="w-3 h-3 rounded-full" style="background-color: ${client.color}"></div>
                     <span class="text-sm text-white dark:text-white">${client.name}</span>
                 `;
                 legend.appendChild(item);
             });
         }
         
         // Initialize client selection list
         function initializeClientList() {
             const clientList = document.getElementById('clientList');
             const totalCount = document.getElementById('totalCount');
             
             if (!clientList || allClientData.length === 0) return;
             
             totalCount.textContent = allClientData.length;
             clientList.innerHTML = '';
             
             allClientData.forEach(client => {
                 const item = document.createElement('div');
                 item.className = 'flex items-center space-x-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded';
                 item.innerHTML = `
                     <input type="checkbox" id="client-${client.name.replace(/\s+/g, '-')}" 
                            class="client-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            data-client="${client.name}"
                            onchange="toggleClientSelection('${client.name}')">
                     <label for="client-${client.name.replace(/\s+/g, '-')}" 
                            class="text-sm text-white dark:text-white cursor-pointer flex-1">
                         ${client.name}
                     </label>
                     <div class="w-3 h-3 rounded-full" style="background-color: ${client.color}"></div>
                 `;
                 clientList.appendChild(item);
             });
             
             // Only initialize with top 10 selected if the panel is visible
             const panel = document.getElementById('advancedClientPanel');
             if (!panel.classList.contains('hidden')) {
                 selectTopClients();
             }
         }
         
         // Toggle client selection
         function toggleClientSelection(clientName) {
             if (selectedClients.has(clientName)) {
                 selectedClients.delete(clientName);
             } else {
                 selectedClients.add(clientName);
             }
             updateSelectedCount();
             updateClientTrendsChart();
         }
         
         // Select all clients
         function selectAllClients() {
             selectedClients.clear();
             allClientData.forEach(client => {
                 selectedClients.add(client.name);
             });
             updateCheckboxes();
             updateSelectedCount();
             updateClientTrendsChart();
         }
         
         // Deselect all clients
         function deselectAllClients() {
             selectedClients.clear();
             updateCheckboxes();
             updateSelectedCount();
             updateClientTrendsChart();
         }
         
         // Select top clients
         function selectTopClients() {
             selectedClients.clear();
             const topClients = allClientData.slice(0, 10);
             topClients.forEach(client => {
                 selectedClients.add(client.name);
             });
             updateCheckboxes();
             updateSelectedCount();
             updateClientTrendsChart();
         }
         
         // Filter client list based on search
         function filterClientList() {
             const searchTerm = document.getElementById('clientSearch').value.toLowerCase();
             const checkboxes = document.querySelectorAll('.client-checkbox');
             
             checkboxes.forEach(checkbox => {
                 const clientName = checkbox.dataset.client.toLowerCase();
                 const item = checkbox.closest('div');
                 
                 if (clientName.includes(searchTerm)) {
                     item.style.display = 'flex';
                 } else {
                     item.style.display = 'none';
                 }
             });
         }
         
         // Update checkboxes to reflect current selection
         function updateCheckboxes() {
             const checkboxes = document.querySelectorAll('.client-checkbox');
             checkboxes.forEach(checkbox => {
                 const clientName = checkbox.dataset.client;
                 checkbox.checked = selectedClients.has(clientName);
             });
         }
         
         // Update selected count display
         function updateSelectedCount() {
             const selectedCount = document.getElementById('selectedCount');
             if (selectedCount) {
                 selectedCount.textContent = selectedClients.size;
             }
         }
         
         // Toggle client selection panel
         function toggleClientPanel() {
             const panel = document.getElementById('advancedClientPanel');
             const toggleBtn = document.getElementById('toggleClientPanel');
             const clientLimit = document.getElementById('clientLimit');
             
             if (panel.classList.contains('hidden')) {
                 // Show panel
                 panel.classList.remove('hidden');
                 toggleBtn.textContent = 'Hide Advanced Selection';
                 toggleBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                 toggleBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                 
                 // Automatically set dropdown to custom
                 clientLimit.value = 'custom';
                 currentClientLimit = 'custom';
                 
                 // Initialize client list if not already done
                 if (document.getElementById('clientList').children.length === 0) {
                     initializeClientList();
                 }
             } else {
                 // Hide panel
                 panel.classList.add('hidden');
                 toggleBtn.textContent = 'Show Advanced Selection';
                 toggleBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                 toggleBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
                 
                 // Reset to top 10 if no custom selections
                 if (selectedClients.size === 0) {
                     clientLimit.value = '10';
                     currentClientLimit = '10';
                     updateClientTrendsChart();
                 }
             }
         }
         
         // RFM Breakdown date range control
         function updateRfmDateRange() {
             const startDate = document.getElementById('rfmStartDate').value;
             const endDate = document.getElementById('rfmEndDate').value;
             
             // Validate date range
             if (startDate && endDate && startDate > endDate) {
                 alert('Start date cannot be after end date');
                 return;
             }
             
             rfmStartDate = startDate;
             rfmEndDate = endDate;
             
             if (rfmBreakdownChart) {
                 updateRfmBreakdownChart();
             }
         }
         

         
         // Set RFM date range from preset buttons
         function setRfmDateRange(preset) {
             const startInput = document.getElementById('rfmStartDate');
             const endInput = document.getElementById('rfmEndDate');
             
             if (allDates.length === 0) return;
             
             const lastDate = new Date(allDates[allDates.length - 1]);
             let startDate = new Date(lastDate);
             
             switch(preset) {
                 case '6m':
                     startDate.setMonth(startDate.getMonth() - 6);
                     break;
                 case '12m':
                     startDate.setMonth(startDate.getMonth() - 12);
                     break;
                 case '24m':
                     startDate.setMonth(startDate.getMonth() - 24);
                     break;
                 case 'all':
                     startDate = new Date(allDates[0]);
                     break;
             }
             
             // Ensure start date is not before the earliest available date
             const earliestDate = new Date(allDates[0]);
             if (startDate < earliestDate) {
                 startDate = earliestDate;
             }
             
             startInput.value = startDate.toISOString().split('T')[0];
             endInput.value = lastDate.toISOString().split('T')[0];
             
             rfmStartDate = startInput.value;
             rfmEndDate = endInput.value;
             
             if (rfmBreakdownChart) {
                 updateRfmBreakdownChart();
             }
         }
        // Chart initialization
        function initializeChart(tabName) {
            switch(tabName) {
                case 'client-trends':
                    initializeClientTrendsChart();
                    break;
                case 'rfm-breakdown':
                    initializeRFMBreakdownChart();
                    break;

                case 'rfm-monthly-distribution':
                    initializeRfmMonthlyDistributionChart();
                    break;
                case 'rfm-score-over-time':
                    initializeRfmScoreOverTimeChart();
                    break;
                case 'customer-retention':
                    initializeCustomerRetentionChart();
                    break;
                case 'customer-lifetime-value':
                    initializeCustomerLifetimeValueChart();
                    break;
                case 'customer-segmentation':
                    initializeCustomerSegmentationChart();
                    break;
                case 'customer-value-distribution':
                    initializeCustomerValueDistributionChart();
                    break;



            }
        }

                 // Initialize Client RFM Trends Chart
         function initializeClientTrendsChart() {
             if (allClientData.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('clientTrendsChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }

             // Initialize client selection list
             initializeClientList();
             updateClientTrendsChart();
         }

                 // Initialize Overall RFM Breakdown Chart
         function initializeRFMBreakdownChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('rfmBreakdownChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateRfmBreakdownChart();
         }
         

         
         // Initialize RFM Monthly Distribution Chart
         function initializeRfmMonthlyDistributionChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('rfmMonthlyDistChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateRfmMonthlyDistributionChart();
         }
         

         
         // Initialize RFM Score Over Time Chart
         function initializeRfmScoreOverTimeChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('rfmScoreOverTimeChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateRfmScoreOverTimeChart();
         }
         
         // Initialize Customer Retention Chart
         function initializeCustomerRetentionChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerRetentionChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateCustomerRetentionChart();
         }
         
         // Initialize Customer Lifetime Value Chart
         function initializeCustomerLifetimeValueChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerLifetimeValueChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateCustomerLifetimeValueChart();
         }
         
         // Initialize Customer Segmentation Chart
         function initializeCustomerSegmentationChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerSegmentationChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateCustomerSegmentationChart();
         }
         
         // Initialize Customer Value Distribution Chart
         function initializeCustomerValueDistributionChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerValueDistributionChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateCustomerValueDistributionChart();
         }
         

         

         

         
         // Update RFM Breakdown Chart with filtered data
         function updateRfmBreakdownChart() {
             const filteredDates = filterDatesByRange(allDates, rfmStartDate, rfmEndDate);
             
             // Process RFM data for the filtered date range
             const rfmData = @json($hasData ? $rfmData : []);
             
                           // Debug: Log the first few records to see the data structure
              console.log('RFM Data sample:', rfmData.slice(0, 3));
              console.log('Available fields:', rfmData.length > 0 ? Object.keys(rfmData[0]) : 'No data');
              
              // Debug: Log a sample record to see exact field names
              if (rfmData.length > 0) {
                  console.log('Sample record:', rfmData[0]);
                  console.log('Sample record keys:', Object.keys(rfmData[0]));
                  console.log('Sample m_score value:', rfmData[0].m_score, 'Type:', typeof rfmData[0].m_score);
                  console.log('Sample rfm_score value:', rfmData[0].rfm_score, 'Type:', typeof rfmData[0].rfm_score);
                  
                  // Check if the values are actually numbers
                  const sampleM = parseFloat(rfmData[0].m_score);
                  const sampleRfm = parseFloat(rfmData[0].rfm_score);
                  console.log('Parsed m_score:', sampleM, 'isNaN:', isNaN(sampleM));
                  console.log('Parsed rfm_score:', sampleRfm, 'isNaN:', isNaN(sampleRfm));
              }
             
             const datasets = [
                 {
                     label: 'Recency',
                     data: filteredDates.map(date => {
                         const monthData = rfmData.filter(record => {
                             const recordDate = new Date(record.date).toISOString().slice(0, 7) + '-01';
                             return recordDate === date;
                         });
                         
                         if (monthData.length > 0) {
                             const avgScore = monthData.reduce((sum, r) => sum + (r.r_score || 0), 0) / monthData.length;
                             console.log(`Recency scores for ${date}:`, monthData.map(r => r.r_score), 'Average:', avgScore);
                             return Math.round(avgScore * 100) / 100;
                         }
                         return null;
                     }),
                     borderColor: '#EF4444',
                     backgroundColor: '#EF444420',
                     borderWidth: 2,
                     tension: 0.1
                 },
                 {
                     label: 'Frequency',
                     data: filteredDates.map(date => {
                         const monthData = rfmData.filter(record => {
                             const recordDate = new Date(record.date).toISOString().slice(0, 7) + '-01';
                             return recordDate === date;
                         });
                         
                         if (monthData.length > 0) {
                             const avgScore = monthData.reduce((sum, r) => sum + (r.f_score || 0), 0) / monthData.length;
                             console.log(`Frequency scores for ${date}:`, monthData.map(r => r.f_score), 'Average:', avgScore);
                             return Math.round(avgScore * 100) / 100;
                         }
                         return null;
                     }),
                     borderColor: '#10B981',
                     backgroundColor: '#10B98120',
                     borderWidth: 2,
                     tension: 0.1
                 },
                 {
                     label: 'Monetary',
                     data: filteredDates.map(date => {
                         const monthData = rfmData.filter(record => {
                             const recordDate = new Date(record.date).toISOString().slice(0, 7) + '-01';
                             return recordDate === date;
                         });
                         
                                                   if (monthData.length > 0) {
                              const scores = monthData.map(r => parseFloat(r.m_score) || 0);
                              const avgScore = scores.reduce((sum, score) => sum + score, 0) / scores.length;
                              console.log(`Monetary scores for ${date}:`, scores, 'Average:', avgScore);
                              return Math.round(avgScore * 100) / 100;
                          }
                         return null;
                     }),
                     borderColor: '#3B82F6',
                     backgroundColor: '#3B82F620',
                     borderWidth: 2,
                     tension: 0.1
                 },
                                   {
                      label: 'Overall RFM Score',
                      data: filteredDates.map(date => {
                          const monthData = rfmData.filter(record => {
                              const recordDate = new Date(record.date).toISOString().slice(0, 7) + '-01';
                              return recordDate === date;
                          });
                          
                          if (monthData.length > 0) {
                              const scores = monthData.map(r => parseFloat(r.rfm_score) || 0);
                              const avgScore = scores.reduce((sum, score) => sum + score, 0) / scores.length;
                              console.log(`Overall RFM scores for ${date}:`, scores, 'Average:', avgScore);
                              return Math.round(avgScore * 100) / 100;
                          }
                          return null;
                      }),
                      borderColor: '#8B5CF6',
                      backgroundColor: '#8B5CF620',
                      borderWidth: 3,
                      tension: 0.1,
                      pointRadius: 4,
                      pointHoverRadius: 6,
                      fill: false
                  }
             ];

             const labels = filteredDates.map(date => {
                 return new Date(date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
             });

             if (rfmBreakdownChart) {
                 rfmBreakdownChart.destroy();
             }

             const ctx = document.getElementById('rfmBreakdownChart');
             rfmBreakdownChart = new Chart(ctx, {
                 type: 'line',
                 data: { labels, datasets },
                 options: {
                     responsive: true,
                     maintainAspectRatio: false,
                     plugins: {
                         legend: {
                             position: 'top',
                             labels: {
                                 usePointStyle: true,
                                 padding: 20
                             }
                         },
                         tooltip: {
                             mode: 'index',
                             intersect: false
                         }
                     },
                     scales: {
                                                 y: {
                            beginAtZero: true,
                            max: 10,
                            title: {
                                display: true,
                                text: 'RFM Score',
                                color: '#FFFFFF'
                            },
                            ticks: {
                                color: '#FFFFFF'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date',
                                color: '#FFFFFF'
                            },
                            ticks: {
                                color: '#FFFFFF'
                            }
                        }
                     },
                     interaction: {
                         mode: 'nearest',
                         axis: 'x',
                         intersect: false
                     }
                 }
             });
         }
         // Update RFM Monthly Distribution Chart with filtered data
         function updateRfmMonthlyDistributionChart() {
             const filteredDates = filterDatesByRange(allDates, monthlyDistStartDate, monthlyDistEndDate);
             
             // Process RFM data for the filtered date range
             const rfmData = @json($hasData ? $rfmData : []);
             
             // Filter data by date range
             const filteredData = rfmData.filter(record => {
                 const recordDate = new Date(record.date);
                 const startDate = new Date(monthlyDistStartDate);
                 const endDate = new Date(monthlyDistEndDate);
                 return recordDate >= startDate && recordDate <= endDate;
             });
             
             if (filteredData.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('rfmMonthlyDistChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available for selected date range</div>';
                 return;
             }
             
             // Group data by month
             const monthlyData = {};
             filteredData.forEach(record => {
                 const monthKey = new Date(record.date).toISOString().slice(0, 7); // YYYY-MM format
                 if (!monthlyData[monthKey]) {
                     monthlyData[monthKey] = {
                         scores: [],
                         customers: new Set(),
                         highValueCount: 0
                     };
                 }
                 monthlyData[monthKey].scores.push(parseFloat(record.rfm_score) || 0);
                 monthlyData[monthKey].customers.add(record.client_id);
                 if (parseFloat(record.rfm_score) >= 8) {
                     monthlyData[monthKey].highValueCount++;
                 }
             });
             
             // Calculate monthly statistics
             const monthlyStats = Object.keys(monthlyData).map(month => {
                 const data = monthlyData[month];
                 const avgScore = data.scores.reduce((a, b) => a + b, 0) / data.scores.length;
                 const customerCount = data.customers.size;
                 const highValuePercent = (data.highValueCount / data.scores.length) * 100;
                 
                 return {
                     month: month,
                     avgScore: avgScore.toFixed(2),
                     customerCount: customerCount,
                     highValuePercent: highValuePercent.toFixed(1),
                     totalRecords: data.scores.length
                 };
             }).sort((a, b) => a.month.localeCompare(b.month));
             
             // Update insights
             if (monthlyStats.length > 0) {
                 const bestMonth = monthlyStats.reduce((max, current) => 
                     parseFloat(current.avgScore) > parseFloat(max.avgScore) ? current : max);
                 const worstMonth = monthlyStats.reduce((min, current) => 
                     parseFloat(current.avgScore) < parseFloat(min.avgScore) ? current : min);
                 const mostActiveMonth = monthlyStats.reduce((max, current) => 
                     current.totalRecords > max.totalRecords ? current : max);
                 
                 document.getElementById('peakMonth').textContent = new Date(bestMonth.month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                 document.getElementById('mostActiveMonth').textContent = new Date(mostActiveMonth.month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                 
                 // Calculate seasonal trend
                 const avgScores = monthlyStats.map(s => parseFloat(s.avgScore));
                 const trend = avgScores[avgScores.length - 1] > avgScores[0] ? 'Growing' : 'Declining';
                 document.getElementById('seasonalTrend').textContent = trend;
                 
                 // Calculate consistency score
                 const variance = avgScores.reduce((sum, score, index) => {
                     const mean = avgScores.reduce((a, b) => a + b, 0) / avgScores.length;
                     return sum + Math.pow(score - mean, 2);
                 }, 0) / avgScores.length;
                 const consistency = Math.max(0, 10 - Math.sqrt(variance)).toFixed(1);
                 document.getElementById('consistencyScore').textContent = consistency + '/10';
                 
                 // Update performance insights
                 document.getElementById('bestMonth').textContent = new Date(bestMonth.month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                 document.getElementById('worstMonth').textContent = new Date(worstMonth.month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                 
                 const overallAvgScore = avgScores.reduce((a, b) => a + b, 0) / avgScores.length;
                 document.getElementById('avgMonthlyScore').textContent = overallAvgScore.toFixed(2);
                 
                 const scoreRange = (Math.max(...avgScores) - Math.min(...avgScores)).toFixed(2);
                 document.getElementById('scoreRange').textContent = scoreRange;
                 
                 const growthRate = avgScores.length > 1 ? 
                     (((avgScores[avgScores.length - 1] - avgScores[0]) / avgScores[0]) * 100).toFixed(1) + '%' : '0%';
                 document.getElementById('monthlyGrowthRate').textContent = growthRate;
                 
                 const volatility = Math.sqrt(variance).toFixed(2);
                 document.getElementById('volatility').textContent = volatility;
             }
             
             // Create chart data
             const chartData = {
                 labels: monthlyStats.map(s => new Date(s.month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' })),
                 datasets: [
                     {
                         label: 'Average RFM Score',
                         data: monthlyStats.map(s => parseFloat(s.avgScore)),
                         borderColor: '#3B82F6',
                         backgroundColor: '#3B82F620',
                         borderWidth: 3,
                         tension: 0.1,
                         fill: false
                     },
                     {
                         label: 'Customer Count',
                         data: monthlyStats.map(s => s.customerCount),
                         borderColor: '#10B981',
                         backgroundColor: '#10B98120',
                         borderWidth: 2,
                         tension: 0.1,
                         fill: false,
                         yAxisID: 'y1'
                     }
                 ]
             };
             
             // Update or create chart
             const ctx = document.getElementById('rfmMonthlyDistChart');
             if (ctx) {
                 ctx.style.display = 'block';
                 
                 if (rfmMonthlyDistChart) {
                     rfmMonthlyDistChart.destroy();
                 }
                 
                 rfmMonthlyDistChart = new Chart(ctx, {
                     type: 'line',
                     data: chartData,
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: {
                                 position: 'top',
                                 labels: {
                                     usePointStyle: true,
                                     padding: 20,
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 }
                             },
                             tooltip: {
                                 mode: 'index',
                                 intersect: false,
                                 backgroundColor: document.documentElement.classList.contains('dark') ? '#374151' : '#FFFFFF',
                                 titleColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 bodyColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 borderColor: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                                 borderWidth: 1
                             }
                         },
                         scales: {
                                                         y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'RFM Score',
                                    color: '#FFFFFF'
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                },
                                 grid: {
                                     color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                 }
                             },
                                                         y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Customer Count',
                                    color: '#FFFFFF'
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                },
                                 grid: {
                                     drawOnChartArea: false
                                 }
                             },
                                                         x: {
                                title: {
                                    display: true,
                                    text: 'Month',
                                    color: '#FFFFFF'
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                },
                                 grid: {
                                     color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                 }
                             }
                         },
                         interaction: {
                             mode: 'nearest',
                             axis: 'x',
                             intersect: false
                         }
                     }
                 });
             }
             
             // Update monthly breakdown table
             updateMonthlyBreakdownTable(monthlyStats);
         }
         
         // Update monthly breakdown table
         function updateMonthlyBreakdownTable(monthlyStats) {
             const tableBody = document.getElementById('monthlyBreakdownTable');
             if (!tableBody) return;
             
             const html = monthlyStats.map(stat => {
                 const trend = parseFloat(stat.avgScore) > 7 ? '📈' : parseFloat(stat.avgScore) > 5 ? '➡️' : '📉';
                 return `
                     <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                         <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                             ${new Date(stat.month + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
                         </td>
                         <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                             ${stat.avgScore}
                         </td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                             ${stat.customerCount}
                         </td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                             ${stat.highValuePercent}%
                         </td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                             ${trend}
                         </td>
                     </tr>
                 `;
             }).join('');
             
             tableBody.innerHTML = html;
         }
         
         // Date range functions for RFM Monthly Distribution
         function updateMonthlyDistDateRange() {
             const startInput = document.getElementById('monthlyDistStartDate');
             const endInput = document.getElementById('monthlyDistEndDate');
             
             monthlyDistStartDate = startInput.value;
             monthlyDistEndDate = endInput.value;
             
             if (rfmMonthlyDistChart) {
                 updateRfmMonthlyDistributionChart();
             }
         }
         
         function setMonthlyDistDateRange(preset) {
             const startInput = document.getElementById('monthlyDistStartDate');
             const endInput = document.getElementById('monthlyDistEndDate');
             
             if (allDates.length === 0) return;
             
             const lastDate = new Date(allDates[allDates.length - 1]);
             let startDate = new Date(lastDate);
             
             switch(preset) {
                 case '6m':
                     startDate.setMonth(startDate.getMonth() - 6);
                     break;
                 case '12m':
                     startDate.setMonth(startDate.getMonth() - 12);
                     break;
                 case '24m':
                     startDate.setMonth(startDate.getMonth() - 24);
                     break;
                 case 'all':
                     startDate = new Date(allDates[0]);
                     break;
             }
             
             // Ensure start date is not before the earliest available date
             const earliestDate = new Date(allDates[0]);
             if (startDate < earliestDate) {
                 startDate = earliestDate;
             }
             
             startInput.value = startDate.toISOString().split('T')[0];
             endInput.value = lastDate.toISOString().split('T')[0];
             
             monthlyDistStartDate = startInput.value;
             monthlyDistEndDate = endInput.value;
             
             if (rfmMonthlyDistChart) {
                 updateRfmMonthlyDistributionChart();
             }
         }
         
         // Update Customer Value Distribution Chart with filtered data
         // Update RFM Score Over Time Chart
         function updateRfmScoreOverTimeChart() {
             // Process RFM data
             const rfmData = @json($hasData ? $rfmData : []);
             
             if (rfmData.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('rfmScoreOverTimeChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
                 return;
             }
             
             // Filter data by score range if needed
             let filteredData = rfmData;
             if (scoreRangeFilter !== 'all') {
                 filteredData = rfmData.filter(record => {
                     const score = parseFloat(record.rfm_score) || 0;
                     switch(scoreRangeFilter) {
                         case 'high': return score >= 8;
                         case 'medium': return score >= 5 && score < 8;
                         case 'low': return score >= 1 && score < 5;
                         default: return true;
                     }
                 });
             }
             
             // Group data by time period
             const periodData = {};
             
             filteredData.forEach(record => {
                 const date = new Date(record.date);
                 let periodKey = '';
                 
                 switch(timePeriodType) {
                     case 'monthly':
                         periodKey = date.toISOString().slice(0, 7); // YYYY-MM
                         break;
                     case 'quarterly':
                         const quarter = Math.ceil((date.getMonth() + 1) / 3);
                         periodKey = `${date.getFullYear()}-Q${quarter}`;
                         break;
                     case 'yearly':
                         periodKey = date.getFullYear().toString();
                         break;
                 }
                 
                 if (!periodData[periodKey]) {
                     periodData[periodKey] = {
                         scores: [],
                         count: 0
                     };
                 }
                 
                 periodData[periodKey].scores.push(parseFloat(record.rfm_score) || 0);
                 periodData[periodKey].count++;
             });
             
             // Calculate statistics for each period
             const periodStats = Object.keys(periodData).map(period => {
                 const data = periodData[period];
                 const avgScore = data.scores.reduce((a, b) => a + b, 0) / data.scores.length;
                 const highScorePercent = (data.scores.filter(s => s >= 8).length / data.scores.length) * 100;
                 const mediumScorePercent = (data.scores.filter(s => s >= 5 && s < 8).length / data.scores.length) * 100;
                 const lowScorePercent = (data.scores.filter(s => s >= 1 && s < 5).length / data.scores.length) * 100;
                 
                 return {
                     period: period,
                     avgScore: avgScore.toFixed(2),
                     count: data.count,
                     highPercent: highScorePercent.toFixed(1),
                     mediumPercent: mediumScorePercent.toFixed(1),
                     lowPercent: lowScorePercent.toFixed(1)
                 };
             }).sort((a, b) => a.period.localeCompare(b.period));
             
             if (periodStats.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('rfmScoreOverTimeChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available for selected filters</div>';
                 return;
             }
             
             // Calculate overall statistics
             const allScores = filteredData.map(r => parseFloat(r.rfm_score) || 0);
             const overallAvg = allScores.reduce((a, b) => a + b, 0) / allScores.length;
             
             const bestPeriod = periodStats.reduce((max, current) => 
                 parseFloat(current.avgScore) > parseFloat(max.avgScore) ? current : max);
             const worstPeriod = periodStats.reduce((min, current) => 
                 parseFloat(current.avgScore) < parseFloat(min.avgScore) ? current : min);
             
             const scoreRange = Math.max(...allScores) - Math.min(...allScores);
             
             // Calculate trend
             const firstAvg = parseFloat(periodStats[0].avgScore);
             const lastAvg = parseFloat(periodStats[periodStats.length - 1].avgScore);
             const trendDirection = lastAvg > firstAvg ? 'Improving' : lastAvg < firstAvg ? 'Declining' : 'Stable';
             const growthRate = ((lastAvg - firstAvg) / firstAvg * 100).toFixed(1);
             
             // Calculate volatility (standard deviation of period averages)
             const periodAverages = periodStats.map(p => parseFloat(p.avgScore));
             const avgOfAverages = periodAverages.reduce((a, b) => a + b, 0) / periodAverages.length;
             const variance = periodAverages.reduce((sum, avg) => sum + Math.pow(avg - avgOfAverages, 2), 0) / periodAverages.length;
             const volatility = Math.sqrt(variance).toFixed(2);
             
             // Determine seasonal pattern
             let seasonalPattern = 'None';
             if (periodStats.length >= 4) {
                 const recentAvg = periodAverages.slice(-3).reduce((a, b) => a + b, 0) / 3;
                 const earlierAvg = periodAverages.slice(0, 3).reduce((a, b) => a + b, 0) / 3;
                 if (recentAvg > earlierAvg * 1.1) seasonalPattern = 'Improving';
                 else if (recentAvg < earlierAvg * 0.9) seasonalPattern = 'Declining';
                 else seasonalPattern = 'Stable';
             }
             
             // Update statistics
             document.getElementById('peakPeriod').textContent = bestPeriod.period;
             document.getElementById('growthTrend').textContent = growthRate + '%';
             document.getElementById('scoreVolatility').textContent = volatility;
             document.getElementById('seasonalPattern').textContent = seasonalPattern;
             
             document.getElementById('bestPeriod').textContent = bestPeriod.period;
             document.getElementById('worstPeriod').textContent = worstPeriod.period;
             document.getElementById('avgScoreOverTime').textContent = overallAvg.toFixed(2);
             document.getElementById('scoreRangeOverTime').textContent = scoreRange.toFixed(2);
             
             document.getElementById('trendDirection').textContent = trendDirection;
             document.getElementById('scoreConsistency').textContent = volatility < 1 ? 'High' : volatility < 2 ? 'Medium' : 'Low';
             document.getElementById('periodsAnalyzed').textContent = periodStats.length;
             document.getElementById('totalRecordsOverTime').textContent = filteredData.length;
             
             // Update insights
             document.getElementById('timeInsight1').textContent = `RFM scores ${trendDirection.toLowerCase()} over time with ${growthRate}% change`;
             document.getElementById('timeInsight2').textContent = `Peak performance in ${bestPeriod.period} with avg score of ${bestPeriod.avgScore}`;
             document.getElementById('timeInsight3').textContent = `Volatility of ${volatility} indicates ${volatility < 1 ? 'consistent' : 'variable'} performance`;
             
             // Create histogram chart data
             const chartData = {
                 labels: periodStats.map(p => p.period),
                 datasets: [{
                     label: 'Average RFM Score',
                     data: periodStats.map(p => parseFloat(p.avgScore)),
                     backgroundColor: 'rgba(59, 130, 246, 0.8)',
                     borderColor: '#3B82F6',
                     borderWidth: 2,
                     borderRadius: 4,
                     borderSkipped: false
                 }]
             };
             
             // Update or create chart
             const ctx = document.getElementById('rfmScoreOverTimeChart');
             if (ctx) {
                 ctx.style.display = 'block';
                 
                 if (rfmScoreOverTimeChart) {
                     rfmScoreOverTimeChart.destroy();
                 }
                 
                 rfmScoreOverTimeChart = new Chart(ctx, {
                     type: 'bar',
                     data: chartData,
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: {
                                 display: false
                             },
                             tooltip: {
                                 backgroundColor: document.documentElement.classList.contains('dark') ? '#374151' : '#FFFFFF',
                                 titleColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 bodyColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 borderColor: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                                 borderWidth: 1,
                                 callbacks: {
                                     label: function(context) {
                                         const value = context.parsed.y;
                                         const period = periodStats[context.dataIndex];
                                         return `Avg Score: ${value} | Records: ${period.count}`;
                                     }
                                 }
                             }
                         },
                         scales: {
                             y: {
                                 beginAtZero: true,
                                 max: 10,
                                 title: {
                                     display: true,
                                     text: 'Average RFM Score',
                                     color: '#FFFFFF'
                                 },
                                 ticks: {
                                     color: '#FFFFFF'
                                 },
                                 grid: {
                                     color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                 }
                             },
                             x: {
                                 title: {
                                     display: true,
                                     text: 'Time Period',
                                     color: '#FFFFFF'
                                 },
                                 ticks: {
                                     color: '#FFFFFF'
                                 },
                                 grid: {
                                     color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                 }
                             }
                         }
                     }
                 });
             }
             
             // Update period breakdown table
             updatePeriodBreakdownTable(periodStats);
         }
         
         // Update period breakdown table
         function updatePeriodBreakdownTable(periodStats) {
             const tableBody = document.getElementById('periodBreakdownTable');
             if (!tableBody) return;
             
             const html = periodStats.map((stat, index) => {
                 const trend = index > 0 ? 
                     (parseFloat(stat.avgScore) > parseFloat(periodStats[index - 1].avgScore) ? '📈' : 
                      parseFloat(stat.avgScore) < parseFloat(periodStats[index - 1].avgScore) ? '📉' : '➡️') : '➡️';
                 
                 return `
                     <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                         <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${stat.period}</td>
                         <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">${stat.avgScore}</td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${stat.highPercent}%</td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${stat.mediumPercent}%</td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${stat.lowPercent}%</td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${trend}</td>
                     </tr>
                 `;
             }).join('');
             
             tableBody.innerHTML = html;
         }
         
         // Control functions for RFM Score Over Time
         function updateTimePeriodHistogram() {
             const timeSelect = document.getElementById('timePeriodSelect');
             const scoreSelect = document.getElementById('scoreRangeSelect');
             
             timePeriodType = timeSelect.value;
             scoreRangeFilter = scoreSelect.value;
             
             if (rfmScoreOverTimeChart) {
                 updateRfmScoreOverTimeChart();
             }
         }
         
         // Update Customer Retention Chart
         function updateCustomerRetentionChart() {
             // Process RFM data
             const rfmData = @json($hasData ? $rfmData : []);
             
             if (rfmData.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerRetentionChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
                 return;
             }
             
             // Calculate retention periods
             const monthsBack = retentionPeriod === '3m' ? 3 : retentionPeriod === '6m' ? 6 : 12;
             const cutoffDate = new Date();
             cutoffDate.setMonth(cutoffDate.getMonth() - monthsBack);
             
             // Group data by customer and analyze retention
             const customerData = {};
             
             rfmData.forEach(record => {
                 const clientId = record.client_id;
                 const recordDate = new Date(record.date);
                 
                 if (!customerData[clientId]) {
                     customerData[clientId] = {
                         name: record.client_name,
                         records: [],
                         firstSeen: recordDate,
                         lastSeen: recordDate,
                         avgRfmScore: 0,
                         retentionStatus: 'unknown'
                     };
                 }
                 
                 customerData[clientId].records.push({
                     date: recordDate,
                     rfmScore: parseFloat(record.rfm_score) || 0,
                     rScore: parseFloat(record.r_score) || 0,
                     fScore: parseFloat(record.f_score) || 0,
                     mScore: parseFloat(record.m_score) || 0
                 });
                 
                 if (recordDate < customerData[clientId].firstSeen) {
                     customerData[clientId].firstSeen = recordDate;
                 }
                 if (recordDate > customerData[clientId].lastSeen) {
                     customerData[clientId].lastSeen = recordDate;
                 }
             });
             
             // Calculate retention metrics for each customer
             Object.values(customerData).forEach(customer => {
                 const avgRfmScore = customer.records.reduce((sum, r) => sum + r.rfmScore, 0) / customer.records.length;
                 customer.avgRfmScore = avgRfmScore;
                 
                 // Determine retention status based on recent activity
                 const daysSinceLastActivity = (new Date() - customer.lastSeen) / (1000 * 60 * 60 * 24);
                 
                 if (daysSinceLastActivity <= 30) {
                     customer.retentionStatus = 'retained';
                 } else if (daysSinceLastActivity <= 90) {
                     customer.retentionStatus = 'at-risk';
                 } else {
                     customer.retentionStatus = 'lost';
                 }
             });
             
             // Calculate retention statistics based on analysis type
             let chartData = {};
             let chartLabels = [];
             let chartValues = [];
             
             switch(retentionAnalysisType) {
                 case 'score-based':
                     // Group by RFM score ranges
                     const scoreRanges = {
                         'High Value (8-10)': { retained: 0, atRisk: 0, lost: 0, total: 0 },
                         'Medium Value (5-7)': { retained: 0, atRisk: 0, lost: 0, total: 0 },
                         'Low Value (1-4)': { retained: 0, atRisk: 0, lost: 0, total: 0 }
                     };
                     
                     Object.values(customerData).forEach(customer => {
                         let range = '';
                         if (customer.avgRfmScore >= 8) range = 'High Value (8-10)';
                         else if (customer.avgRfmScore >= 5) range = 'Medium Value (5-7)';
                         else range = 'Low Value (1-4)';
                         
                         scoreRanges[range].total++;
                         scoreRanges[range][customer.retentionStatus]++;
                     });
                     
                     chartLabels = Object.keys(scoreRanges);
                     chartValues = chartLabels.map(range => {
                         const data = scoreRanges[range];
                         return data.total > 0 ? ((data.retained / data.total) * 100).toFixed(1) : 0;
                     });
                     
                     // Update statistics
                     const overallRetention = Object.values(customerData).filter(c => c.retentionStatus === 'retained').length / Object.keys(customerData).length * 100;
                     const highValueRetention = scoreRanges['High Value (8-10)'].total > 0 ? 
                         (scoreRanges['High Value (8-10)'].retained / scoreRanges['High Value (8-10)'].total * 100).toFixed(1) : 0;
                     const atRiskCount = Object.values(customerData).filter(c => c.retentionStatus === 'at-risk').length;
                     const churnRate = Object.values(customerData).filter(c => c.retentionStatus === 'lost').length / Object.keys(customerData).length * 100;
                     
                     document.getElementById('overallRetention').textContent = overallRetention.toFixed(1) + '%';
                     document.getElementById('highValueRetention').textContent = highValueRetention + '%';
                     document.getElementById('atRiskCustomers').textContent = atRiskCount;
                     document.getElementById('churnRate').textContent = churnRate.toFixed(1) + '%';
                     
                     // Update breakdown table
                     updateRetentionBreakdownTable(scoreRanges);
                     break;
                     
                 case 'time-based':
                     // Group by time periods
                     const timeRanges = {
                         'Recent (0-3 months)': { retained: 0, atRisk: 0, lost: 0, total: 0 },
                         'Medium (3-6 months)': { retained: 0, atRisk: 0, lost: 0, total: 0 },
                         'Older (6+ months)': { retained: 0, atRisk: 0, lost: 0, total: 0 }
                     };
                     
                     Object.values(customerData).forEach(customer => {
                         const monthsSinceFirst = (new Date() - customer.firstSeen) / (1000 * 60 * 60 * 24 * 30);
                         let range = '';
                         if (monthsSinceFirst <= 3) range = 'Recent (0-3 months)';
                         else if (monthsSinceFirst <= 6) range = 'Medium (3-6 months)';
                         else range = 'Older (6+ months)';
                         
                         timeRanges[range].total++;
                         timeRanges[range][customer.retentionStatus]++;
                     });
                     
                     chartLabels = Object.keys(timeRanges);
                     chartValues = chartLabels.map(range => {
                         const data = timeRanges[range];
                         return data.total > 0 ? ((data.retained / data.total) * 100).toFixed(1) : 0;
                     });
                     
                     updateRetentionBreakdownTable(timeRanges);
                     break;
                     
                 case 'segment-based':
                     // Group by customer segments
                     const segments = {
                         'Champions': { retained: 0, atRisk: 0, lost: 0, total: 0 },
                         'Loyal Customers': { retained: 0, atRisk: 0, lost: 0, total: 0 },
                         'At Risk': { retained: 0, atRisk: 0, lost: 0, total: 0 },
                         'Lost': { retained: 0, atRisk: 0, lost: 0, total: 0 }
                     };
                     
                     Object.values(customerData).forEach(customer => {
                         let segment = '';
                         if (customer.avgRfmScore >= 8) segment = 'Champions';
                         else if (customer.avgRfmScore >= 6) segment = 'Loyal Customers';
                         else if (customer.avgRfmScore >= 4) segment = 'At Risk';
                         else segment = 'Lost';
                         
                         segments[segment].total++;
                         segments[segment][customer.retentionStatus]++;
                     });
                     
                     chartLabels = Object.keys(segments);
                     chartValues = chartLabels.map(segment => {
                         const data = segments[segment];
                         return data.total > 0 ? ((data.retained / data.total) * 100).toFixed(1) : 0;
                     });
                     
                     updateRetentionBreakdownTable(segments);
                     break;
             }
             
             // Create chart
             const chartConfig = {
                 labels: chartLabels,
                 datasets: [{
                     label: 'Retention Rate (%)',
                     data: chartValues,
                     backgroundColor: 'rgba(16, 185, 129, 0.8)',
                     borderColor: '#10B981',
                     borderWidth: 2,
                     borderRadius: 4,
                     borderSkipped: false
                 }]
             };
             // Update or create chart
             const ctx = document.getElementById('customerRetentionChart');
             if (ctx) {
                 ctx.style.display = 'block';
                 
                 if (customerRetentionChart) {
                     customerRetentionChart.destroy();
                 }
                 
                 customerRetentionChart = new Chart(ctx, {
                     type: 'bar',
                     data: chartConfig,
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: {
                                 display: false
                             },
                             tooltip: {
                                 backgroundColor: document.documentElement.classList.contains('dark') ? '#374151' : '#FFFFFF',
                                 titleColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 bodyColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 borderColor: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                                 borderWidth: 1,
                                 callbacks: {
                                     label: function(context) {
                                         return `Retention Rate: ${context.parsed.y}%`;
                                     }
                                 }
                             }
                         },
                                                 scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Retention Rate (%)',
                                    color: '#FFFFFF'
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Customer Segments',
                                    color: '#FFFFFF'
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                }
                            }
                        }
                     }
                 });
             }
             
             // Update insights
             const totalCustomers = Object.keys(customerData).length;
             const retainedCustomers = Object.values(customerData).filter(c => c.retentionStatus === 'retained').length;
             const lostCustomers = Object.values(customerData).filter(c => c.retentionStatus === 'lost').length;
             const newCustomers = Object.values(customerData).filter(c => {
                 const monthsSinceFirst = (new Date() - c.firstSeen) / (1000 * 60 * 60 * 24 * 30);
                 return monthsSinceFirst <= 3;
             }).length;
             
             const bestSegment = chartLabels[chartValues.indexOf(Math.max(...chartValues))];
             const worstSegment = chartLabels[chartValues.indexOf(Math.min(...chartValues))];
             const retentionTrend = chartValues[chartValues.length - 1] > chartValues[0] ? 'Improving' : 'Declining';
             const retentionScore = ((retainedCustomers / totalCustomers) * 100).toFixed(1);
             
             document.getElementById('bestRetainingSegment').textContent = bestSegment;
             document.getElementById('worstRetainingSegment').textContent = worstSegment;
             document.getElementById('retentionTrend').textContent = retentionTrend;
             document.getElementById('totalCustomersRetention').textContent = totalCustomers;
             
             document.getElementById('retainedCustomers').textContent = retainedCustomers;
             document.getElementById('lostCustomers').textContent = lostCustomers;
             document.getElementById('newCustomers').textContent = newCustomers;
             document.getElementById('retentionScore').textContent = retentionScore + '%';
             
             document.getElementById('retentionInsight1').textContent = `Overall retention rate of ${retentionScore}% with ${retainedCustomers} retained customers`;
             document.getElementById('retentionInsight2').textContent = `${bestSegment} has the highest retention rate, while ${worstSegment} needs attention`;
             document.getElementById('retentionInsight3').textContent = `Retention trend is ${retentionTrend.toLowerCase()} with ${atRiskCount} customers at risk`;
         }
         
         // Update retention breakdown table
         function updateRetentionBreakdownTable(data) {
             const tableBody = document.getElementById('retentionBreakdownTable');
             if (!tableBody) return;
             
             const html = Object.keys(data).map(segment => {
                 const segmentData = data[segment];
                 const retentionRate = segmentData.total > 0 ? ((segmentData.retained / segmentData.total) * 100).toFixed(1) : 0;
                 
                 let riskLevel = 'Low';
                 if (retentionRate < 50) riskLevel = 'High';
                 else if (retentionRate < 75) riskLevel = 'Medium';
                 
                 let riskColor = 'text-green-600 dark:text-green-400';
                 if (riskLevel === 'High') riskColor = 'text-red-600 dark:text-red-400';
                 else if (riskLevel === 'Medium') riskColor = 'text-yellow-600 dark:text-yellow-400';
                 
                 return `
                     <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                         <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${segment}</td>
                         <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">${retentionRate}%</td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${segmentData.total}</td>
                         <td class="px-4 py-3 text-sm text-green-600 dark:text-green-400">${segmentData.retained}</td>
                         <td class="px-4 py-3 text-sm text-red-600 dark:text-red-400">${segmentData.lost}</td>
                         <td class="px-4 py-3 text-sm ${riskColor}">${riskLevel}</td>
                     </tr>
                 `;
             }).join('');
             
             tableBody.innerHTML = html;
         }
         
         // Control functions for Customer Retention
         function updateRetentionAnalysis() {
             const periodSelect = document.getElementById('retentionPeriodSelect');
             const analysisSelect = document.getElementById('retentionAnalysisType');
             
             retentionPeriod = periodSelect.value;
             retentionAnalysisType = analysisSelect.value;
             
             if (customerRetentionChart) {
                 updateCustomerRetentionChart();
             }
         }
         
         // Update Customer Lifetime Value Chart
         function updateCustomerLifetimeValueChart() {
             // Process RFM data
             const rfmData = @json($hasData ? $rfmData : []);
             
             if (rfmData.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerLifetimeValueChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
                 return;
             }
             
             // Calculate time horizon in years
             const timeHorizonYears = parseInt(clvTimeHorizon.replace('y', ''));
             const discountRate = parseFloat(clvDiscountRate);
             
             // Group data by customer and calculate CLV
             const customerData = {};
             
             rfmData.forEach(record => {
                 const clientId = record.client_id;
                 const recordDate = new Date(record.date);
                 
                 if (!customerData[clientId]) {
                     customerData[clientId] = {
                         name: record.client_name,
                         records: [],
                         firstSeen: recordDate,
                         lastSeen: recordDate,
                         avgRfmScore: 0,
                         avgRScore: 0,
                         avgFScore: 0,
                         avgMScore: 0,
                         totalRevenue: 0,
                         purchaseFrequency: 0,
                         clv: 0
                     };
                 }
                 
                 customerData[clientId].records.push({
                     date: recordDate,
                     rfmScore: parseFloat(record.rfm_score) || 0,
                     rScore: parseFloat(record.r_score) || 0,
                     fScore: parseFloat(record.f_score) || 0,
                     mScore: parseFloat(record.m_score) || 0
                 });
                 
                 if (recordDate < customerData[clientId].firstSeen) {
                     customerData[clientId].firstSeen = recordDate;
                 }
                 if (recordDate > customerData[clientId].lastSeen) {
                     customerData[clientId].lastSeen = recordDate;
                 }
             });
             
             // Calculate CLV for each customer
             Object.values(customerData).forEach(customer => {
                 const avgRfmScore = customer.records.reduce((sum, r) => sum + r.rfmScore, 0) / customer.records.length;
                 const avgRScore = customer.records.reduce((sum, r) => sum + r.rScore, 0) / customer.records.length;
                 const avgFScore = customer.records.reduce((sum, r) => sum + r.fScore, 0) / customer.records.length;
                 const avgMScore = customer.records.reduce((sum, r) => sum + r.mScore, 0) / customer.records.length;
                 
                 customer.avgRfmScore = avgRfmScore;
                 customer.avgRScore = avgRScore;
                 customer.avgFScore = avgFScore;
                 customer.avgMScore = avgMScore;
                 
                 // Calculate purchase frequency (purchases per year)
                 const daysSinceFirst = (customer.lastSeen - customer.firstSeen) / (1000 * 60 * 60 * 24);
                 const yearsSinceFirst = daysSinceFirst / 365;
                 customer.purchaseFrequency = yearsSinceFirst > 0 ? customer.records.length / yearsSinceFirst : customer.records.length;
                 
                 // Calculate CLV based on method
                 let clv = 0;
                 
                 switch(clvMethod) {
                     case 'rfm-based':
                         // CLV based on RFM score (higher score = higher value)
                         const baseValue = avgMScore * 1000; // Base value from M-score
                         const frequencyMultiplier = avgFScore / 5; // Frequency multiplier
                         const recencyMultiplier = avgRScore / 5; // Recency multiplier
                         clv = baseValue * frequencyMultiplier * recencyMultiplier * timeHorizonYears;
                         break;
                         
                     case 'frequency-based':
                         // CLV based on purchase frequency
                         const avgPurchaseValue = avgMScore * 800; // Average purchase value
                         clv = avgPurchaseValue * customer.purchaseFrequency * timeHorizonYears;
                         break;
                         
                     case 'monetary-based':
                         // CLV based on monetary value
                         const monetaryValue = avgMScore * 1200; // Direct monetary value
                         const retentionRate = avgRScore / 5; // Retention based on recency
                         clv = monetaryValue * retentionRate * timeHorizonYears;
                         break;
                 }
                 
                 // Apply discount rate
                 customer.clv = clv / Math.pow(1 + discountRate, timeHorizonYears);
                 customer.totalRevenue = customer.clv; // For display purposes
             });
             
             // Calculate CLV statistics based on segments
             const clvSegments = {
                 'Premium (CLV > £10,000)': { customers: [], totalClv: 0, avgClv: 0 },
                 'High Value (£5,000 - £10,000)': { customers: [], totalClv: 0, avgClv: 0 },
                 'Medium Value (£2,000 - £5,000)': { customers: [], totalClv: 0, avgClv: 0 },
                 'Standard (£500 - £2,000)': { customers: [], totalClv: 0, avgClv: 0 },
                 'Low Value (< £500)': { customers: [], totalClv: 0, avgClv: 0 }
             };
             
             Object.values(customerData).forEach(customer => {
                 let segment = '';
                 if (customer.clv > 10000) segment = 'Premium (CLV > £10,000)';
                 else if (customer.clv > 5000) segment = 'High Value (£5,000 - £10,000)';
                 else if (customer.clv > 2000) segment = 'Medium Value (£2,000 - £5,000)';
                 else if (customer.clv > 500) segment = 'Standard (£500 - £2,000)';
                 else segment = 'Low Value (< £500)';
                 
                 clvSegments[segment].customers.push(customer);
                 clvSegments[segment].totalClv += customer.clv;
             });
             
             // Calculate averages
             Object.keys(clvSegments).forEach(segment => {
                 const segmentData = clvSegments[segment];
                 segmentData.avgClv = segmentData.customers.length > 0 ? segmentData.totalClv / segmentData.customers.length : 0;
             });
             
             // Create chart data
             const chartLabels = Object.keys(clvSegments);
             const chartValues = chartLabels.map(segment => clvSegments[segment].avgClv);
             const chartColors = [
                 'rgba(147, 51, 234, 0.8)', // Purple
                 'rgba(59, 130, 246, 0.8)',  // Blue
                 'rgba(16, 185, 129, 0.8)',  // Green
                 'rgba(245, 158, 11, 0.8)',  // Orange
                 'rgba(239, 68, 68, 0.8)'    // Red
             ];
             
             // Create chart
             const chartConfig = {
                 labels: chartLabels,
                 datasets: [{
                     label: 'Average CLV (£)',
                     data: chartValues,
                     backgroundColor: chartColors,
                     borderColor: chartColors.map(color => color.replace('0.8', '1')),
                     borderWidth: 2,
                     borderRadius: 4,
                     borderSkipped: false
                 }]
             };
             
             // Update or create chart
             const ctx = document.getElementById('customerLifetimeValueChart');
             if (ctx) {
                 ctx.style.display = 'block';
                 
                 if (customerLifetimeValueChart) {
                     customerLifetimeValueChart.destroy();
                 }
                 
                 customerLifetimeValueChart = new Chart(ctx, {
                     type: 'bar',
                     data: chartConfig,
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: {
                                 display: false
                             },
                             tooltip: {
                                 backgroundColor: document.documentElement.classList.contains('dark') ? '#374151' : '#FFFFFF',
                                 titleColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 bodyColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                 borderColor: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                                 borderWidth: 1,
                                 callbacks: {
                                     label: function(context) {
                                         return `Average CLV: £${context.parsed.y.toLocaleString()}`;
                                     }
                                 }
                             }
                         },
                                                 scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Average CLV (£)',
                                    color: '#FFFFFF'
                                },
                                ticks: {
                                    color: '#FFFFFF',
                                    callback: function(value) {
                                        return '£' + value.toLocaleString();
                                    }
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Customer Segments',
                                    color: '#FFFFFF'
                                },
                                ticks: {
                                    color: '#FFFFFF'
                                },
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                }
                            }
                        }
                     }
                 });
             }
             
             // Update statistics
             const totalClv = Object.values(customerData).reduce((sum, c) => sum + c.clv, 0);
             const averageClv = totalClv / Object.keys(customerData).length;
             const highValueCustomers = clvSegments['Premium (CLV > £10,000)'].customers.length + 
                                      clvSegments['High Value (£5,000 - £10,000)'].customers.length;
             const roiPotential = (totalClv / Object.keys(customerData).length) * 0.3; // 30% ROI assumption
             
             const highestClvCustomer = Object.values(customerData).reduce((max, c) => c.clv > max.clv ? c : max);
             const clvRange = `£${Math.min(...Object.values(customerData).map(c => c.clv)).toFixed(0)} - £${Math.max(...Object.values(customerData).map(c => c.clv)).toFixed(0)}`;
             const clvGrowthRate = ((averageClv / 5000) - 1) * 100; // Growth vs baseline
             
             const premiumCustomers = clvSegments['Premium (CLV > £10,000)'].customers.length;
             const standardCustomers = clvSegments['Standard (£500 - £2,000)'].customers.length;
             const lowValueCustomers = clvSegments['Low Value (< £500)'].customers.length;
             const clvScore = ((averageClv / 10000) * 100).toFixed(1); // CLV score out of 100
             
             document.getElementById('totalClv').textContent = '£' + totalClv.toLocaleString();
             document.getElementById('averageClv').textContent = '£' + averageClv.toLocaleString();
             document.getElementById('highValueCustomers').textContent = highValueCustomers;
             document.getElementById('roiPotential').textContent = '£' + roiPotential.toLocaleString();
             
             document.getElementById('highestClvCustomer').textContent = highestClvCustomer.name;
             document.getElementById('clvRange').textContent = clvRange;
             document.getElementById('clvGrowthRate').textContent = clvGrowthRate.toFixed(1) + '%';
             document.getElementById('totalCustomersClv').textContent = Object.keys(customerData).length;
             
             document.getElementById('premiumCustomers').textContent = premiumCustomers;
             document.getElementById('standardCustomers').textContent = standardCustomers;
             document.getElementById('lowValueCustomers').textContent = lowValueCustomers;
             document.getElementById('clvScore').textContent = clvScore + '/100';
             
             // Update breakdown table
             updateClvBreakdownTable(clvSegments, totalClv);
             
             // Update insights
             const bestSegment = chartLabels[chartValues.indexOf(Math.max(...chartValues))];
             const worstSegment = chartLabels[chartValues.indexOf(Math.min(...chartValues))];
             
             document.getElementById('clvInsight1').textContent = `Total predicted revenue of £${totalClv.toLocaleString()} with average CLV of £${averageClv.toLocaleString()}`;
             document.getElementById('clvInsight2').textContent = `${bestSegment} customers generate the highest value, while ${worstSegment} need attention`;
             document.getElementById('clvInsight3').textContent = `ROI potential of £${roiPotential.toLocaleString()} with ${highValueCustomers} high-value customers identified`;
         }
         
         // Update CLV breakdown table
         function updateClvBreakdownTable(segments, totalClv) {
             const tableBody = document.getElementById('clvBreakdownTable');
             if (!tableBody) return;
             
             const html = Object.keys(segments).map(segment => {
                 const segmentData = segments[segment];
                 const percentageOfTotal = totalClv > 0 ? ((segmentData.totalClv / totalClv) * 100).toFixed(1) : 0;
                 
                 let priority = 'Low';
                 if (segmentData.avgClv > 8000) priority = 'Critical';
                 else if (segmentData.avgClv > 4000) priority = 'High';
                 else if (segmentData.avgClv > 1500) priority = 'Medium';
                 
                 let priorityColor = 'text-green-600 dark:text-green-400';
                 if (priority === 'Critical') priorityColor = 'text-red-600 dark:text-red-400';
                 else if (priority === 'High') priorityColor = 'text-orange-600 dark:text-orange-400';
                 else if (priority === 'Medium') priorityColor = 'text-yellow-600 dark:text-yellow-400';
                 
                 return `
                     <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                         <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${segment}</td>
                         <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">£${segmentData.avgClv.toLocaleString()}</td>
                         <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${segmentData.customers.length}</td>
                         <td class="px-4 py-3 text-sm text-purple-600 dark:text-purple-400">£${segmentData.totalClv.toLocaleString()}</td>
                         <td class="px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400">${percentageOfTotal}%</td>
                         <td class="px-4 py-3 text-sm ${priorityColor}">${priority}</td>
                     </tr>
                 `;
             }).join('');
             
             tableBody.innerHTML = html;
         }
         
         // Control functions for Customer Lifetime Value
         function updateClvAnalysis() {
             const methodSelect = document.getElementById('clvMethodSelect');
             const timeHorizonSelect = document.getElementById('clvTimeHorizon');
             const discountRateSelect = document.getElementById('clvDiscountRate');
             
             clvMethod = methodSelect.value;
             clvTimeHorizon = timeHorizonSelect.value;
             clvDiscountRate = discountRateSelect.value;
             
             if (customerLifetimeValueChart) {
                 updateCustomerLifetimeValueChart();
             }
         }
         


        // Sample data for other charts (placeholder implementations)
        const sampleLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

        function initializeCustomerValueChart() {
            const ctx = document.getElementById('customerValueChart');
            if (ctx && !ctx.chart) {
                ctx.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: sampleLabels,
                        datasets: [{
                            label: 'Customer Lifetime Value',
                            data: [500, 750, 600, 900, 800, 1100],
                            borderColor: '#F59E0B',
                            backgroundColor: '#F59E0B20',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        }
                    }
                });
            }
        }
        function initializeSegmentationChart() {
            const ctx = document.getElementById('segmentationChart');
            if (ctx && !ctx.chart) {
                ctx.chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Champions', 'Loyal Customers', 'At Risk', "Can't Lose", 'Lost'],
                        datasets: [{
                            data: [25, 30, 20, 15, 10],
                            backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#6B7280']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }
        }

        function initializeChurnRetentionChart() {
            const ctx = document.getElementById('churnRetentionChart');
            if (ctx && !ctx.chart) {
                ctx.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: sampleLabels,
                        datasets: [
                            {
                                label: 'Retention Rate',
                                data: [85, 88, 82, 90, 87, 92],
                                borderColor: '#10B981',
                                backgroundColor: '#10B98120',
                                tension: 0.1
                            },
                            {
                                label: 'Churn Rate',
                                data: [15, 12, 18, 10, 13, 8],
                                borderColor: '#EF4444',
                                backgroundColor: '#EF444420',
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        }

        // Initialize overview tab by default
        document.addEventListener('DOMContentLoaded', function() {
            showTab('overview');
            
            // Load top companies data when RFM breakdown tab is shown
            loadTopCompaniesDataMain();
        });

        // Load top companies data for the main RFM breakdown tab
        function loadTopCompaniesDataMain() {
            fetch('/rfm/analysis/top-companies', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                updateTopCompaniesDisplayMain(data);
            })
            .catch(error => {
                console.error('Error loading top companies data:', error);
                showTopCompaniesErrorMain();
            });
        }

        function updateTopCompaniesDisplayMain(data) {
            // Update Most Recent Companies
            updateCompanyListMain('topRecentCompaniesMain', data.recent || [], 'r_score');
            
            // Update Most Frequent Companies
            updateCompanyListMain('topFrequentCompaniesMain', data.frequent || [], 'f_score');
            
            // Update Highest Monetary Companies
            updateCompanyListMain('topMonetaryCompaniesMain', data.monetary || [], 'm_score');
        }

        function updateCompanyListMain(containerId, companies, scoreType) {
            const container = document.getElementById(containerId);
            if (!container) return;

            if (companies.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm">No data available</p>
                    </div>
                `;
                return;
            }

            const scoreColors = {
                'r_score': 'text-red-600 dark:text-red-400',
                'f_score': 'text-green-600 dark:text-green-400',
                'm_score': 'text-yellow-600 dark:text-yellow-400'
            };

            const html = companies.map((company, index) => `
                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-600 rounded-lg shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-gray-200 dark:bg-gray-500 rounded-full flex items-center justify-center">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">${index + 1}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate max-w-32">${company.name}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Last: ${company.last_activity}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold ${scoreColors[scoreType]}">${company.score}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">RFM: ${company.rfm_score}</p>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;
        }

        function showTopCompaniesErrorMain() {
            const containers = ['topRecentCompaniesMain', 'topFrequentCompaniesMain', 'topMonetaryCompaniesMain'];
            containers.forEach(containerId => {
                const container = document.getElementById(containerId);
                if (container) {
                    container.innerHTML = `
                        <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                            <svg class="w-8 h-8 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">Failed to load data</p>
                        </div>
                    `;
                }
            });
        }
        
        // Update Customer Segmentation Chart
        function updateCustomerSegmentationChart() {
            // Process RFM data
            const rfmData = @json($hasData ? $rfmData : []);
            
            if (rfmData.length === 0) {
                // Show no data message
                const ctx = document.getElementById('customerSegmentationChart');
                ctx.style.display = 'none';
                const container = ctx.parentElement;
                container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
                return;
            }
            
            // Calculate time period
            const monthsBack = parseInt(segmentationTimePeriod.replace('m', ''));
            const cutoffDate = new Date();
            cutoffDate.setMonth(cutoffDate.getMonth() - monthsBack);
            
            // Group data by customer and analyze segmentation
            const customerData = {};
            
            rfmData.forEach(record => {
                const clientId = record.client_id;
                const recordDate = new Date(record.date);
                
                if (!customerData[clientId]) {
                    customerData[clientId] = {
                        name: record.client_name,
                        records: [],
                        firstSeen: recordDate,
                        lastSeen: recordDate,
                        avgRfmScore: 0,
                        avgRScore: 0,
                        avgFScore: 0,
                        avgMScore: 0,
                        totalValue: 0,
                        purchaseFrequency: 0,
                        segment: 'unknown'
                    };
                }
                
                customerData[clientId].records.push({
                    date: recordDate,
                    rfmScore: parseFloat(record.rfm_score) || 0,
                    rScore: parseFloat(record.r_score) || 0,
                    fScore: parseFloat(record.f_score) || 0,
                    mScore: parseFloat(record.m_score) || 0
                });
                
                if (recordDate < customerData[clientId].firstSeen) {
                    customerData[clientId].firstSeen = recordDate;
                }
                if (recordDate > customerData[clientId].lastSeen) {
                    customerData[clientId].lastSeen = recordDate;
                }
            });
            
            // Calculate metrics for each customer
            Object.values(customerData).forEach(customer => {
                const avgRfmScore = customer.records.reduce((sum, r) => sum + r.rfmScore, 0) / customer.records.length;
                const avgRScore = customer.records.reduce((sum, r) => sum + r.rScore, 0) / customer.records.length;
                const avgFScore = customer.records.reduce((sum, r) => sum + r.fScore, 0) / customer.records.length;
                const avgMScore = customer.records.reduce((sum, r) => sum + r.mScore, 0) / customer.records.length;
                
                customer.avgRfmScore = avgRfmScore;
                customer.avgRScore = avgRScore;
                customer.avgFScore = avgFScore;
                customer.avgMScore = avgMScore;
                
                // Calculate purchase frequency
                const daysSinceFirst = (customer.lastSeen - customer.firstSeen) / (1000 * 60 * 60 * 24);
                const yearsSinceFirst = daysSinceFirst / 365;
                customer.purchaseFrequency = yearsSinceFirst > 0 ? customer.records.length / yearsSinceFirst : customer.records.length;
                
                // Calculate total value
                customer.totalValue = customer.records.reduce((sum, r) => sum + (r.mScore * 1000), 0);
                
                // Determine segment based on method
                switch(segmentationMethod) {
                    case 'rfm-score':
                        if (avgRfmScore >= 8) customer.segment = 'Champions';
                        else if (avgRfmScore >= 6) customer.segment = 'Loyal Customers';
                        else if (avgRfmScore >= 4) customer.segment = 'At Risk';
                        else if (avgRfmScore >= 2) customer.segment = "Can't Lose";
                        else customer.segment = 'Lost';
                        break;
                        
                    case 'behavior-pattern':
                        if (avgFScore >= 4 && avgRScore >= 4) customer.segment = 'High Frequency & Recent';
                        else if (avgFScore >= 4 && avgRScore < 4) customer.segment = 'High Frequency & Old';
                        else if (avgFScore < 4 && avgRScore >= 4) customer.segment = 'Low Frequency & Recent';
                        else customer.segment = 'Low Frequency & Old';
                        break;
                        
                    case 'value-tier':
                        if (avgMScore >= 8) customer.segment = 'Premium';
                        else if (avgMScore >= 5) customer.segment = 'High Value';
                        else if (avgMScore >= 3) customer.segment = 'Medium Value';
                        else customer.segment = 'Low Value';
                        break;
                        
                    case 'engagement-level':
                        const daysSinceLastActivity = (new Date() - customer.lastSeen) / (1000 * 60 * 60 * 24);
                        if (daysSinceLastActivity <= 30 && customer.purchaseFrequency >= 12) customer.segment = 'Highly Engaged';
                        else if (daysSinceLastActivity <= 90 && customer.purchaseFrequency >= 6) customer.segment = 'Engaged';
                        else if (daysSinceLastActivity <= 180) customer.segment = 'Moderately Engaged';
                        else customer.segment = 'Disengaged';
                        break;
                }
            });
            
            // Group customers by segments
            const segments = {};
            Object.values(customerData).forEach(customer => {
                if (!segments[customer.segment]) {
                    segments[customer.segment] = {
                        customers: [],
                        count: 0,
                        avgRfmScore: 0,
                        avgValue: 0,
                        totalValue: 0
                    };
                }
                
                segments[customer.segment].customers.push(customer);
                segments[customer.segment].count++;
                segments[customer.segment].totalValue += customer.totalValue;
            });
            
            // Calculate averages for each segment
            Object.keys(segments).forEach(segment => {
                const segmentData = segments[segment];
                segmentData.avgRfmScore = segmentData.customers.reduce((sum, c) => sum + c.avgRfmScore, 0) / segmentData.count;
                segmentData.avgValue = segmentData.totalValue / segmentData.count;
            });
            
            // Create chart data
            const chartLabels = Object.keys(segments);
            const chartValues = chartLabels.map(segment => segments[segment].count);
            const chartColors = [
                'rgba(34, 197, 94, 0.8)',   // Green - Champions
                'rgba(59, 130, 246, 0.8)',  // Blue - Loyal
                'rgba(245, 158, 11, 0.8)',  // Orange - At Risk
                'rgba(239, 68, 68, 0.8)',   // Red - Can't Lose
                'rgba(107, 114, 128, 0.8)', // Gray - Lost
                'rgba(147, 51, 234, 0.8)',  // Purple - Premium
                'rgba(16, 185, 129, 0.8)',  // Emerald - High Value
                'rgba(251, 191, 36, 0.8)',  // Yellow - Medium Value
                'rgba(156, 163, 175, 0.8)'  // Gray - Low Value
            ];
            
            // Create chart
            const chartConfig = {
                labels: chartLabels,
                datasets: [{
                    label: 'Customers',
                    data: chartValues,
                    backgroundColor: chartColors.slice(0, chartLabels.length),
                    borderColor: chartColors.slice(0, chartLabels.length).map(color => color.replace('0.8', '1')),
                    borderWidth: 2
                }]
            };
            
            // Update or create chart
            const ctx = document.getElementById('customerSegmentationChart');
            if (ctx) {
                ctx.style.display = 'block';
                
                if (customerSegmentationChart) {
                    customerSegmentationChart.destroy();
                }
                
                customerSegmentationChart = new Chart(ctx, {
                    type: segmentationChartType,
                    data: chartConfig,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: document.documentElement.classList.contains('dark') ? '#374151' : '#FFFFFF',
                                titleColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                bodyColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                borderColor: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        const segment = context.label;
                                        const count = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((count / total) * 100).toFixed(1);
                                        return `${segment}: ${count} customers (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Update statistics
            const totalCustomers = Object.keys(customerData).length;
            const totalSegments = Object.keys(segments).length;
            const largestSegment = Object.keys(segments).reduce((max, segment) => 
                segments[segment].count > segments[max].count ? segment : max
            );
            const highestValueSegment = Object.keys(segments).reduce((max, segment) => 
                segments[segment].avgValue > segments[max].avgValue ? segment : max
            );
            const segmentationScore = ((totalSegments / 5) * 100).toFixed(1); // Score based on segment diversity
            
            const mostActiveSegment = Object.keys(segments).reduce((max, segment) => {
                const avgFrequency = segments[segment].customers.reduce((sum, c) => sum + c.purchaseFrequency, 0) / segments[segment].count;
                const maxFrequency = segments[max] ? segments[max].customers.reduce((sum, c) => sum + c.purchaseFrequency, 0) / segments[max].count : 0;
                return avgFrequency > maxFrequency ? segment : max;
            });
            
            const avgRfmScore = Object.values(customerData).reduce((sum, c) => sum + c.avgRfmScore, 0) / totalCustomers;
            const segmentDiversity = ((totalSegments / 8) * 100).toFixed(1); // Diversity score
            
            const premiumCustomers = Object.values(customerData).filter(c => c.avgRfmScore >= 8).length;
            const atRiskCustomers = Object.values(customerData).filter(c => c.avgRfmScore >= 4 && c.avgRfmScore < 6).length;
            const newCustomers = Object.values(customerData).filter(c => {
                const monthsSinceFirst = (new Date() - c.firstSeen) / (1000 * 60 * 60 * 24 * 30);
                return monthsSinceFirst <= 3;
            }).length;
            const loyalCustomers = Object.values(customerData).filter(c => c.avgRfmScore >= 6).length;
            
            document.getElementById('totalSegments').textContent = totalSegments;
            document.getElementById('largestSegment').textContent = largestSegment;
            document.getElementById('highestValueSegment').textContent = highestValueSegment;
            document.getElementById('segmentationScore').textContent = segmentationScore + '/100';
            
            document.getElementById('mostActiveSegment').textContent = mostActiveSegment;
            document.getElementById('avgRfmScoreSegmentation').textContent = avgRfmScore.toFixed(1);
            document.getElementById('segmentDiversity').textContent = segmentDiversity + '%';
            document.getElementById('totalCustomersSegmentation').textContent = totalCustomers;
            
            document.getElementById('premiumCustomersSegmentation').textContent = premiumCustomers;
            document.getElementById('atRiskCustomersSegmentation').textContent = atRiskCustomers;
            document.getElementById('newCustomersSegmentation').textContent = newCustomers;
            document.getElementById('loyalCustomersSegmentation').textContent = loyalCustomers;
            
            // Update breakdown table
            updateSegmentBreakdownTable(segments, totalCustomers);
            
            // Update insights
            document.getElementById('segmentationInsight1').textContent = `Identified ${totalSegments} distinct customer segments with ${largestSegment} being the largest (${segments[largestSegment].count} customers)`;
            document.getElementById('segmentationInsight2').textContent = `${highestValueSegment} customers generate the highest average value (£${segments[highestValueSegment].avgValue.toLocaleString()})`;
            document.getElementById('segmentationInsight3').textContent = `Segmentation quality score of ${segmentationScore}/100 with ${premiumCustomers} premium customers identified`;
        }
        
        // Update segment breakdown table
        function updateSegmentBreakdownTable(segments, totalCustomers) {
            const tableBody = document.getElementById('segmentBreakdownTable');
            if (!tableBody) return;
            
            const html = Object.keys(segments).map(segment => {
                const segmentData = segments[segment];
                const percentageOfTotal = ((segmentData.count / totalCustomers) * 100).toFixed(1);
                
                let status = 'Good';
                let statusColor = 'text-green-600 dark:text-green-400';
                
                if (segmentData.avgRfmScore >= 7) {
                    status = 'Excellent';
                    statusColor = 'text-emerald-600 dark:text-emerald-400';
                } else if (segmentData.avgRfmScore >= 5) {
                    status = 'Good';
                    statusColor = 'text-blue-600 dark:text-blue-400';
                } else if (segmentData.avgRfmScore >= 3) {
                    status = 'Fair';
                    statusColor = 'text-yellow-600 dark:text-yellow-400';
                } else {
                    status = 'Poor';
                    statusColor = 'text-red-600 dark:text-red-400';
                }
                
                return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${segment}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${segmentData.count}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${percentageOfTotal}%</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${segmentData.avgRfmScore.toFixed(1)}</td>
                        <td class="px-4 py-3 text-sm text-purple-600 dark:text-purple-400">£${segmentData.avgValue.toLocaleString()}</td>
                        <td class="px-4 py-3 text-sm ${statusColor}">${status}</td>
                    </tr>
                `;
            }).join('');
            
            tableBody.innerHTML = html;
        }
        
        // Control functions for Customer Segmentation
        function updateSegmentationAnalysis() {
            const methodSelect = document.getElementById('segmentationMethodSelect');
            const timePeriodSelect = document.getElementById('segmentationTimePeriod');
            const chartTypeSelect = document.getElementById('segmentationChartType');
            
            segmentationMethod = methodSelect.value;
            segmentationTimePeriod = timePeriodSelect.value;
            segmentationChartType = chartTypeSelect.value;
            
            if (customerSegmentationChart) {
                updateCustomerSegmentationChart();
            }
        }
        // Update Customer Value Distribution Chart
        function updateCustomerValueDistributionChart() {
            // Process RFM data
            const rfmData = @json($hasData ? $rfmData : []);
            
            if (rfmData.length === 0) {
                // Show no data message
                const ctx = document.getElementById('customerValueDistributionChart');
                ctx.style.display = 'none';
                const container = ctx.parentElement;
                container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
                return;
            }
            
            // Calculate time period
            const monthsBack = valueDistributionPeriod === 'all' ? 999 : parseInt(valueDistributionPeriod.replace('m', ''));
            const cutoffDate = new Date();
            cutoffDate.setMonth(cutoffDate.getMonth() - monthsBack);
            
            // Group data by customer and calculate values
            const customerData = {};
            
            rfmData.forEach(record => {
                const clientId = record.client_id;
                const recordDate = new Date(record.date);
                
                if (recordDate < cutoffDate) return; // Skip old data
                
                if (!customerData[clientId]) {
                    customerData[clientId] = {
                        name: record.client_name,
                        values: [],
                        avgValue: 0
                    };
                }
                
                let value = 0;
                switch(valueDistributionMethod) {
                    case 'rfm-score':
                        value = parseFloat(record.rfm_score) || 0;
                        break;
                    case 'monetary-value':
                        value = parseFloat(record.m_score) || 0;
                        break;
                    case 'frequency':
                        value = parseFloat(record.f_score) || 0;
                        break;
                    case 'recency':
                        value = parseFloat(record.r_score) || 0;
                        break;
                }
                
                customerData[clientId].values.push(value);
            });
            
            // Calculate average values for each customer
            Object.values(customerData).forEach(customer => {
                customer.avgValue = customer.values.reduce((sum, v) => sum + v, 0) / customer.values.length;
            });
            
            // Create distribution data
            const values = Object.values(customerData).map(c => c.avgValue).filter(v => v > 0);
            
            if (values.length === 0) {
                // Show no data message
                const ctx = document.getElementById('customerValueDistributionChart');
                ctx.style.display = 'none';
                const container = ctx.parentElement;
                container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No valid data available</div>';
                return;
            }
            
            // Calculate statistics
            const mean = values.reduce((sum, v) => sum + v, 0) / values.length;
            const sortedValues = values.sort((a, b) => a - b);
            const median = sortedValues[Math.floor(sortedValues.length / 2)];
            const min = Math.min(...values);
            const max = Math.max(...values);
            const range = max - min;
            
            // Calculate percentiles
            const top20Index = Math.floor(values.length * 0.8);
            const top20Value = sortedValues[top20Index];
            
            // Calculate standard deviation
            const variance = values.reduce((sum, v) => sum + Math.pow(v - mean, 2), 0) / values.length;
            const stdDev = Math.sqrt(variance);
            
            // Calculate skewness
            const skewness = values.reduce((sum, v) => sum + Math.pow((v - mean) / stdDev, 3), 0) / values.length;
            
            // Detect outliers (using IQR method)
            const q1Index = Math.floor(values.length * 0.25);
            const q3Index = Math.floor(values.length * 0.75);
            const q1 = sortedValues[q1Index];
            const q3 = sortedValues[q3Index];
            const iqr = q3 - q1;
            const lowerBound = q1 - 1.5 * iqr;
            const upperBound = q3 + 1.5 * iqr;
            const outliers = values.filter(v => v < lowerBound || v > upperBound);
            
            // Calculate Gini coefficient
            const giniCoefficient = calculateGiniCoefficient(values);
            
            // Create chart data based on type
            let chartData = {};
            let chartType = 'bar';
            
            switch(valueDistributionType) {
                case 'histogram':
                    chartData = createHistogramData(values, 10);
                    chartType = 'bar';
                    break;
                case 'boxplot':
                    chartData = createBoxPlotData(values);
                    chartType = 'bar';
                    break;
                case 'percentile':
                    chartData = createPercentileData(values);
                    chartType = 'line';
                    break;
            }
            
            // Create or update chart
            const ctx = document.getElementById('customerValueDistributionChart');
            ctx.style.display = 'block';
            
            if (customerValueDistributionChart) {
                customerValueDistributionChart.destroy();
            }
            
            customerValueDistributionChart = new Chart(ctx, {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: getChartTitle(),
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 20
                            }
                        },
                        legend: {
                            display: chartType === 'bar' && valueDistributionType === 'boxplot',
                            position: 'top',
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: document.documentElement.classList.contains('dark') ? '#374151' : '#FFFFFF',
                            titleColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                            bodyColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                            borderColor: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const value = context.parsed.y || context.parsed;
                                    const methodLabel = getMethodLabel();
                                    
                                    if (valueDistributionType === 'histogram') {
                                        return `${context.parsed} customers in this range`;
                                    } else if (valueDistributionType === 'boxplot') {
                                        return `${label}: ${value.toFixed(2)}`;
                                    } else {
                                        return `${methodLabel}: ${value.toFixed(2)}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: getYAxisLabel(),
                                color: '#FFFFFF',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                padding: {
                                    top: 10,
                                    bottom: 10
                                }
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB',
                                drawBorder: false,
                                lineWidth: 0.5
                            },
                            ticks: {
                                color: '#FFFFFF',
                                font: {
                                    size: 12
                                },
                                padding: 8,
                                callback: function(value, index, values) {
                                    if (valueDistributionType === 'histogram') {
                                        return value + ' customers';
                                    }
                                    return value;
                                }
                            },
                            border: {
                                color: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                                width: 1
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: getXAxisLabel(),
                                color: '#FFFFFF',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                padding: {
                                    top: 10,
                                    bottom: 10
                                }
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB',
                                drawBorder: false,
                                lineWidth: 0.5
                            },
                            ticks: {
                                color: '#FFFFFF',
                                font: {
                                    size: 11
                                },
                                padding: 8,
                                maxRotation: 45,
                                minRotation: 0,
                                callback: function(value, index, values) {
                                    if (valueDistributionType === 'histogram') {
                                        // Shorten long labels
                                        const label = this.getLabelForValue(value);
                                        if (label.length > 8) {
                                            return label.split('-')[0] + '-';
                                        }
                                        return label;
                                    }
                                    return value;
                                }
                            },
                            border: {
                                color: document.documentElement.classList.contains('dark') ? '#6B7280' : '#E5E7EB',
                                width: 1
                            }
                        }
                    },
                    elements: {
                        bar: {
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        point: {
                            radius: 4,
                            hoverRadius: 6,
                            borderWidth: 2
                        },
                        line: {
                            tension: 0.2
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    }
                }
            });
            
            // Update statistics
            document.getElementById('meanValue').textContent = mean.toFixed(2);
            document.getElementById('medianValue').textContent = median.toFixed(2);
            document.getElementById('top20Percent').textContent = top20Value.toFixed(2);
            document.getElementById('valueRange').textContent = `${min.toFixed(2)} - ${max.toFixed(2)}`;
            
            document.getElementById('distributionShape').textContent = Math.abs(skewness) < 0.5 ? 'Normal' : skewness > 0 ? 'Right-skewed' : 'Left-skewed';
            document.getElementById('distributionSkewness').textContent = skewness.toFixed(3);
            document.getElementById('standardDeviation').textContent = stdDev.toFixed(2);
            document.getElementById('outlierCount').textContent = outliers.length;
            document.getElementById('giniCoefficient').textContent = giniCoefficient.toFixed(3);
            document.getElementById('valueConcentration').textContent = ((top20Value / mean) * 100).toFixed(1) + '%';
            
            // Update insights
            const methodLabel = valueDistributionMethod.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
            document.getElementById('valueInsight1').textContent = `Analyzed ${values.length} customers using ${methodLabel} distribution`;
            document.getElementById('valueInsight2').textContent = `Found ${outliers.length} outliers and ${giniCoefficient.toFixed(3)} Gini coefficient indicating value concentration`;
            document.getElementById('valueInsight3').textContent = `Distribution is ${Math.abs(skewness) < 0.5 ? 'relatively normal' : skewness > 0 ? 'right-skewed' : 'left-skewed'} with ${stdDev.toFixed(2)} standard deviation`;
        }
        

        
        // Helper functions for value distribution
        function createHistogramData(values, bins = 10) {
            const min = Math.min(...values);
            const max = Math.max(...values);
            const binSize = (max - min) / bins;
            
            const histogram = new Array(bins).fill(0);
            const labels = [];
            
            for (let i = 0; i < bins; i++) {
                const binStart = min + i * binSize;
                const binEnd = min + (i + 1) * binSize;
                
                // Create more readable labels
                let label;
                if (binSize <= 1) {
                    label = `${binStart.toFixed(1)}-${binEnd.toFixed(1)}`;
                } else if (binSize <= 2) {
                    label = `${binStart.toFixed(1)}-${binEnd.toFixed(1)}`;
                } else {
                    label = `${Math.round(binStart)}-${Math.round(binEnd)}`;
                }
                
                labels.push(label);
                
                values.forEach(value => {
                    if (value >= binStart && value < binEnd) {
                        histogram[i]++;
                    }
                });
            }
            
            // Create gradient colors based on value
            const colors = histogram.map(count => {
                const maxCount = Math.max(...histogram);
                const intensity = count / maxCount;
                return `rgba(59, 130, 246, ${0.3 + intensity * 0.4})`;
            });
            
            const borderColors = histogram.map(count => {
                const maxCount = Math.max(...histogram);
                const intensity = count / maxCount;
                return `rgba(59, 130, 246, ${0.6 + intensity * 0.4})`;
            });
            
            return {
                labels: labels,
                datasets: [{
                    label: 'Customer Count',
                    data: histogram,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            };
        }
        
        function createBoxPlotData(values) {
            const sorted = values.sort((a, b) => a - b);
            const q1Index = Math.floor(values.length * 0.25);
            const q3Index = Math.floor(values.length * 0.75);
            const medianIndex = Math.floor(values.length * 0.5);
            
            const min = sorted[0];
            const q1 = sorted[q1Index];
            const median = sorted[medianIndex];
            const q3 = sorted[q3Index];
            const max = sorted[sorted.length - 1];
            
            return {
                labels: ['Value Distribution'],
                datasets: [{
                    label: 'Min',
                    data: [min],
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }, {
                    label: 'Q1 (25th Percentile)',
                    data: [q1],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }, {
                    label: 'Median (50th Percentile)',
                    data: [median],
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                    borderColor: 'rgba(168, 85, 247, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }, {
                    label: 'Q3 (75th Percentile)',
                    data: [q3],
                    backgroundColor: 'rgba(236, 72, 153, 0.8)',
                    borderColor: 'rgba(236, 72, 153, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }, {
                    label: 'Max',
                    data: [max],
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            };
        }
        
        function createPercentileData(values) {
            const sorted = values.sort((a, b) => a - b);
            const percentiles = [10, 25, 50, 75, 90, 95, 99];
            const labels = percentiles.map(p => `${p}th`);
            const data = percentiles.map(p => {
                const index = Math.floor((p / 100) * sorted.length);
                return sorted[index];
            });
            
            return {
                labels: labels,
                datasets: [{
                    label: 'Percentile Value',
                    data: data,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            };
        }
        
        function calculateGiniCoefficient(values) {
            const sorted = values.sort((a, b) => a - b);
            const n = sorted.length;
            let sum = 0;
            
            for (let i = 0; i < n; i++) {
                sum += (2 * (i + 1) - n - 1) * sorted[i];
            }
            
            return sum / (n * n * (sorted.reduce((a, b) => a + b, 0) / n));
        }
        
        // Helper functions for chart labels and titles
        function getChartTitle() {
            const methodLabel = getMethodLabel();
            const typeLabel = getTypeLabel();
            const periodLabel = getPeriodLabel();
            return `${methodLabel} Distribution - ${typeLabel} (${periodLabel})`;
        }
        
        function getMethodLabel() {
            switch(valueDistributionMethod) {
                case 'rfm-score': return 'RFM Score';
                case 'monetary-value': return 'Monetary Value';
                case 'frequency': return 'Purchase Frequency';
                case 'recency': return 'Recency Score';
                default: return 'Value';
            }
        }
        function getTypeLabel() {
            switch(valueDistributionType) {
                case 'histogram': return 'Histogram';
                case 'boxplot': return 'Box Plot';
                case 'percentile': return 'Percentile Analysis';
                default: return 'Distribution';
            }
        }
        
        function getPeriodLabel() {
            switch(valueDistributionPeriod) {
                case '3m': return 'Last 3 Months';
                case '6m': return 'Last 6 Months';
                case '12m': return 'Last 12 Months';
                case 'all': return 'All Time';
                default: return 'Recent';
            }
        }
        
        function getXAxisLabel() {
            switch(valueDistributionMethod) {
                case 'rfm-score': return 'RFM Score Range';
                case 'monetary-value': return 'Monetary Value Range';
                case 'frequency': return 'Purchase Frequency Range';
                case 'recency': return 'Recency Score Range';
                default: return 'Value Range';
            }
        }
        
        function getYAxisLabel() {
            switch(valueDistributionType) {
                case 'histogram': return 'Number of Customers';
                case 'boxplot': return 'Value';
                case 'percentile': return 'Value';
                default: return 'Count';
            }
        }
        
        // Control functions for Customer Value Distribution
        function updateValueDistribution() {
            const methodSelect = document.getElementById('valueDistributionMethod');
            const typeSelect = document.getElementById('valueDistributionType');
            const periodSelect = document.getElementById('valueDistributionPeriod');
            
            valueDistributionMethod = methodSelect.value;
            valueDistributionType = typeSelect.value;
            valueDistributionPeriod = periodSelect.value;
            
            if (customerValueDistributionChart) {
                updateCustomerValueDistributionChart();
            }
        }
         


     </script>

    

    <script>
        // Remove any leftover calls cleanly
        
        // Tour removed: no initialization
        
        // Global function to restart tour (disabled)
        function restartTour() {
            if (window.onboardingTour) {
                localStorage.removeItem('rfm_tour_completed');
                window.onboardingTour.startTour();
            }
        }
        
        // Development function to test tour
        function testTour() {
            if (window.onboardingTour) {
                localStorage.removeItem('rfm_tour_completed');
                window.onboardingTour.startTour();
            }
        }
        
        // Quick Actions Functions for Customer Value Distribution
        function exportChartData() {
            if (!customerValueDistributionChart) {
                alert('No chart data available to export');
                return;
            }
            
            const chartData = customerValueDistributionChart.data;
            const method = valueDistributionMethod;
            const type = valueDistributionType;
            const period = valueDistributionPeriod;
            
            const exportData = {
                chartType: 'Customer Value Distribution',
                method: method,
                type: type,
                period: period,
                datasets: chartData.datasets,
                labels: chartData.labels,
                timestamp: new Date().toISOString()
            };
            
            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `customer-value-distribution-${method}-${type}-${period}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            // Show success notification
            showNotification('Data exported successfully!', 'success');
        }
        
        function resetChartView() {
            // Reset to default values
            valueDistributionMethod = 'rfm-score';
            valueDistributionType = 'histogram';
            valueDistributionPeriod = '6m';
            
            // Update selectors
            document.getElementById('valueDistributionMethod').value = valueDistributionMethod;
            document.getElementById('valueDistributionType').value = valueDistributionType;
            document.getElementById('valueDistributionPeriod').value = valueDistributionPeriod;
            
            // Update chart
            updateCustomerValueDistributionChart();
            
            showNotification('Chart view reset to defaults', 'info');
        }
        
        function toggleFullscreen() {
            const chartContainer = document.querySelector('#tab-content-customer-value-distribution .chart-container');
            if (!chartContainer) return;
            
            if (!document.fullscreenElement) {
                chartContainer.requestFullscreen().then(() => {
                    showNotification('Entered fullscreen mode', 'info');
                }).catch(err => {
                    showNotification('Fullscreen not supported', 'error');
                });
            } else {
                document.exitFullscreen().then(() => {
                    showNotification('Exited fullscreen mode', 'info');
                });
            }
        }
        
        function shareAnalysis() {
            const method = valueDistributionMethod;
            const type = valueDistributionType;
            const period = valueDistributionPeriod;
            
            const shareText = `Customer Value Distribution Analysis\nMethod: ${method}\nType: ${type}\nPeriod: ${period}\n\nView the full analysis at: ${window.location.href}`;
            
            if (navigator.share) {
                navigator.share({
                    title: 'Customer Value Distribution Analysis',
                    text: shareText,
                    url: window.location.href
                }).then(() => {
                    showNotification('Analysis shared successfully!', 'success');
                }).catch(err => {
                    showNotification('Share cancelled', 'info');
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(shareText).then(() => {
                    showNotification('Analysis link copied to clipboard!', 'success');
                }).catch(err => {
                    showNotification('Failed to copy to clipboard', 'error');
                });
            }
        }
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    </script>

    <!-- Custom Features Enquiry Modal -->
    <div id="customFeaturesModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Custom Charts Enquiry</h3>
                    <button onclick="closeCustomFeaturesModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="customFeaturesForm" class="space-y-4">
                    <div>
                        <label for="enquiryEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Email:</label>
                        <input type="email" id="enquiryEmail" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="your@email.com">
                    </div>
                    
                    <div>
                        <label for="enquiryMessage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom Charts Request:</label>
                        <textarea id="enquiryMessage" name="message" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                  placeholder="Tell us about the custom charts and analysis you need for your business..."></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" onclick="closeCustomFeaturesModal()" 
                                class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                            Send Enquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCustomFeaturesModal() {
            document.getElementById('customFeaturesModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCustomFeaturesModal() {
            document.getElementById('customFeaturesModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('customFeaturesModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCustomFeaturesModal();
            }
        });

        // Handle form submission
        document.getElementById('customFeaturesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('enquiryEmail').value;
            const message = document.getElementById('enquiryMessage').value;
            
            // Here you would typically send this to your backend
            console.log('Custom Charts Enquiry:', { email, message });
            
            // Show success message
            alert('Thank you for your enquiry! We\'ll get back to you soon.');
            
            // Close modal and reset form
            closeCustomFeaturesModal();
            this.reset();
        });
    </script>
</x-app-layout>