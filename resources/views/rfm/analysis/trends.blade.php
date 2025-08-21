{{-- resources/views/rfm/analysis/trends.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            {{ __('Trend Analysis') }}
        </h2>
    </x-slot>

    @push('styles')
    <style>
        .trend-card { border-radius: 12px; }
        .trend-chart-container { padding: 16px; border-radius: 10px; height: 420px; }
    </style>
    @endpush

    <div class="p-6 space-y-6">
        {{-- Filters --}}
        <form method="GET" class="bg-white dark:bg-gray-800 shadow trend-card p-4 flex flex-wrap gap-3 items-end">
            <div>
                <label for="months_back" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Months back</label>
                <input type="number" id="months_back" name="months_back" value="{{ $monthsBack ?? 12 }}" min="1" max="36"
                       class="mt-1 block w-28 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label for="metric" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Metric</label>
                <select id="metric" name="metric"
                        class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @php $opts = ['rfm_score'=>'RFM Score','r_score'=>'Recency','f_score'=>'Frequency','m_score'=>'Monetary (score)','txn_count'=>'Transactions','monetary_sum'=>'Monetary (sum)']; @endphp
                    @foreach($opts as $val => $label)
                        <option value="{{ $val }}" @selected(($metric ?? 'rfm_score') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="limit" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Top N customers</label>
                <input type="number" id="limit" name="limit" value="{{ $limit ?? 12 }}" min="1" max="50"
                       class="mt-1 block w-28 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <button type="submit"
                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                Apply
            </button>
        </form>

        {{-- Chart --}}
        <div class="bg-white dark:bg-gray-800 shadow trend-card">
            <div class="trend-chart-container">
                <canvas id="customerTrend"></canvas>
            </div>
            <div class="px-5 pb-5 text-sm text-gray-600 dark:text-gray-300">
                {{ __('Each line is a customer. Click legend items to toggle lines.') }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const LABELS   = @json($labels ?? []);
        const DATASETS = @json($datasets ?? []);
        const METRIC   = @json($metric ?? 'rfm_score');

        function colorFromString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
            const hue = Math.abs(hash) % 360;
            return `hsl(${hue}, 65%, 50%)`;
        }

        const styledDatasets = (DATASETS || []).map(ds => ({
            ...ds,
            borderColor: colorFromString(ds.label || 'Customer'),
            backgroundColor: 'transparent',
            pointRadius: 1.5,
            borderWidth: 2,
            tension: 0.2,
            spanGaps: true,
        }));

        const ctx = document.getElementById('customerTrend');
        if (!ctx) {
            console.error('Canvas #customerTrend not found');
        } else {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: { labels: LABELS, datasets: styledDatasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12 } },
                        tooltip: {
                            callbacks: {
                                title: items => items[0]?.label ?? '',
                                label: item => `${item.dataset.label}: ${item.formattedValue}`
                            }
                        },
                        decimation: { enabled: true }
                    },
                    scales: {
                        x: { ticks: { maxRotation: 0, autoSkip: true }, grid: { display: false } },
                        y: { beginAtZero: true }
                    },
                    elements: { point: { hitRadius: 6 } }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
