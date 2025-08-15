<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Analysis</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        @if (session('status'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
            </div>
        @endif

        <!-- Actions Card -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Calculate RFM Scores</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">RFM scores help you understand customer value and behavior</p>
            </div>

            <div class="px-4 py-4 space-y-4">
                <!-- Current Scores -->
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Current Scores</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Calculate RFM scores based on your latest invoice data</p>
                    </div>
                    <form method="POST" action="{{ route('rfm.sync') }}">
                        @csrf
                        <button type="submit" name="action" value="current" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                            Calculate Now
                        </button>
                    </form>
                </div>

                <!-- Historical Analysis -->
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Historical Analysis</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Create snapshots for trend analysis and future charts</p>
                        </div>
                        <form method="POST" action="{{ route('rfm.sync') }}" class="flex items-center gap-2">
                            @csrf
                            <select name="months_back" class="border rounded px-2 py-1 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                <option value="12">12 months</option>
                                <option value="24">24 months</option>
                                <option value="36">36 months</option>
                            </select>
                            <button type="submit" name="action" value="historical" class="px-4 py-2 rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Generate Monthly Snapshots
                            </button>
                        </form>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-2">
                        <strong>Date Logic:</strong> Monthly snapshots are created for the 1st of each month (e.g., Aug 1st, Jul 1st, etc.) for consistent historical comparison.
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
                            <option value="current" {{ $viewMode === 'current' ? 'selected' : '' }}>Current Scores</option>
                            @foreach($availableSnapshots as $date)
                                <option value="{{ $date }}" {{ $viewMode === $date ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($date)->format('M 1, Y') }} Snapshot
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
                        @if($viewMode === 'current')
                            Current Scores
                        @else
                            {{ \Carbon\Carbon::parse($viewMode)->format('M 1, Y') }} Snapshot
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
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">R</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">F</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">M</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">RFM</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">Txns</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">Revenue</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Last Txn</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Snapshot</th>
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
                                                                 <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ number_format($r->r_score, 2) }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ $r->f_score }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ $r->m_score }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">
                                    @php
                                        $rfm = (float) $r->rfm_score;
                                        $rfmClass = $rfm >= 7.0
                                            ? 'bg-green-100 text-green-800'
                                            : ($rfm >= 5.0
                                                ? 'bg-blue-100 text-blue-800'
                                                : ($rfm >= 3.0
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-red-100 text-red-800'));
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs {{ $rfmClass }}">
                                        {{ number_format($rfm, 1) }}
                                    </span>
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ $r->txn_count }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ number_format($r->monetary_sum, 2) }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ \Carbon\Carbon::parse($r->last_txn_date)->format('M Y') }}</td>
                                                                 <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                                     {{ \Carbon\Carbon::parse($r->snapshot_date)->format('M j, Y') }}
                                 </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700" colspan="10">
                                    No RFM data found. Click "Sync now" to compute current scores.
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
