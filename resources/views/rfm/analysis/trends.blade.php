<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Trend Analysis</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        <!-- Trend Analysis Overview -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Score Trends</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Track how RFM scores change over time for your clients
                </p>
            </div>
        </div>

        <!-- Trend Filters -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Client Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Client</label>
                        <select name="client_id" class="mt-1 w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">All Clients</option>
                            <!-- Client options would be populated here -->
                        </select>
                    </div>

                    <!-- Time Period -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time Period</label>
                        <select name="months_back" class="mt-1 w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="6">Last 6 Months</option>
                            <option value="12" selected>Last 12 Months</option>
                            <option value="24">Last 24 Months</option>
                            <option value="36">Last 36 Months</option>
                        </select>
                    </div>

                    <!-- Apply Button -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">
                            Update Trends
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Trend Chart Placeholder -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">RFM Score Trends</h3>
                <div class="h-64 bg-gray-50 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Trend chart will be displayed here</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Coming soon with Chart.js integration</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trend Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Average RFM Score -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average RFM Score</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">6.8</p>
                    </div>
                </div>
            </div>

            <!-- Trend Direction -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Trend Direction</p>
                        <p class="text-2xl font-semibold text-green-600 dark:text-green-400">+0.3</p>
                    </div>
                </div>
            </div>

            <!-- Active Clients -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Clients</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">156</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 