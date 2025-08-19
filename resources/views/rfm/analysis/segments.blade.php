<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Segment Analysis</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        <!-- Segment Analysis Overview -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Client Segmentation</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Analyze client segments based on RFM scores and behavior patterns
                </p>
            </div>
        </div>

        <!-- Segment Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <!-- Champions -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Champions</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $segmentData['champions'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">RFM 8-10</p>
                    </div>
                </div>
            </div>

            <!-- Loyal Customers -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Loyal</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $segmentData['loyal_customers'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">RFM 6-7.9</p>
                    </div>
                </div>
            </div>

            <!-- At Risk -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">At Risk</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $segmentData['at_risk'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">RFM 4-5.9</p>
                    </div>
                </div>
            </div>

            <!-- Can't Lose -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Can't Lose</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $segmentData['cant_lose'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">RFM 2-3.9</p>
                    </div>
                </div>
            </div>

            <!-- Lost -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lost</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $segmentData['lost'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">RFM 0-1.9</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segment Distribution Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pie Chart -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Segment Distribution</h3>
                </div>
                <div class="p-6">
                    <canvas id="segmentPieChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Bar Chart -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Segment Comparison</h3>
                </div>
                <div class="p-6">
                    <canvas id="segmentBarChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Segment Details -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Segment Details</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Champions Details -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">Champions</h4>
                        <p class="text-sm text-green-700 dark:text-green-300 mb-3">
                            Your best customers with high RFM scores. They buy frequently, spend more, and have purchased recently.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">Count:</span>
                                <span class="font-semibold">{{ $segmentData['champions'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">Percentage:</span>
                                <span class="font-semibold">
                                    @php
                                        $total = array_sum($segmentData ?? []);
                                        echo $total > 0 ? round(($segmentData['champions'] ?? 0) / $total * 100, 1) : 0;
                                    @endphp%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Loyal Customers Details -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">Loyal Customers</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
                            Good customers who buy regularly but may not spend as much as champions.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-600 dark:text-blue-400">Count:</span>
                                <span class="font-semibold">{{ $segmentData['loyal_customers'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-600 dark:text-blue-400">Percentage:</span>
                                <span class="font-semibold">
                                    @php
                                        $total = array_sum($segmentData ?? []);
                                        echo $total > 0 ? round(($segmentData['loyal_customers'] ?? 0) / $total * 100, 1) : 0;
                                    @endphp%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- At Risk Details -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">At Risk</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
                            Customers who haven't purchased recently or frequently enough.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600 dark:text-yellow-400">Count:</span>
                                <span class="font-semibold">{{ $segmentData['at_risk'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600 dark:text-yellow-400">Percentage:</span>
                                <span class="font-semibold">
                                    @php
                                        $total = array_sum($segmentData ?? []);
                                        echo $total > 0 ? round(($segmentData['at_risk'] ?? 0) / $total * 100, 1) : 0;
                                    @endphp%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Can't Lose Details -->
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">Can't Lose</h4>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-3">
                            High-value customers who haven't purchased recently. Need immediate attention.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-red-600 dark:text-red-400">Count:</span>
                                <span class="font-semibold">{{ $segmentData['cant_lose'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-red-600 dark:text-red-400">Percentage:</span>
                                <span class="font-semibold">
                                    @php
                                        $total = array_sum($segmentData ?? []);
                                        echo $total > 0 ? round(($segmentData['cant_lose'] ?? 0) / $total * 100, 1) : 0;
                                    @endphp%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Lost Details -->
                    <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Lost</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                            Customers who haven't purchased in a long time and may be lost forever.
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Count:</span>
                                <span class="font-semibold">{{ $segmentData['lost'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Percentage:</span>
                                <span class="font-semibold">
                                    @php
                                        $total = array_sum($segmentData ?? []);
                                        echo $total > 0 ? round(($segmentData['lost'] ?? 0) / $total * 100, 1) : 0;
                                    @endphp%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Segment Recommendations</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">High Priority Actions</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span><strong>Can't Lose:</strong> Immediate re-engagement campaigns, personalized offers</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-yellow-500 mr-2">•</span>
                                <span><strong>At Risk:</strong> Win-back campaigns, loyalty programs</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 mr-2">•</span>
                                <span><strong>Loyal:</strong> Upselling opportunities, referral programs</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Growth Opportunities</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">•</span>
                                <span><strong>Champions:</strong> VIP treatment, exclusive access, testimonials</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 mr-2">•</span>
                                <span><strong>Loyal:</strong> Premium services, early access to new products</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-gray-500 mr-2">•</span>
                                <span><strong>Lost:</strong> Low-cost reactivation campaigns</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Segment data
        const segmentData = @json($segmentData ?? []);
        const segmentDistribution = @json($segmentDistribution ?? {});
        
        // Pie Chart
        const pieCtx = document.getElementById('segmentPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Champions', 'Loyal Customers', 'At Risk', 'Can\'t Lose', 'Lost'],
                datasets: [{
                    data: [
                        segmentData.champions || 0,
                        segmentData.loyal_customers || 0,
                        segmentData.at_risk || 0,
                        segmentData.cant_lose || 0,
                        segmentData.lost || 0
                    ],
                    backgroundColor: [
                        '#10B981', // Green for Champions
                        '#3B82F6', // Blue for Loyal
                        '#F59E0B', // Yellow for At Risk
                        '#EF4444', // Red for Can't Lose
                        '#6B7280'  // Gray for Lost
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
                    },
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

        // Bar Chart
        const barCtx = document.getElementById('segmentBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Champions', 'Loyal', 'At Risk', 'Can\'t Lose', 'Lost'],
                datasets: [{
                    label: 'Number of Clients',
                    data: [
                        segmentData.champions || 0,
                        segmentData.loyal_customers || 0,
                        segmentData.at_risk || 0,
                        segmentData.cant_lose || 0,
                        segmentData.lost || 0
                    ],
                    backgroundColor: [
                        '#10B981',
                        '#3B82F6',
                        '#F59E0B',
                        '#EF4444',
                        '#6B7280'
                    ],
                    borderWidth: 0,
                    borderRadius: 4
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
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>

