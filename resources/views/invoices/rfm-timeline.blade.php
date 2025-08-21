<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Invoice RFM Timeline</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        <!-- Overview -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score Timeline by Invoice Date</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Track how RFM scores change over time based on individual invoice dates
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filters</h3>
            </div>
            <div class="p-6">
                <form id="timelineFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="months_back" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time Period</label>
                        <select id="months_back" name="months_back" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <option value="6">Last 6 months</option>
                            <option value="12" selected>Last 12 months</option>
                            <option value="24">Last 24 months</option>
                            <option value="36">Last 36 months</option>
                        </select>
                    </div>
                    <div>
                        <label for="client_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client (Optional)</label>
                        <select id="client_filter" name="client_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <option value="">All Clients</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                            Update Chart
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RFM Timeline Chart -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score Progression</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    X-axis: Invoice Date | Y-axis: RFM Score (0-10)
                </p>
            </div>
            <div class="p-6">
                <canvas id="rfmTimelineChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- RFM Components Chart -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Components Breakdown</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Individual R, F, and M scores over time
                </p>
            </div>
            <div class="p-6">
                <canvas id="rfmComponentsChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invoices</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="totalInvoices">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg RFM Score</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="avgRfmScore">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="totalRevenue">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Clients</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="activeClients">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let rfmTimelineChart, rfmComponentsChart;

        // Load clients for filter dropdown
        async function loadClients() {
            try {
                const response = await fetch('/invoices/rfm-timeline?months_back=12');
                const data = await response.json();
                
                // Extract unique clients from timeline data
                const clients = new Set();
                data.timeline_data.forEach(item => {
                    item.clients.forEach(client => clients.add(client));
                });
                
                const clientSelect = document.getElementById('client_filter');
                clientSelect.innerHTML = '<option value="">All Clients</option>';
                
                Array.from(clients).sort().forEach(client => {
                    const option = document.createElement('option');
                    option.value = client;
                    option.textContent = client;
                    clientSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading clients:', error);
            }
        }

        // Load and display RFM timeline data
        async function loadRfmTimeline() {
            const form = document.getElementById('timelineFilters');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            try {
                const response = await fetch(`/invoices/rfm-timeline?${params}`);
                const data = await response.json();
                
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                
                updateCharts(data.timeline_data);
                updateSummaryStats(data);
                
            } catch (error) {
                console.error('Error loading RFM timeline:', error);
            }
        }

        // Update charts with new data
        function updateCharts(timelineData) {
            const dates = timelineData.map(item => new Date(item.date).toLocaleDateString());
            const rfmScores = timelineData.map(item => item.avg_rfm_score);
            const rScores = timelineData.map(item => item.avg_r_score);
            const fScores = timelineData.map(item => item.avg_f_score);
            const mScores = timelineData.map(item => item.avg_m_score);
            
            // Destroy existing charts
            if (rfmTimelineChart) rfmTimelineChart.destroy();
            if (rfmComponentsChart) rfmComponentsChart.destroy();
            
            // Create RFM Timeline Chart
            const timelineCtx = document.getElementById('rfmTimelineChart').getContext('2d');
            rfmTimelineChart = new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'RFM Score',
                        data: rfmScores,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Invoice Date',
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'RFM Score',
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            },
                            beginAtZero: true,
                            max: 10,
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
            
            // Create RFM Components Chart
            const componentsCtx = document.getElementById('rfmComponentsChart').getContext('2d');
            rfmComponentsChart = new Chart(componentsCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'R Score (Recency)',
                            data: rScores,
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: 'F Score (Frequency)',
                            data: fScores,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: 'M Score (Monetary)',
                            data: mScores,
                            borderColor: '#F59E0B',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Invoice Date',
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Score',
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                            },
                            beginAtZero: true,
                            max: 10,
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

        // Update summary statistics
        function updateSummaryStats(data) {
            const timelineData = data.timeline_data;
            
            if (timelineData.length > 0) {
                const totalInvoices = timelineData.reduce((sum, item) => sum + item.invoice_count, 0);
                const avgRfmScore = timelineData.reduce((sum, item) => sum + item.avg_rfm_score, 0) / timelineData.length;
                const totalRevenue = timelineData.reduce((sum, item) => sum + item.total_revenue, 0);
                const uniqueClients = new Set(timelineData.flatMap(item => item.clients)).size;
                
                document.getElementById('totalInvoices').textContent = totalInvoices.toLocaleString();
                document.getElementById('avgRfmScore').textContent = avgRfmScore.toFixed(2);
                document.getElementById('totalRevenue').textContent = '$' + totalRevenue.toLocaleString();
                document.getElementById('activeClients').textContent = uniqueClients;
            }
        }

        // Event listeners
        document.getElementById('timelineFilters').addEventListener('submit', function(e) {
            e.preventDefault();
            loadRfmTimeline();
        });

        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            loadClients();
            loadRfmTimeline();
        });
    </script>
    @endpush
</x-app-layout>

