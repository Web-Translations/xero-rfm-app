{{-- resources/views/rfm/analysis/components.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Components Analysis</h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">RFM Components Analysis</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Detailed breakdown of Recency, Frequency, and Monetary scores over time</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">
                                <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full">RFM Components View</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <a href="{{ route('rfm.analysis.index') }}" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Overview
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#company-trends" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Company Trends
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#rfm-components" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            RFM Components
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#revenue-analysis" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Revenue Analysis
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#customer-value" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Customer Value
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#segmentation" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Segmentation
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#churn-retention" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Churn & Retention
                        </a>
                        <a href="{{ route('rfm.analysis.business') }}" 
                           class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Business Analytics
                        </a>
                        <span class="py-4 px-1 border-b-2 border-purple-500 font-medium text-sm text-purple-600 dark:text-purple-400">
                            RFM Components
                        </span>
                    </nav>
                </div>
            </div>

            <!-- RFM Components Content -->
            <div class="space-y-6">
                <!-- RFM Component Breakdown Trend Chart -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Component Breakdown Trends</h3>
                        <div class="flex items-center space-x-4">
                            <select id="rfmTrendPeriod" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="6">Last 6 Months</option>
                                <option value="12" selected>Last 12 Months</option>
                                <option value="18">Last 18 Months</option>
                                <option value="24">Last 24 Months</option>
                            </select>
                            <button id="refreshRfmTrend" class="text-sm bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded-md">
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div class="chart-container" style="height: 400px; position: relative;">
                        <canvas id="rfmComponentTrendChart"></canvas>
                    </div>
                    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        <p class="mb-2"><strong>Chart Explanation:</strong></p>
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="text-red-500 font-medium">R Score (Recency):</span> How recently customers made purchases - higher scores indicate more recent activity</li>
                            <li><span class="text-green-500 font-medium">F Score (Frequency):</span> How often customers make purchases - higher scores indicate more frequent activity</li>
                            <li><span class="text-yellow-500 font-medium">M Score (Monetary):</span> How much customers spend - higher scores indicate larger purchase values</li>
                        </ul>
                    </div>
                </div>

                <!-- Top Companies by RFM Component -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Top Companies by RFM Component</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Most Recent Companies -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Most Recent (R Score)</h4>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div id="topRecentCompanies" class="space-y-3">
                                    <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-500 mx-auto mb-2"></div>
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Most Frequent Companies -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Most Frequent (F Score)</h4>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div id="topFrequentCompanies" class="space-y-3">
                                    <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500 mx-auto mb-2"></div>
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Highest Monetary Companies -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Highest Value (M Score)</h4>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div id="topMonetaryCompanies" class="space-y-3">
                                    <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-yellow-500 mx-auto mb-2"></div>
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RFM Component Insights -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">RFM Component Insights</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-red-600 dark:text-red-400 font-semibold">R</span>
                                </div>
                                <h4 class="text-md font-semibold text-red-800 dark:text-red-200">Recency Insights</h4>
                            </div>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                Track how recently your customers have made purchases. High recency scores indicate active engagement, while declining scores may signal customers at risk of churning.
                            </p>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-green-600 dark:text-green-400 font-semibold">F</span>
                                </div>
                                <h4 class="text-md font-semibold text-green-800 dark:text-green-200">Frequency Insights</h4>
                            </div>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                Monitor how often customers make purchases. High frequency indicates strong customer loyalty and engagement with your products or services.
                            </p>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-yellow-600 dark:text-yellow-400 font-semibold">M</span>
                                </div>
                                <h4 class="text-md font-semibold text-yellow-800 dark:text-yellow-200">Monetary Insights</h4>
                            </div>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Analyze customer spending patterns. High monetary scores show customers who contribute significantly to your revenue and may be candidates for premium services.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let rfmComponentTrendChart = null;

        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeRfmComponentTrendChart();
            loadTopCompaniesData();

            // Event listeners for RFM trend controls
            const refreshBtn = document.getElementById('refreshRfmTrend');
            const periodSelect = document.getElementById('rfmTrendPeriod');
            
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    loadRfmComponentTrendData();
                });
            }
            
            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
                    loadRfmComponentTrendData();
                });
            }
        });

        function initializeRfmComponentTrendChart() {
            const ctx = document.getElementById('rfmComponentTrendChart');
            if (!ctx) return;

            // Destroy existing chart if it exists
            if (rfmComponentTrendChart) {
                rfmComponentTrendChart.destroy();
            }

            // Sample data - this will be replaced with real data from the backend
            const sampleData = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'R Score (Recency)',
                        data: [7.2, 7.8, 6.9, 8.1, 7.5, 8.3],
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: 'F Score (Frequency)',
                        data: [6.8, 7.1, 6.5, 7.9, 7.2, 8.0],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: 'M Score (Monetary)',
                        data: [6.5, 7.3, 6.2, 7.7, 6.9, 7.8],
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4
                    }
                ]
            };

            rfmComponentTrendChart = new Chart(ctx, {
                type: 'line',
                data: sampleData,
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
                            beginAtZero: true,
                            max: 10,
                            title: {
                                display: true,
                                text: 'RFM Score (0-10)',
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

        function loadRfmComponentTrendData() {
            const period = document.getElementById('rfmTrendPeriod')?.value || 12;
            
            // Show loading state
            const refreshBtn = document.getElementById('refreshRfmTrend');
            if (refreshBtn) {
                refreshBtn.textContent = 'Loading...';
                refreshBtn.disabled = true;
            }

            // Fetch data from backend API
            fetch(`/rfm/analysis/component-trends?months_back=${period}`, {
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
                // Update chart with real data
                updateRfmComponentTrendChart(data);
                
                // Reset button
                if (refreshBtn) {
                    refreshBtn.textContent = 'Refresh';
                    refreshBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error loading RFM component trend data:', error);
                
                // Reset button
                if (refreshBtn) {
                    refreshBtn.textContent = 'Refresh';
                    refreshBtn.disabled = false;
                }
                
                // Show error message to user
                alert('Failed to load RFM component trend data. Please try again.');
            });
        }

        function updateRfmComponentTrendChart(data) {
            const ctx = document.getElementById('rfmComponentTrendChart');
            if (!ctx) return;

            // Destroy existing chart if it exists
            if (rfmComponentTrendChart) {
                rfmComponentTrendChart.destroy();
            }

            rfmComponentTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: data.datasets || []
                },
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
                            beginAtZero: true,
                            max: 10,
                            title: {
                                display: true,
                                text: 'RFM Score (0-10)',
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

            // Display summary information if available
            if (data.summary) {
                console.log('RFM Component Trend Summary:', data.summary);
            }
        }

        function loadTopCompaniesData() {
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
                updateTopCompaniesDisplay(data);
            })
            .catch(error => {
                console.error('Error loading top companies data:', error);
                showTopCompaniesError();
            });
        }

        function updateTopCompaniesDisplay(data) {
            // Update Most Recent Companies
            updateCompanyList('topRecentCompanies', data.recent || [], 'r_score');
            
            // Update Most Frequent Companies
            updateCompanyList('topFrequentCompanies', data.frequent || [], 'f_score');
            
            // Update Highest Monetary Companies
            updateCompanyList('topMonetaryCompanies', data.monetary || [], 'm_score');
        }

        function updateCompanyList(containerId, companies, scoreType) {
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

            const scoreLabels = {
                'r_score': 'R Score',
                'f_score': 'F Score', 
                'm_score': 'M Score'
            };

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

        function showTopCompaniesError() {
            const containers = ['topRecentCompanies', 'topFrequentCompanies', 'topMonetaryCompanies'];
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
