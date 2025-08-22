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
        });
    </script>
</x-app-layout>
