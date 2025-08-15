<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Reports</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        <!-- Report Generator Card -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Generate RFM Report</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Create detailed reports and analytics for your RFM data
                        </p>
                    </div>
                </div>
            </div>

            <!-- Report Options -->
            <div class="px-6 pb-6">
                <form method="GET" action="{{ route('rfm.reports.generate') }}" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Report Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Report Type</label>
                            <select name="report_type" class="mt-1 w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="summary">Summary Report</option>
                                <option value="detailed">Detailed Analysis</option>
                                <option value="trends">Trend Analysis</option>
                                <option value="comparison">Comparison Report</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                            <select name="date_range" class="mt-1 w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="current">Current Scores</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_quarter">Last Quarter</option>
                                <option value="last_year">Last Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition-colors">
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Reports -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Top Performers Report -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Top Performers</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">View your highest-scoring clients</p>
                <a href="{{ route('rfm.reports.generate', ['report_type' => 'top_performers', 'date_range' => 'current']) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    View Report
                </a>
            </div>

            <!-- At Risk Clients -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">At Risk Clients</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Identify clients with declining scores</p>
                <a href="{{ route('rfm.reports.generate', ['report_type' => 'at_risk', 'date_range' => 'current']) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                    View Report
                </a>
            </div>

            <!-- Growth Opportunities -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Growth Opportunities</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Find clients with potential for growth</p>
                <a href="{{ route('rfm.reports.generate', ['report_type' => 'growth', 'date_range' => 'current']) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    View Report
                </a>
            </div>
        </div>
    </div>
</x-app-layout> 