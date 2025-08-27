<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Analysis Dashboard</h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">RFM Analysis</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive customer behavior analysis and insights</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">
                                <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button onclick="showTab('overview')" id="tab-overview" 
                                class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 dark:text-blue-400">
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
                        <button onclick="showTab('customer-value-distribution')" id="tab-customer-value-distribution" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Value Distribution
                        </button>
                        <button onclick="showTab('rfm-monthly-distribution')" id="tab-rfm-monthly-distribution" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            RFM Monthly Distribution
                        </button>
                        <button onclick="showTab('customer-value-distribution')" id="tab-customer-value-distribution" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Value Distribution
                        </button>
                        <button onclick="showTab('rfm-score-over-time')" id="tab-rfm-score-over-time" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            RFM Score Over Time
                        </button>

                        <button onclick="showTab('customer-value')" id="tab-customer-value" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Value
                        </button>
                        <button onclick="showTab('segmentation')" id="tab-segmentation" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Segmentation
                        </button>
                        <button onclick="showTab('churn-retention')" id="tab-churn-retention" 
                                class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Churn & Retention
                        </button>
                        <a href="{{ route('rfm.analysis.business') }}" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Business Analytics
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="space-y-6">
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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Summary Cards -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Data Summary</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Total Data Points:</span>
                                    <span class="font-semibold">{{ $hasData ? $rfmData->count() : 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Unique Clients:</span>
                                    <span class="font-semibold">{{ $hasData ? $allClients->count() : 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Date Range:</span>
                                    <span class="font-semibold">{{ $hasData ? $allDates->first() . ' - ' . $allDates->last() : 'No data' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Average RFM Score:</span>
                                    <span class="font-semibold">{{ $hasData ? round($rfmData->avg('rfm_score'), 2) : 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <button onclick="showTab('client-trends')" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors">
                                    View Client RFM Trends
                                </button>
                                <button onclick="showTab('rfm-breakdown')" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition-colors">
                                    Analyze RFM Breakdown
                                </button>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client RFM Trends Tab -->
                <div id="tab-content-client-trends" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Client RFM Trends</h3>
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
                             <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Overall RFM Breakdown</h3>
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

                <!-- Customer Value Distribution Tab -->
                <div id="tab-content-customer-value-distribution" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Value Distribution</h3>
                            <div class="flex space-x-4">
                                <!-- Date Range Controls -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Start Date:</label>
                                    <input type="date" id="valueDistStartDate" onchange="updateValueDistDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->first() : '' }}">
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">End Date:</label>
                                    <input type="date" id="valueDistEndDate" onchange="updateValueDistDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->last() : '' }}">
                                </div>
                                
                                <!-- Quick Preset Buttons -->
                                <div class="flex items-center space-x-1">
                                    <button onclick="setValueDistDateRange('6m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        6M
                                    </button>
                                    <button onclick="setValueDistDateRange('12m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        12M
                                    </button>
                                    <button onclick="setValueDistDateRange('24m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        24M
                                    </button>
                                    <button onclick="setValueDistDateRange('all')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        All
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Insights Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Distribution Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="customerValueDistChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Value Distribution Insights -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-2">High-Value Customers</h4>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="highValueCount">-</div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">RFM Score: 8-10</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-green-800 dark:text-green-200 mb-2">Medium-Value Customers</h4>
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="mediumValueCount">-</div>
                                    <p class="text-sm text-green-700 dark:text-green-300">RFM Score: 5-7</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Low-Value Customers</h4>
                                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" id="lowValueCount">-</div>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">RFM Score: 1-4</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-purple-800 dark:text-purple-200 mb-2">Total Customers</h4>
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" id="totalCustomers">-</div>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">Active in period</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Customer Value Insights -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Customer Value Insights</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">High-Value %:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="highValuePercent">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Medium-Value %:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400" id="mediumValuePercent">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Low-Value %:</span>
                                        <span class="font-semibold text-yellow-600 dark:text-yellow-400" id="lowValuePercent">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Avg RFM Score:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" id="avgRfmScore">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Top 20% Value:</span>
                                        <span class="font-semibold text-purple-600 dark:text-purple-400" id="top20Percent">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Growth Trend:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400" id="growthTrend">-</span>
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score Distribution by Month</h3>
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

                <!-- Customer Value Distribution Tab -->
                <div id="tab-content-customer-value-distribution" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Revenue Distribution</h3>
                            <div class="flex space-x-4">
                                <!-- Date Range Controls -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Start Date:</label>
                                    <input type="date" id="valueHistogramStartDate" onchange="updateValueHistogramDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->first() : '' }}">
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">End Date:</label>
                                    <input type="date" id="valueHistogramEndDate" onchange="updateValueHistogramDateRange()" 
                                           class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                           min="{{ $hasData ? $allDates->first() : '' }}" 
                                           max="{{ $hasData ? $allDates->last() : '' }}"
                                           value="{{ $hasData ? $allDates->last() : '' }}">
                                </div>
                                
                                <!-- Quick Preset Buttons -->
                                <div class="flex items-center space-x-1">
                                    <button onclick="setValueHistogramDateRange('6m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        6M
                                    </button>
                                    <button onclick="setValueHistogramDateRange('12m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        12M
                                    </button>
                                    <button onclick="setValueHistogramDateRange('24m')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        24M
                                    </button>
                                    <button onclick="setValueHistogramDateRange('all')" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                        All
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart and Stats Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Revenue Histogram Chart -->
                            <div class="lg:col-span-2">
                                <div class="chart-container" style="height: 400px; position: relative;">
                                    <canvas id="customerValueDistributionChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Revenue Statistics -->
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-emerald-800 dark:text-emerald-200 mb-2">Total Revenue</h4>
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="totalRevenue">-</div>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">All customers</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-2">Top 20% Revenue</h4>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="top20Revenue">-</div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">High-value customers</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-orange-800 dark:text-orange-200 mb-2">Avg Customer Value</h4>
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400" id="avgCustomerValue">-</div>
                                    <p class="text-sm text-orange-700 dark:text-orange-300">Per customer</p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-purple-800 dark:text-purple-200 mb-2">Revenue Concentration</h4>
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" id="revenueConcentration">-</div>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">Top 20% share</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Revenue Analysis -->
                        <div class="mt-6 bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Revenue Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Median Revenue:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400" id="medianRevenue">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Revenue Range:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400" id="revenueRange">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Top Customer:</span>
                                        <span class="font-semibold text-yellow-600 dark:text-yellow-400" id="topCustomerRevenue">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Bottom Customer:</span>
                                        <span class="font-semibold text-purple-600 dark:text-purple-400" id="bottomCustomerRevenue">-</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Customers > £1K:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100" id="customersOver1k">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Customers > £5K:</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="customersOver5k">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Customers > £10K:</span>
                                        <span class="font-semibold text-indigo-600 dark:text-indigo-400" id="customersOver10k">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Customers:</span>
                                        <span class="font-semibold text-rose-600 dark:text-rose-400" id="totalCustomersValue">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Revenue Tiers Breakdown -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Revenue Tier Breakdown</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">£10K+ (VIP)</h5>
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400" id="tier10kPlus">-</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" id="tier10kPlusPercent">-</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">£5K-£10K (Premium)</h5>
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400" id="tier5kTo10k">-</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" id="tier5kTo10kPercent">-</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">£1K-£5K (Regular)</h5>
                                    <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400" id="tier1kTo5k">-</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" id="tier1kTo5kPercent">-</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">< £1K (Small)</h5>
                                    <div class="text-lg font-bold text-red-600 dark:text-red-400" id="tierUnder1k">-</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" id="tierUnder1kPercent">-</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Business Insights -->
                        <div class="mt-6 bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-emerald-900 dark:text-emerald-100 mb-3">Business Insights</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-start space-x-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">•</span>
                                    <span class="text-gray-700 dark:text-gray-300" id="businessInsight1">Analyzing revenue distribution patterns...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">•</span>
                                    <span class="text-gray-700 dark:text-gray-300" id="businessInsight2">Identifying high-value customer segments...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">•</span>
                                    <span class="text-gray-700 dark:text-gray-300" id="businessInsight3">Calculating revenue concentration metrics...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RFM Score Over Time Tab -->
                <div id="tab-content-rfm-score-over-time" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score Distribution Over Time</h3>
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
                                    <span class="text-gray-700 dark:text-gray-300" id="timeInsight1">Analyzing RFM score trends over time...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold">•</span>
                                    <span class="text-gray-700 dark:text-gray-300" id="timeInsight2">Identifying seasonal patterns and trends...</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold">•</span>
                                    <span class="text-gray-700 dark:text-gray-300" id="timeInsight3">Calculating performance consistency metrics...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Value Tab -->
                <div id="tab-content-customer-value" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Customer Lifetime Value</h3>
                        <div class="chart-container" style="height: 400px; position: relative;">
                            <canvas id="customerValueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Segmentation Tab -->
                <div id="tab-content-segmentation" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Customer Segmentation</h3>
                        <div class="chart-container" style="height: 400px; position: relative;">
                            <canvas id="segmentationChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Churn & Retention Tab -->
                <div id="tab-content-churn-retention" class="tab-content" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Churn & Retention Analysis</h3>
                        <div class="chart-container" style="height: 400px; position: relative;">
                            <canvas id="churnRetentionChart"></canvas>
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
         
         // Customer Value Distribution chart variables
         let customerValueDistChart = null;
         let valueDistStartDate = allDates.length > 0 ? allDates[0] : '';
         let valueDistEndDate = allDates.length > 0 ? allDates[allDates.length - 1] : '';
         
         // RFM Monthly Distribution chart variables
         let rfmMonthlyDistChart = null;
         let monthlyDistStartDate = allDates.length > 0 ? allDates[0] : '';
         let monthlyDistEndDate = allDates.length > 0 ? allDates[allDates.length - 1] : '';
         
         // Customer Value Distribution chart variables
         let customerValueDistributionChart = null;
         let valueHistogramStartDate = allDates.length > 0 ? allDates[0] : '';
         let valueHistogramEndDate = allDates.length > 0 ? allDates[allDates.length - 1] : '';
         
         // RFM Score Over Time chart variables
         let rfmScoreOverTimeChart = null;
         let timePeriodType = 'monthly';
         let scoreRangeFilter = 'all';
         


        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            
            // Show selected tab content
            document.getElementById('tab-content-' + tabName).style.display = 'block';
            
            // Add active class to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            if (activeTab) {
                activeTab.classList.add('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
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
                                text: 'RFM Score'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
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
                     <span class="text-sm text-gray-700 dark:text-gray-300">${client.name}</span>
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
                            class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer flex-1">
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
                case 'customer-value-distribution':
                    initializeCustomerValueDistributionChart();
                    break;
                case 'rfm-monthly-distribution':
                    initializeRfmMonthlyDistributionChart();
                    break;
                case 'customer-value-distribution':
                    initializeCustomerValueDistributionChart();
                    break;
                case 'rfm-score-over-time':
                    initializeRfmScoreOverTimeChart();
                    break;
                case 'customer-value':
                    initializeCustomerValueChart();
                    break;
                case 'segmentation':
                    initializeSegmentationChart();
                    break;
                case 'churn-retention':
                    initializeChurnRetentionChart();
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
         
         // Initialize Customer Value Distribution Chart
         function initializeCustomerValueDistributionChart() {
             if (allDates.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerValueDistChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No RFM data available</div>';
                 return;
             }
             
             updateCustomerValueDistributionChart();
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
                                 text: 'RFM Score'
                             }
                         },
                         x: {
                             title: {
                                 display: true,
                                 text: 'Date'
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
         
         // Update Customer Value Distribution Chart with filtered data
         function updateCustomerValueDistributionChart() {
             const filteredDates = filterDatesByRange(allDates, valueDistStartDate, valueDistEndDate);
             
             // Process RFM data for the filtered date range
             const rfmData = @json($hasData ? $rfmData : []);
             
             // Filter data by date range
             const filteredData = rfmData.filter(record => {
                 const recordDate = new Date(record.date);
                 const startDate = new Date(valueDistStartDate);
                 const endDate = new Date(valueDistEndDate);
                 return recordDate >= startDate && recordDate <= endDate;
             });
             
             if (filteredData.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerValueDistChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available for selected date range</div>';
                 return;
             }
             
             // Calculate value distribution
             const rfmScores = filteredData.map(record => record.rfm_score).filter(score => score > 0);
             
             // Define value categories
             const highValue = rfmScores.filter(score => score >= 8).length;
             const mediumValue = rfmScores.filter(score => score >= 5 && score < 8).length;
             const lowValue = rfmScores.filter(score => score >= 1 && score < 5).length;
             
             const totalCustomers = rfmScores.length;
             const avgRfmScore = totalCustomers > 0 ? (rfmScores.reduce((a, b) => a + b, 0) / totalCustomers).toFixed(1) : 0;
             
             // Update insights
             document.getElementById('highValueCount').textContent = highValue;
             document.getElementById('mediumValueCount').textContent = mediumValue;
             document.getElementById('lowValueCount').textContent = lowValue;
             document.getElementById('totalCustomers').textContent = totalCustomers;
             
             document.getElementById('highValuePercent').textContent = totalCustomers > 0 ? ((highValue / totalCustomers) * 100).toFixed(1) + '%' : '0%';
             document.getElementById('mediumValuePercent').textContent = totalCustomers > 0 ? ((mediumValue / totalCustomers) * 100).toFixed(1) + '%' : '0%';
             document.getElementById('lowValuePercent').textContent = totalCustomers > 0 ? ((lowValue / totalCustomers) * 100).toFixed(1) + '%' : '0%';
             
             document.getElementById('avgRfmScore').textContent = avgRfmScore;
             document.getElementById('top20Percent').textContent = totalCustomers > 0 ? Math.ceil(totalCustomers * 0.2) : 0;
             
             // Calculate growth trend (simplified - could be enhanced with historical comparison)
             const growthTrend = highValue > mediumValue ? 'Positive' : 'Needs Attention';
             document.getElementById('growthTrend').textContent = growthTrend;
             
             // Create chart data
             const chartData = {
                 labels: ['High Value (8-10)', 'Medium Value (5-7)', 'Low Value (1-4)'],
                 datasets: [{
                     data: [highValue, mediumValue, lowValue],
                     backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                     borderColor: ['#2563EB', '#059669', '#D97706'],
                     borderWidth: 2
                 }]
             };
             
             // Update or create chart
             const ctx = document.getElementById('customerValueDistChart');
             if (ctx) {
                 ctx.style.display = 'block';
                 
                 if (customerValueDistChart) {
                     customerValueDistChart.destroy();
                 }
                 
                 customerValueDistChart = new Chart(ctx, {
                     type: 'doughnut',
                     data: chartData,
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: {
                                 position: 'bottom',
                                 labels: {
                                     padding: 20,
                                     usePointStyle: true,
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
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
                                         const label = context.label || '';
                                         const value = context.parsed;
                                         const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                         const percentage = ((value / total) * 100).toFixed(1);
                                         return `${label}: ${value} (${percentage}%)`;
                                     }
                                 }
                             }
                         }
                     }
                 });
             }
         }
         
         // Date range functions for Customer Value Distribution
         function updateValueDistDateRange() {
             const startInput = document.getElementById('valueDistStartDate');
             const endInput = document.getElementById('valueDistEndDate');
             
             valueDistStartDate = startInput.value;
             valueDistEndDate = endInput.value;
             
             if (customerValueDistChart) {
                 updateCustomerValueDistributionChart();
             }
         }
         
         function setValueDistDateRange(preset) {
             const startInput = document.getElementById('valueDistStartDate');
             const endInput = document.getElementById('valueDistEndDate');
             
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
             
             valueDistStartDate = startInput.value;
             valueDistEndDate = endInput.value;
             
             if (customerValueDistChart) {
                 updateCustomerValueDistributionChart();
             }
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
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 ticks: {
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
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
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 ticks: {
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 grid: {
                                     drawOnChartArea: false
                                 }
                             },
                             x: {
                                 title: {
                                     display: true,
                                     text: 'Month',
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 ticks: {
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
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
         function updateCustomerValueDistributionChart() {
             const filteredDates = filterDatesByRange(allDates, valueHistogramStartDate, valueHistogramEndDate);
             
             // Process RFM data for the filtered date range
             const rfmData = @json($hasData ? $rfmData : []);
             
             // Filter data by date range
             const filteredData = rfmData.filter(record => {
                 const recordDate = new Date(record.date);
                 const startDate = new Date(valueHistogramStartDate);
                 const endDate = new Date(valueHistogramEndDate);
                 return recordDate >= startDate && recordDate <= endDate;
             });
             
             if (filteredData.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerValueDistributionChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available for selected date range</div>';
                 return;
             }
             
             // Get latest data for each customer and calculate revenue
             const customerData = {};
             filteredData.forEach(record => {
                 const clientId = record.client_id;
                 if (!customerData[clientId] || new Date(record.date) > new Date(customerData[clientId].date)) {
                     // Calculate estimated revenue based on M score (monetary value)
                     // M score of 10 = £50,000+, M score of 1 = £0-£100
                     const mScore = parseFloat(record.m_score) || 0;
                     let estimatedRevenue = 0;
                     
                     if (mScore >= 9) estimatedRevenue = 50000 + (mScore - 9) * 10000; // £50K+
                     else if (mScore >= 8) estimatedRevenue = 25000 + (mScore - 8) * 25000; // £25K-£50K
                     else if (mScore >= 7) estimatedRevenue = 10000 + (mScore - 7) * 15000; // £10K-£25K
                     else if (mScore >= 6) estimatedRevenue = 5000 + (mScore - 6) * 5000; // £5K-£10K
                     else if (mScore >= 5) estimatedRevenue = 2500 + (mScore - 5) * 2500; // £2.5K-£5K
                     else if (mScore >= 4) estimatedRevenue = 1000 + (mScore - 4) * 1500; // £1K-£2.5K
                     else if (mScore >= 3) estimatedRevenue = 500 + (mScore - 3) * 500; // £500-£1K
                     else if (mScore >= 2) estimatedRevenue = 200 + (mScore - 2) * 300; // £200-£500
                     else if (mScore >= 1) estimatedRevenue = 50 + (mScore - 1) * 150; // £50-£200
                     else estimatedRevenue = Math.random() * 50; // £0-£50
                     
                     customerData[clientId] = {
                         revenue: estimatedRevenue,
                         m_score: mScore,
                         date: record.date,
                         client_name: record.client_name
                     };
                 }
             });
             
             const allRevenues = Object.values(customerData).map(c => c.revenue).filter(revenue => revenue > 0);
             
             if (allRevenues.length === 0) {
                 // Show no data message
                 const ctx = document.getElementById('customerValueDistributionChart');
                 ctx.style.display = 'none';
                 const container = ctx.parentElement;
                 container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No valid revenue data found</div>';
                 return;
             }
             
             // Create revenue bins for histogram
             const revenueBins = {
                 '£0-£500': 0,
                 '£500-£1K': 0,
                 '£1K-£2.5K': 0,
                 '£2.5K-£5K': 0,
                 '£5K-£10K': 0,
                 '£10K-£25K': 0,
                 '£25K-£50K': 0,
                 '£50K+': 0
             };
             
             allRevenues.forEach(revenue => {
                 if (revenue < 500) revenueBins['£0-£500']++;
                 else if (revenue < 1000) revenueBins['£500-£1K']++;
                 else if (revenue < 2500) revenueBins['£1K-£2.5K']++;
                 else if (revenue < 5000) revenueBins['£2.5K-£5K']++;
                 else if (revenue < 10000) revenueBins['£5K-£10K']++;
                 else if (revenue < 25000) revenueBins['£10K-£25K']++;
                 else if (revenue < 50000) revenueBins['£25K-£50K']++;
                 else revenueBins['£50K+']++;
             });
             
             // Calculate statistics
             const totalRevenue = allRevenues.reduce((a, b) => a + b, 0);
             const avgRevenue = totalRevenue / allRevenues.length;
             const sortedRevenues = [...allRevenues].sort((a, b) => a - b);
             const medianRevenue = sortedRevenues.length % 2 === 0 
                 ? (sortedRevenues[sortedRevenues.length / 2 - 1] + sortedRevenues[sortedRevenues.length / 2]) / 2
                 : sortedRevenues[Math.floor(sortedRevenues.length / 2)];
             
             const maxRevenue = Math.max(...allRevenues);
             const minRevenue = Math.min(...allRevenues);
             const revenueRange = maxRevenue - minRevenue;
             
             // Calculate top 20% revenue
             const top20Count = Math.ceil(allRevenues.length * 0.2);
             const top20Revenues = sortedRevenues.slice(-top20Count);
             const top20Revenue = top20Revenues.reduce((a, b) => a + b, 0);
             const revenueConcentration = (top20Revenue / totalRevenue * 100).toFixed(1);
             
             // Update statistics
             document.getElementById('totalRevenue').textContent = '£' + totalRevenue.toLocaleString();
             document.getElementById('top20Revenue').textContent = '£' + top20Revenue.toLocaleString();
             document.getElementById('avgCustomerValue').textContent = '£' + avgRevenue.toLocaleString(undefined, {maximumFractionDigits: 0});
             document.getElementById('revenueConcentration').textContent = revenueConcentration + '%';
             
             document.getElementById('medianRevenue').textContent = '£' + medianRevenue.toLocaleString(undefined, {maximumFractionDigits: 0});
             document.getElementById('revenueRange').textContent = '£' + revenueRange.toLocaleString(undefined, {maximumFractionDigits: 0});
             document.getElementById('topCustomerRevenue').textContent = '£' + maxRevenue.toLocaleString(undefined, {maximumFractionDigits: 0});
             document.getElementById('bottomCustomerRevenue').textContent = '£' + minRevenue.toLocaleString(undefined, {maximumFractionDigits: 0});
             
             // Update customer counts by revenue thresholds
             const customersOver1k = allRevenues.filter(r => r > 1000).length;
             const customersOver5k = allRevenues.filter(r => r > 5000).length;
             const customersOver10k = allRevenues.filter(r => r > 10000).length;
             
             document.getElementById('customersOver1k').textContent = customersOver1k;
             document.getElementById('customersOver5k').textContent = customersOver5k;
             document.getElementById('customersOver10k').textContent = customersOver10k;
             document.getElementById('totalCustomersValue').textContent = allRevenues.length;
             
             // Update revenue tier breakdowns
             const tier10kPlus = allRevenues.filter(r => r >= 10000).length;
             const tier5kTo10k = allRevenues.filter(r => r >= 5000 && r < 10000).length;
             const tier1kTo5k = allRevenues.filter(r => r >= 1000 && r < 5000).length;
             const tierUnder1k = allRevenues.filter(r => r < 1000).length;
             
             document.getElementById('tier10kPlus').textContent = tier10kPlus;
             document.getElementById('tier5kTo10k').textContent = tier5kTo10k;
             document.getElementById('tier1kTo5k').textContent = tier1kTo5k;
             document.getElementById('tierUnder1k').textContent = tierUnder1k;
             
             document.getElementById('tier10kPlusPercent').textContent = allRevenues.length > 0 ? ((tier10kPlus / allRevenues.length) * 100).toFixed(1) + '%' : '0%';
             document.getElementById('tier5kTo10kPercent').textContent = allRevenues.length > 0 ? ((tier5kTo10k / allRevenues.length) * 100).toFixed(1) + '%' : '0%';
             document.getElementById('tier1kTo5kPercent').textContent = allRevenues.length > 0 ? ((tier1kTo5k / allRevenues.length) * 100).toFixed(1) + '%' : '0%';
             document.getElementById('tierUnder1kPercent').textContent = allRevenues.length > 0 ? ((tierUnder1k / allRevenues.length) * 100).toFixed(1) + '%' : '0%';
             
             // Update insights
             document.getElementById('businessInsight1').textContent = `Total revenue of £${totalRevenue.toLocaleString()} from ${allRevenues.length} customers`;
             document.getElementById('businessInsight2').textContent = `Top 20% of customers generate ${revenueConcentration}% of total revenue`;
             document.getElementById('businessInsight3').textContent = `Average customer value is £${avgRevenue.toLocaleString(undefined, {maximumFractionDigits: 0})} with ${customersOver10k} VIP customers`;
             
             // Create histogram chart data
             const chartData = {
                 labels: Object.keys(revenueBins),
                 datasets: [{
                     label: 'Number of Customers',
                     data: Object.values(revenueBins),
                     backgroundColor: 'rgba(16, 185, 129, 0.8)',
                     borderColor: '#10B981',
                     borderWidth: 2,
                     borderRadius: 4,
                     borderSkipped: false
                 }]
             };
             
             // Update or create chart
             const ctx = document.getElementById('customerValueDistributionChart');
             if (ctx) {
                 ctx.style.display = 'block';
                 
                 if (customerValueDistributionChart) {
                     customerValueDistributionChart.destroy();
                 }
                 
                 customerValueDistributionChart = new Chart(ctx, {
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
                                         const total = allRevenues.length;
                                         const percentage = ((value / total) * 100).toFixed(1);
                                         return `${value} customers (${percentage}%)`;
                                     }
                                 }
                             }
                         },
                         scales: {
                             y: {
                                 beginAtZero: true,
                                 title: {
                                     display: true,
                                     text: 'Number of Customers',
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 ticks: {
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 grid: {
                                     color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                 }
                             },
                             x: {
                                 title: {
                                     display: true,
                                     text: 'Revenue Range',
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 ticks: {
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 grid: {
                                     color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                 }
                             }
                         }
                     }
                 });
             }
         }
         
         // Date range functions for Customer Value Distribution
         function updateValueHistogramDateRange() {
             const startInput = document.getElementById('valueHistogramStartDate');
             const endInput = document.getElementById('valueHistogramEndDate');
             
             valueHistogramStartDate = startInput.value;
             valueHistogramEndDate = endInput.value;
             
             if (customerValueDistributionChart) {
                 updateCustomerValueDistributionChart();
             }
         }
         
         function setValueHistogramDateRange(preset) {
             const startInput = document.getElementById('valueHistogramStartDate');
             const endInput = document.getElementById('valueHistogramEndDate');
             
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
             
             valueHistogramStartDate = startInput.value;
             valueHistogramEndDate = endInput.value;
             
             if (customerValueDistributionChart) {
                 updateCustomerValueDistributionChart();
             }
         }
         
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
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 ticks: {
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 grid: {
                                     color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                                 }
                             },
                             x: {
                                 title: {
                                     display: true,
                                     text: 'Time Period',
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                                 },
                                 ticks: {
                                     color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
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
    </script>
</x-app-layout>
