{{-- resources/views/trend/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Trend Analysis
        </h2>
    </x-slot>

    {{-- Optional styles for a nice card and chart container --}}
    @push('styles')
    <style>
        .trend-card { border-radius: 12px; }
        .trend-chart-container { padding: 16px; border-radius: 10px; }
    </style>
    @endpush

    <div class="p-6 space-y-6">
        {{-- Controls (optional) --}}
        <form method="GET" class="bg-white dark:bg-gray-800 shadow trend-card p-4 flex flex-wrap gap-3 items-end">
            <div>
                <label for="from" class="block text-sm font-medium text-gray-700 dark:text-gray-200">From</label>
                <input type="date" id="from" name="from" value="{{ request('from') }}"
                       class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label for="to" class="block text-sm font-medium text-gray-700 dark:text-gray-200">To</label>
                <input type="date" id="to" name="to" value="{{ request('to') }}"
                       class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label for="limit" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Top N Customers</label>
                <input type="number" id="limit" name="limit" value="{{ request('limit', 10) }}" min="1" step="1"
                       class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <button type="submit"
                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                Apply
            </button>
        </form>

        {{-- Chart --}}
        <div class="bg-white dark:bg-gray-800 shadow trend-card">
            <div class="trend-chart-container" style="min-height: 600px; height: 70vh;">
                <canvas id="customerTrend" height="600"></canvas>
            </div>
            <div class="px-5 pb-5 text-sm text-gray-600 dark:text-gray-300">
                One line per customer across the selected date range. Click legend items to toggle lines.
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        // Real RFM data passed from controller
        const RFM_DATA = @json($rfmData ?? []);
        console.log('RFM data from controller:', RFM_DATA);
        
        // Process the data for the chart
        function processRfmData() {
            if (!RFM_DATA || RFM_DATA.length === 0) {
                console.log('No RFM data available');
                return { labels: [], datasets: [] };
            }
            
            console.log('Processing RFM data:', RFM_DATA.length, 'records');
            
            // Group data by client
            const clientGroups = {};
            RFM_DATA.forEach(item => {
                const clientName = item.client_name || `Client ${item.client_id}`;
                if (!clientGroups[clientName]) {
                    clientGroups[clientName] = [];
                }
                const date = new Date(item.date);
                console.log(`Client: ${clientName}, Date: ${item.date} -> ${date.toISOString()}`);
                clientGroups[clientName].push({
                    date: date,
                    score: parseFloat(item.rfm_score)
                });
            });
            
            // Sort each client's data by date
            Object.keys(clientGroups).forEach(clientName => {
                clientGroups[clientName].sort((a, b) => a.date - b.date);
            });
            
            // Get all unique dates for labels - sort chronologically
            const allDateStrings = RFM_DATA.map(item => {
                const date = new Date(item.date);
                // Normalize to start of day to group by date only
                const normalizedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                const day = normalizedDate.getDate().toString().padStart(2, '0');
                const month = (normalizedDate.getMonth() + 1).toString().padStart(2, '0');
                const year = normalizedDate.getFullYear();
                return `${day}/${month}/${year}`;
            });
            console.log('All date strings:', allDateStrings);
            
            const uniqueDateStrings = [...new Set(allDateStrings)];
            console.log('Unique date strings:', uniqueDateStrings);
            
            const uniqueDates = uniqueDateStrings.map(dateStr => {
                const [day, month, year] = dateStr.split('/');
                return new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            });
            console.log('Unique dates before sorting:', uniqueDates.map(d => d.toISOString()));
            
            const sortedDates = uniqueDates.sort((a, b) => a - b);
            console.log('Sorted dates:', sortedDates.map(d => d.toISOString()));
            
            const allDates = sortedDates.map(date => {
                // Format as DD/MM/YYYY for better readability
                const day = date.getDate().toString().padStart(2, '0');
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            });
            
            console.log('Final date labels:', allDates);
            
            // Create datasets for each client
            const datasets = Object.keys(clientGroups).map((clientName, index) => {
                const clientData = clientGroups[clientName];
                
                // Create a map of date to score for this client
                const clientDataMap = {};
                clientData.forEach(item => {
                    // Normalize to start of day to group by date only
                    const normalizedDate = new Date(item.date.getFullYear(), item.date.getMonth(), item.date.getDate());
                    const day = normalizedDate.getDate().toString().padStart(2, '0');
                    const month = (normalizedDate.getMonth() + 1).toString().padStart(2, '0');
                    const year = normalizedDate.getFullYear();
                    const dateKey = `${day}/${month}/${year}`;
                    clientDataMap[dateKey] = item.score;
                });
                
                // Map data to align with the sorted date labels
                const data = allDates.map(dateLabel => clientDataMap[dateLabel] || null);
                
                return {
                    label: clientName,
                    data: data
                };
            });
            
            return { labels: allDates, datasets: datasets };
        }
        
        const chartData = processRfmData();
        const LABELS = chartData.labels;
        const DATASETS = chartData.datasets;

        // Stable color per customer label
        function colorFromString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
            const hue = Math.abs(hash) % 360;
            return `hsl(${hue}, 65%, 50%)`;
        }

        const styledDatasets = (DATASETS || []).map(ds => ({
            ...ds,
            borderColor: colorFromString(ds.label ?? 'Customer'),
            backgroundColor: 'transparent',
            pointRadius: 1.5,
            borderWidth: 2,
            tension: 0.2,
            spanGaps: true, // if you use nulls for missing dates, this draws gaps
        }));

        const ctx = document.getElementById('customerTrend').getContext('2d');

        // Guard: if no data, render an empty chart with a friendly message in the center
        if (!LABELS.length || !styledDatasets.length) {
            const empty = document.createElement('div');
            empty.textContent = 'No data to display. Adjust your filters or date range.';
            empty.className = 'p-6 text-center text-sm text-gray-500 dark:text-gray-400';
            document.querySelector('.trend-chart-container').appendChild(empty);
        }

        new Chart(ctx, {
            type: 'line',
            data: { labels: LABELS, datasets: styledDatasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 2,
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } },
                    tooltip: {
                        callbacks: {
                            title: items => items[0]?.label ?? '',
                            label: item => `${item.dataset.label}: ${item.formattedValue}`
                        }
                    },
                    decimation: { enabled: true } // performance for long series
                },
                scales: {
                    x: {
                        ticks: { maxRotation: 0, autoSkip: true },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                elements: { point: { hitRadius: 6 } }
            }
        });
    </script>
    @endpush
</x-app-layout>
