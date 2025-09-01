<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Invoices</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @php
            $hasInvoices = ($totalInvoices ?? 0) > 0;
        @endphp
        <!-- Intro Card (always visible) -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-800">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 dark:bg-indigo-900 rounded-full mb-3">
                    <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">Invoices</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Sync your Xero sales invoices to power RFM analysis and reporting.
                </p>
            </div>
        </div>
        @if (session('status'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
            </div>
        @endif

        @if (! $hasInvoices)
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-5 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Import your invoices to get started</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">We’ll fetch your full invoice history from Xero to power your RFM analysis.</p>
                    <button
                        id="sync-button-empty"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm border border-indigo-600 text-sm">
                        <svg id="sync-icon-empty" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span id="sync-button-text-empty">Start full sync</span>
                    </button>
                    <span id="sync-counter-empty" class="hidden ml-3 text-sm text-gray-700 dark:text-gray-300">Syncing <span id="sync-count-empty">0</span> invoices...</span>
                </div>
            </div>
        @endif

        <!-- Filters Card (disabled/greyed when no invoices yet) -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 {{ $hasInvoices ? '' : 'opacity-50 pointer-events-none' }}">
            <!-- Card header -->
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Filters</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Showing {{ $filteredCount }} of {{ $totalInvoices }} total invoices</span>
                </div>
                <div class="flex items-center justify-between">
                    <!-- Left side: Sync info and counter -->
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center mr-4">
                        @if($lastSyncInfo['last_sync_at'])
                            <span>Last synced {{ number_format($lastSyncInfo['last_sync_invoice_count']) }} invoices at {{ $lastSyncInfo['last_sync_at']->format('M j, Y g:i A') }}</span>
                        @else
                            <span>No invoice sync yet</span>
                        @endif
                        
                        <!-- Sync Counter (hidden by default) -->
                        <span id="sync-counter" class="hidden ml-2">
                            <span class="text-gray-400 dark:text-gray-600 mx-1">•</span>
                            <span class="ml-1">Syncing <span id="sync-count">0</span> invoices...</span>
                        </span>
                    </div>
                    
                    <!-- Right side: Sync Button -->
                    <button
                        id="sync-button"
                        class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm border border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 text-sm transition-all duration-200">
                        <svg id="sync-icon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span id="sync-button-text">Sync from Xero</span>
                    </button>
                </div>
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

        <!-- Results Card (disabled/greyed when no invoices yet) -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 {{ $hasInvoices ? '' : 'opacity-50 pointer-events-none' }}">
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
                            <th class="p-3 border-b border-gray-200 dark:border-gray-700 text-center font-semibold">Exclude</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($invoices as $inv)
                            @php
                                $isExcluded = in_array($inv->invoice_id, $excludedInvoiceIds);
                            @endphp
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 {{ $isExcluded ? 'line-through opacity-60' : '' }}">
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 font-mono">{{ $inv->invoice_number }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ optional($clients->get($inv->contact_id))->name }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ $inv->date?->format('M j, Y') }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ $inv->due_date?->format('M j, Y') }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ number_format((float) $inv->subtotal, 2) }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-right">{{ number_format((float) $inv->total, 2) }} {{ $inv->currency }}</td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700">
                                    <span class="px-2 py-1 rounded-md text-xs {{ $inv->status==='PAID' ? 'bg-green-100 text-green-800' : ($inv->status==='AUTHORISED' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                                <td class="p-3 border-b border-gray-200 dark:border-gray-700 text-center">
                                    <input type="checkbox" 
                                           class="exclude-checkbox rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500"
                                           data-invoice-id="{{ $inv->invoice_id }}"
                                           {{ $isExcluded ? 'checked' : '' }}
                                           title="Exclude from RFM calculations">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3 border-b dark:border-gray-700" colspan="8">No invoices found for the selected filters.</td>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle exclude checkbox changes
            document.querySelectorAll('.exclude-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const invoiceId = this.dataset.invoiceId;
                    const isChecked = this.checked;
                    const row = this.closest('tr');
                    
                    // Show loading state
                    this.disabled = true;
                    
                    // Make AJAX request
                    const url = isChecked 
                        ? '{{ route("invoices.exclude", ["invoice" => ":id"]) }}'.replace(':id', invoiceId)
                        : '{{ route("invoices.unexclude", ["invoice" => ":id"]) }}'.replace(':id', invoiceId);
                    
                    const method = isChecked ? 'POST' : 'DELETE';
                    
                    fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update row styling
                            if (isChecked) {
                                row.classList.add('line-through', 'opacity-60');
                            } else {
                                row.classList.remove('line-through', 'opacity-60');
                            }
                        } else {
                            // Revert checkbox if failed
                            this.checked = !isChecked;
                            alert('Failed to update exclusion status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revert checkbox if failed
                        this.checked = !isChecked;
                        alert('Failed to update exclusion status');
                    })
                    .finally(() => {
                        // Re-enable checkbox
                        this.disabled = false;
                    });
                });
            });

            // Enhanced sync functionality
            const syncButton = document.getElementById('sync-button');
            const syncCounter = document.getElementById('sync-counter');
            const syncCount = document.getElementById('sync-count');
            const syncIcon = document.getElementById('sync-icon');
            const syncButtonText = document.getElementById('sync-button-text');

            let syncInProgress = false;
            let progressInterval = null;
            let syncTimeout = null;

            syncButton.addEventListener('click', function() {
                if (syncInProgress) return;

                startSync();
            });

            function startSync() {
                syncInProgress = true;
                
                // Update UI to show sync is starting
                syncButton.disabled = true;
                syncButton.classList.add('opacity-50');
                syncIcon.classList.add('animate-spin');
                syncButtonText.textContent = 'Starting...';
                syncCounter.classList.remove('hidden');
                
                // Set a timeout to prevent hanging (30 minutes)
                syncTimeout = setTimeout(() => {
                    if (syncInProgress) {
                        resetSyncUI();
                        alert('Sync timed out after 30 minutes. Please try again.');
                    }
                }, 30 * 60 * 1000);
                
                // Start the sync process
                fetch('{{ route("invoices.sync") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Start polling for progress
                        startProgressPolling();
                        // Start fetching batches
                        fetchNextBatch();
                    } else {
                        throw new Error(data.message || 'Failed to start sync');
                    }
                })
                .catch(error => {
                    console.error('Sync error:', error);
                    resetSyncUI();
                    alert('Failed to start sync: ' + error.message);
                });
            }
            // Empty-state sync button hooks same flow
            const emptyBtn = document.getElementById('sync-button-empty');
            if (emptyBtn) {
                emptyBtn.addEventListener('click', function() {
                    if (syncInProgress) return;
                    // Mirror main startSync but simpler UI
                    syncInProgress = true;
                    this.disabled = true;
                    document.getElementById('sync-icon-empty').classList.add('animate-spin');
                    document.getElementById('sync-button-text-empty').textContent = 'Starting...';
                    fetch('{{ route("invoices.sync") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (!d.success) throw new Error(d.message || 'Failed to start');
                        startProgressPolling();
                        fetchNextBatch();
                    })
                    .catch(err => {
                        console.error(err);
                        this.disabled = false;
                        document.getElementById('sync-icon-empty').classList.remove('animate-spin');
                        document.getElementById('sync-button-text-empty').textContent = 'Start full sync';
                        alert('Failed to start sync: ' + err.message);
                        syncInProgress = false;
                    });
                });
            }

            function startProgressPolling() {
                progressInterval = setInterval(() => {
                    fetch('{{ route("invoices.sync") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ action: 'get_progress' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        updateProgressUI(data);
                    })
                    .catch(error => {
                        console.error('Progress polling error:', error);
                    });
                }, 1000); // Poll every second
            }

            function fetchNextBatch() {
                fetch('{{ route("invoices.sync") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'fetch_batch' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.completed) {
                            // Sync completed
                            completeSync();
                        } else if (data.has_more) {
                            // Continue with next batch after a short delay
                            setTimeout(fetchNextBatch, 1000);
                        } else {
                            // No more data
                            completeSync();
                        }
                    } else {
                        throw new Error(data.error || 'Failed to fetch batch');
                    }
                })
                .catch(error => {
                    console.error('Batch fetch error:', error);
                    resetSyncUI();
                    alert('Sync failed: ' + error.message);
                });
            }

            function completeSync() {
                fetch('{{ route("invoices.sync") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'complete' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message briefly
                        syncCount.textContent = data.processed_invoices.toLocaleString();
                        
                        // Reset UI after a delay
                        setTimeout(() => {
                            resetSyncUI();
                            // Reload page to show updated data
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.error || 'Failed to complete sync');
                    }
                })
                .catch(error => {
                    console.error('Complete sync error:', error);
                    resetSyncUI();
                    alert('Failed to complete sync: ' + error.message);
                });
            }

            function updateProgressUI(progress) {
                if (progress.processed_invoices > 0) {
                    // Simple counter update
                    syncCount.textContent = progress.processed_invoices.toLocaleString();
                    const emptyCounter = document.getElementById('sync-counter-empty');
                    const emptyCount = document.getElementById('sync-count-empty');
                    if (emptyCounter && emptyCount) {
                        emptyCounter.classList.remove('hidden');
                        emptyCount.textContent = progress.processed_invoices.toLocaleString();
                    }
                }
            }

            function resetSyncUI() {
                syncInProgress = false;
                syncButton.disabled = false;
                syncButton.classList.remove('opacity-50');
                syncIcon.classList.remove('animate-spin');
                syncButtonText.textContent = 'Sync from Xero';
                syncCounter.classList.add('hidden');
                syncCount.textContent = '0';
                
                if (progressInterval) {
                    clearInterval(progressInterval);
                    progressInterval = null;
                }
                
                if (syncTimeout) {
                    clearTimeout(syncTimeout);
                    syncTimeout = null;
                }
            }
        });
    </script>
</x-app-layout>
