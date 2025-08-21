<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Data Test</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        <!-- Overview -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Raw RFM Data</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Testing the RFM data endpoint - showing R, F, M scores, date, and client ID for each invoice
                </p>
            </div>
        </div>

        <!-- Data Display -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Data</h3>
            </div>
            <div class="p-6">
                <div id="dataDisplay" class="space-y-4">
                    <div class="text-center text-gray-500">Loading data...</div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Summary</h3>
            </div>
            <div class="p-6">
                <div id="summaryDisplay" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" id="totalRecords">-</div>
                        <div class="text-sm text-gray-500">Total Records</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" id="uniqueClients">-</div>
                        <div class="text-sm text-gray-500">Unique Clients</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="dateRange">-</div>
                        <div class="text-sm text-gray-500">Date Range</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600" id="avgRfm">-</div>
                        <div class="text-sm text-gray-500">Avg RFM Score</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Load RFM data
        async function loadRfmData() {
            try {
                const response = await fetch('/invoices/rfm-data?months_back=12');
                const data = await response.json();
                
                if (data.error) {
                    console.error('Error:', data.error);
                    document.getElementById('dataDisplay').innerHTML = '<div class="text-red-500">Error: ' + data.error + '</div>';
                    return;
                }
                
                displayData(data);
                displaySummary(data);
                
            } catch (error) {
                console.error('Error loading RFM data:', error);
                document.getElementById('dataDisplay').innerHTML = '<div class="text-red-500">Error loading data</div>';
            }
        }

        // Display the raw data
        function displayData(data) {
            const container = document.getElementById('dataDisplay');
            
            if (!data.rfm_data || data.rfm_data.length === 0) {
                container.innerHTML = '<div class="text-gray-500 text-center">No RFM data found. Please sync invoices first.</div>';
                return;
            }
            
            let html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
            html += '<thead class="bg-gray-50 dark:bg-gray-800"><tr>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">R Score</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">F Score</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">M Score</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">RFM Score</th>';
            html += '</tr></thead>';
            html += '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';
            
            data.rfm_data.forEach(item => {
                html += '<tr>';
                html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' + new Date(item.date).toLocaleDateString() + '</td>';
                html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' + (item.client_name || 'Unknown') + '</td>';
                html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' + item.r_score + '</td>';
                html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' + item.f_score + '</td>';
                html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' + item.m_score + '</td>';
                html += '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">' + item.rfm_score + '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // Display summary statistics
        function displaySummary(data) {
            const rfmData = data.rfm_data;
            
            if (!rfmData || rfmData.length === 0) {
                return;
            }
            
            const totalRecords = rfmData.length;
            const uniqueClients = new Set(rfmData.map(item => item.client_id)).size;
            const dateRange = data.date_range ? 
                new Date(data.date_range.start).toLocaleDateString() + ' to ' + new Date(data.date_range.end).toLocaleDateString() : 
                'N/A';
            const avgRfm = (rfmData.reduce((sum, item) => sum + parseFloat(item.rfm_score), 0) / totalRecords).toFixed(2);
            
            document.getElementById('totalRecords').textContent = totalRecords;
            document.getElementById('uniqueClients').textContent = uniqueClients;
            document.getElementById('dateRange').textContent = dateRange;
            document.getElementById('avgRfm').textContent = avgRfm;
        }

        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            loadRfmData();
        });
    </script>
    @endpush
</x-app-layout>

