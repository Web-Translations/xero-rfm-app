<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Invoices</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        @if (session('status'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
            </div>
        @endif

        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <!-- Card header -->
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Filters</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Showing {{ $filteredCount }} of {{ $totalInvoices }} total invoices</span>
                </div>
                <form method="POST" action="{{ route('invoices.sync') }}">
                    @csrf
                    <button
                        class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm border border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 text-sm">
                        Sync from Xero
                    </button>
                </form>
            </div>

            <!-- Filter form -->
            <form method="GET" id="filters-form" class="px-4 py-4">
                <!-- First row: Date range + Search -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                        <select name="days" class="mt-1 w-full border rounded-md px-2 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="0" {{ (int)$days === 0 ? 'selected' : '' }}>All time</option>
                            <option value="30" {{ (int)$days === 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ (int)$days === 90 ? 'selected' : '' }}>Last 90 days</option>
                            <option value="180" {{ (int)$days === 180 ? 'selected' : '' }}>Last 6 months</option>
                            <option value="365" {{ (int)$days === 365 ? 'selected' : '' }}>Last year</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input type="text"
                               name="q"
                               value="{{ $q }}"
                               placeholder="Invoice number or client..."
                               class="mt-1 w-full border rounded-md px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                </div>

                <!-- Second row: Status full width -->
                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center text-xs px-2 py-1 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                onclick="document.querySelectorAll('input[name=\'statuses[]\']').forEach(cb=>cb.checked=true)">
                                Select all
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center text-xs px-2 py-1 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                onclick="document.querySelectorAll('input[name=\'statuses[]\']').forEach(cb=>cb.checked=false)">
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2 p-2 border rounded-md dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        @php
                            $allStatuses = ['DRAFT','SUBMITTED','DELETED','AUTHORISED','PAID','VOIDED'];
                        @endphp
                        @foreach($allStatuses as $s)
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox"
                                       name="statuses[]"
                                       value="{{ $s }}"
                                       class="rounded border-gray-300 dark:border-gray-600"
                                       {{ in_array($s, (array)($statuses ?? []), true) ? 'checked' : '' }}>
                                <span>{{ $s }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Footer actions -->
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2">
                    <a href="{{ url()->current() }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Reset
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm border border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 text-sm">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Card -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Results</h3>
            </div>

            <div class="px-4 py-2 flex flex-wrap gap-2 text-xs">
                @if((int)$days > 0)
                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        Date: <span class="font-medium">Last {{ (int)$days }} days</span>
                    </span>
                @else
                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        Date: <span class="font-medium">All time</span>
                    </span>
                @endif
                @if(!empty($statuses))
                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        Status: <span class="font-medium">{{ implode(', ', $statuses) }}</span>
                    </span>
                @endif
                @if($q !== '')
                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        Search: <span class="font-medium">{{ $q }}</span>
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 sticky top-0 z-10">
                        <tr class="text-left">
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Number</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Client</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Date</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Due</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">Subtotal</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-right font-semibold">Total</th>
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($invoices as $inv)
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 font-mono">{{ $inv->invoice_number }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ optional($clients->get($inv->contact_id))->name }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ $inv->date?->format('Y-m-d') }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ $inv->due_date?->format('Y-m-d') }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ number_format((float) $inv->subtotal, 2) }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ number_format((float) $inv->total, 2) }} {{ $inv->currency }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">
                                    <span class="px-2 py-1 rounded-md text-xs {{ $inv->status==='PAID' ? 'bg-green-100 text-green-800' : ($inv->status==='AUTHORISED' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3 border-b dark:border-gray-700" colspan="7">No invoices found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="text-gray-700 dark:text-gray-300">
            {{ $invoices->links() }}
        </div>
    </div>
</x-app-layout>
