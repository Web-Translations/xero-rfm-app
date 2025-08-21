<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Analysis</h2>
    </x-slot>

    <div class="p-6">
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Predictive Analysis</h3>
            </div>

            <div class="p-6">
                {{-- Tabs --}}
                <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                    <nav class="-mb-px flex gap-6 overflow-x-auto">
                        <a href="{{ route('rfm.analysis.index') }}" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Company Trends
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#rfm-breakdown" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            RFM Component Breakdown
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#revenue-trend" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Revenue Trend Analysis
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#clv-trend" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Customer Lifetime Value
                        </a>
                        <a href="{{ route('rfm.analysis.segments') }}" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Customer Segmentation
                        </a>
                        <a href="{{ route('rfm.analysis.index') }}#churn-analysis" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Churn & Retention
                        </a>
                        <a href="{{ route('rfm.analysis.business') }}" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Business Analytics
                        </a>
                    </nav>
                </div>

                {{-- Predictive Analysis Content --}}
                <div class="space-y-6">
        <!-- Predictive Analysis Overview -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Predictive Analytics</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Forecast future client behavior and churn risk based on RFM patterns
                </p>
            </div>
        </div>

        <!-- Churn Risk Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Current Churn Rate -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Churn Rate</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $predictiveData['predicted_churn']['current_rate'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>

            <!-- Predicted Churn Rate -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Predicted Churn</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $predictiveData['predicted_churn']['predicted_rate'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>

            <!-- Trend Direction -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 {{ ($predictiveData['predicted_churn']['trend'] ?? '') == 'increasing' ? 'bg-red-100 dark:bg-red-900' : 'bg-green-100 dark:bg-green-900' }} rounded-lg">
                        @if(($predictiveData['predicted_churn']['trend'] ?? '') == 'increasing')
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Trend</p>
                        <p class="text-2xl font-semibold {{ ($predictiveData['predicted_churn']['trend'] ?? '') == 'increasing' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ ucfirst($predictiveData['predicted_churn']['trend'] ?? 'stable') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Churn Risk Trend Chart -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Churn Risk Trends</h3>
            </div>
            <div class="p-6">
                <canvas id="churnRiskChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- RFM Score vs Churn Risk -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Scatter Plot -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score vs Churn Risk</h3>
                </div>
                <div class="p-6">
                    <canvas id="scatterChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Risk Distribution -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Risk Level Distribution</h3>
                </div>
                <div class="p-6">
                    <canvas id="riskDistributionChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Risk Analysis Details -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Risk Analysis Details</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- High Risk -->
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">High Risk</h4>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-3">
                            Clients with RFM scores below 3.0 and declining patterns.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-red-600 dark:text-red-400">Risk Level:</span>
                                <span class="font-semibold">Critical</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-red-600 dark:text-red-400">Action:</span>
                                <span class="font-semibold">Immediate</span>
                            </div>
                        </div>
                    </div>

                    <!-- Medium Risk -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Medium Risk</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
                            Clients with RFM scores between 3.0-5.9 showing warning signs.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600 dark:text-yellow-400">Risk Level:</span>
                                <span class="font-semibold">Moderate</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600 dark:text-yellow-400">Action:</span>
                                <span class="font-semibold">Monitor</span>
                            </div>
                        </div>
                    </div>

                    <!-- Low Risk -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">Low Risk</h4>
                        <p class="text-sm text-green-700 dark:text-green-300 mb-3">
                            Clients with RFM scores above 6.0 and stable patterns.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">Risk Level:</span>
                                <span class="font-semibold">Safe</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">Action:</span>
                                <span class="font-semibold">Maintain</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Predictive Insights -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Predictive Insights</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Key Risk Factors</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span><strong>Low Recency:</strong> Clients who haven't purchased recently</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span><strong>Declining Frequency:</strong> Reduced purchase frequency over time</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span><strong>Decreasing Monetary:</strong> Lower average order values</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span><strong>Seasonal Patterns:</strong> Clients with seasonal purchase behavior</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Recommended Actions</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start">
                                <span class="text-blue-500 mr-2">•</span>
                                <span><strong>High Risk:</strong> Personalized re-engagement campaigns</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 mr-2">•</span>
                                <span><strong>Medium Risk:</strong> Loyalty programs and incentives</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 mr-2">•</span>
                                <span><strong>Low Risk:</strong> Upselling and cross-selling opportunities</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 mr-2">•</span>
                                <span><strong>All Segments:</strong> Regular monitoring and trend analysis</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historical Data Table -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Historical Churn Risk Data</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg RFM Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Churn Risk %</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Risk Level</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($predictiveData['churn_risk'] ?? [] as $date => $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($data['date'])->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ number_format($data['avg_rfm'], 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $data['churn_risk'] >= 20 ? 'bg-red-100 text-red-800' : 
                                       ($data['churn_risk'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    {{ number_format($data['churn_risk'], 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                @if($data['churn_risk'] >= 20)
                                    <span class="text-red-600 dark:text-red-400 font-semibold">High</span>
                                @elseif($data['churn_risk'] >= 10)
                                    <span class="text-yellow-600 dark:text-yellow-400 font-semibold">Medium</span>
                                @else
                                    <span class="text-green-600 dark:text-green-400 font-semibold">Low</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No historical churn risk data available
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Predictive data
        const predictiveData = @json($predictiveData ?? {});
        const churnRiskData = @json($predictiveData['churn_risk'] ?? {});
        
        // Churn Risk Trend Chart
        const churnCtx = document.getElementById('churnRiskChart').getContext('2d');
        const churnDates = Object.keys(churnRiskData).sort();
        
        new Chart(churnCtx, {
            type: 'line',
            data: {
                labels: churnDates.map(date => new Date(date).toLocaleDateString()),
                datasets: [{
                    label: 'Churn Risk %',
                    data: churnDates.map(date => churnRiskData[date].churn_risk),
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Avg RFM Score',
                    data: churnDates.map(date => churnRiskData[date].avg_rfm),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Churn Risk %',
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
                            text: 'Avg RFM Score',
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                        },
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Risk Distribution Chart
        const riskCtx = document.getElementById('riskDistributionChart').getContext('2d');
        
        // Calculate risk distribution from historical data
        const riskLevels = {
            'Low Risk': 0,
            'Medium Risk': 0,
            'High Risk': 0
        };
        
        Object.values(churnRiskData).forEach(data => {
            if (data.churn_risk < 10) {
                riskLevels['Low Risk']++;
            } else if (data.churn_risk < 20) {
                riskLevels['Medium Risk']++;
            } else {
                riskLevels['High Risk']++;
            }
        });

        new Chart(riskCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(riskLevels),
                datasets: [{
                    data: Object.values(riskLevels),
                    backgroundColor: [
                        '#10B981', // Green for Low Risk
                        '#F59E0B', // Yellow for Medium Risk
                        '#EF4444'  // Red for High Risk
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Scatter Plot (simplified version)
        const scatterCtx = document.getElementById('scatterChart').getContext('2d');
        
        const scatterData = Object.values(churnRiskData).map(data => ({
            x: data.avg_rfm,
            y: data.churn_risk
        }));

        new Chart(scatterCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'RFM vs Churn Risk',
                    data: scatterData,
                    backgroundColor: scatterData.map(point => 
                        point.y >= 20 ? '#EF4444' : 
                        point.y >= 10 ? '#F59E0B' : '#10B981'
                    ),
                    borderColor: '#ffffff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
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
                    y: {
                        title: {
                            display: true,
                            text: 'Churn Risk %',
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
    </script>
    @endpush
</x-app-layout>

