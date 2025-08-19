<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Scores</h2>
    </x-slot>

    {{-- KaTeX for beautiful math rendering --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"
            onload="renderMathInElement(document.body, {delimiters:[{left:'$$',right:'$$',display:true},{left:'\\(',right:'\\)',display:false},{left:'\\[',right:'\\]',display:true}]});">
    </script>

    <div class="p-6 space-y-6">
        @if (session('status'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
            </div>
        @endif

        <!-- Combined Configuration and Calculate Card -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Configuration & Analysis</h3>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('rfm.config.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                            Configure Settings →
                        </a>
                        <form method="POST" action="{{ route('rfm.sync') }}" class="inline">
                            @csrf
                            <button type="submit" name="action" value="sync_all" class="px-6 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition-colors">
                                Calculate RFM Scores
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4">
                <!-- Current Configuration -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Recency Window:</span>
                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $config->recency_window_months }} months</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Frequency Period:</span>
                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $config->frequency_period_months }} months</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Monetary Window:</span>
                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $config->monetary_window_months }} months</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Benchmark:</span>
                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">
                            @if($config->monetary_benchmark_mode === 'percentile')
                                Top {{ $config->monetary_benchmark_percentile }}%
                            @else
                                £{{ number_format($config->monetary_benchmark_value, 2) }}
                            @endif
                        </span>
                    </div>
                </div>
                
                <!-- RFM Formulas -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">RFM Calculation Formulas</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <div class="font-medium text-blue-600 dark:text-blue-400 mb-1">Recency (R)</div>
                            <div class="text-center mb-2">
                                \[ R = 10 - \frac{10}{\text{Window Months}} \times \text{Months Since Last} \]
                            </div>
                            <div class="text-gray-600 dark:text-gray-400">Score: 0-10</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <div class="font-medium text-green-600 dark:text-green-400 mb-1">Frequency (F)</div>
                            <div class="text-center mb-2">
                                \[ F = \text{Invoice Count in Window} \]
                            </div>
                            <div class="text-gray-600 dark:text-gray-400">Score: 0-10 (capped)</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <div class="font-medium text-purple-600 dark:text-purple-400 mb-1">Monetary (M)</div>
                            <div class="text-center mb-2">
                                \[ M = \frac{\text{Largest Invoice}}{\text{Benchmark}} \times 10 \]
                            </div>
                            <div class="text-gray-600 dark:text-gray-400">Score: 0-10</div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Overall RFM Score</div>
                        <div class="text-center">
                            \[ \text{RFM} = \frac{R + F + M}{3} \]
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Options Card -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <!-- Card header -->
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">View RFM Scores</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Showing {{ $filteredCount }} of {{ $totalClients }} total clients
                        @if($availableSnapshots->count() > 0)
                            • {{ $availableSnapshots->count() }} monthly snapshots available
                        @endif
                    </span>
                </div>
            </div>

            <!-- Filter body (3 widgets spread out without individual cards) -->
            <form method="GET" class="px-4 py-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- View RFM Data -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">View RFM Data</label>
                        <select name="view" class="mt-1 w-full border rounded px-2 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                            @foreach($availableSnapshots as $date)
                                <option value="{{ $date }}" {{ $viewMode === $date ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($date)->format('M j, Y') }} Snapshot
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Client name..."
                            class="mt-1 w-full border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                        />
                    </div>

                    <!-- Apply -->
                    <div class="flex md:justify-end">
                        <button class="w-full md:w-auto px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Card -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">RFM Leaderboard</h3>
            </div>

            <!-- Active Filters -->
            <div class="px-4 py-2 flex gap-2 text-xs">
                <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                    View: <span class="font-medium">
                        @if($viewMode === 'date')
                            {{ \Carbon\Carbon::parse($viewMode)->format('M j, Y') }} Snapshot
                        @endif
                    </span>
                </span>
                @if($search !== '')
                    <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        Search: <span class="font-medium">{{ $search }}</span>
                    </span>
                @endif
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 sticky top-0 z-10">
                        <tr class="text-left">
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-center font-semibold w-12">#</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Client</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-center font-semibold">R</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-center font-semibold">F</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-center font-semibold">M</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-center font-semibold">RFM</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Snapshot Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($rows as $index => $r)
                            @php
                                $rank = ($rows->currentPage() - 1) * $rows->perPage() + $index + 1;
                            @endphp
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-center font-semibold text-gray-600 dark:text-gray-400">
                                    {{ $rank }}
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 font-medium">
                                    {{ $r->client_name }}
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-center">
                                    <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ number_format($r->r_score, 1) }}
                                    </span>
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-center">
                                    <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $r->f_score }}
                                    </span>
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-center">
                                    <span class="px-2 py-1 rounded text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        {{ number_format($r->m_score, 1) }}
                                    </span>
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-center">
                                    @php
                                        $rfm = (float) $r->rfm_score;
                                        $rfmClass = $rfm >= 7.0
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : ($rfm >= 5.0
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                                : ($rfm >= 3.0
                                                    ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'));
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $rfmClass }}">
                                        {{ number_format($rfm, 1) }}
                                    </span>
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($r->snapshot_date)->format('M j, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700" colspan="7">
                                    No RFM data found. Click "Calculate RFM Scores" to compute current scores.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                <div class="text-gray-700 dark:text-gray-300">{{ $rows->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
