<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
                </div>
            @endif

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 backdrop-blur shadow-lg sm:rounded-2xl p-6 border border-indigo-200/50 dark:border-indigo-700/50 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">Total Users</div>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-bold tracking-tight text-indigo-900 dark:text-indigo-100">{{ number_format($stats['total_users']) }}</div>
                </div>
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 backdrop-blur shadow-lg sm:rounded-2xl p-6 border border-emerald-200/50 dark:border-emerald-700/50 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Paying Subscribers</div>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v3m2-13h2a2 2 0 012 2v6a2 2 0 01-2 2h-2m-4 0H6a2 2 0 01-2-2V9a2 2 0 012-2h2m8 4V9a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-bold tracking-tight text-emerald-900 dark:text-emerald-100">{{ number_format($stats['paying_subscribers']) }}</div>
                </div>
                <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20 backdrop-blur shadow-lg sm:rounded-2xl p-6 border border-cyan-200/50 dark:border-cyan-700/50 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-cyan-700 dark:text-cyan-300">Xero Connections</div>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-3xl font-bold tracking-tight text-cyan-900 dark:text-cyan-100">{{ number_format($stats['xero_connections']) }} <span class="text-sm font-normal text-cyan-600 dark:text-cyan-400">({{ number_format($stats['xero_active_connections']) }} active)</span></div>
                </div>
            </div>

            <!-- Plan Mix -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800/20 dark:to-slate-700/20 backdrop-blur shadow-lg sm:rounded-2xl p-6 border border-slate-200/50 dark:border-slate-600/50 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-slate-600 dark:text-slate-300">Free Plan</div>
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['free_users']) }}</div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 backdrop-blur shadow-lg sm:rounded-2xl p-6 border border-blue-200/50 dark:border-blue-600/50 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-blue-600 dark:text-blue-300">Pro Plan</div>
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 text-2xl font-bold text-blue-800 dark:text-blue-100">{{ number_format($stats['pro_users']) }}</div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 backdrop-blur shadow-lg sm:rounded-2xl p-6 border border-purple-200/50 dark:border-purple-600/50 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-purple-600 dark:text-purple-300">Pro+ Plan</div>
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 text-2xl font-bold text-purple-800 dark:text-purple-100">{{ number_format($stats['pro_plus_users']) }}</div>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="bg-gradient-to-br from-white/90 to-gray-50/90 dark:from-gray-900/90 dark:to-gray-800/90 backdrop-blur overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="p-6 md:p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Customers</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Users, plans, payment status, and recent activity.</p>
                        </div>
                        <form method="GET" action="{{ route('admin.index') }}" class="flex items-center space-x-2">
                            <input
                                type="text"
                                name="q"
                                value="{{ $search ?? '' }}"
                                placeholder="Search name, email, or ID..."
                                class="block w-64 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                            <button class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
                                Search
                            </button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200/60 dark:divide-gray-700/60">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/90 dark:to-gray-700/90">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">User ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Plan</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Xero Orgs</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Last Sync</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Last RFM</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/80 dark:bg-gray-900/80 divide-y divide-gray-100/60 dark:divide-gray-700/60">
                                @forelse($customers as $customer)
                                    <tr class="odd:bg-white/60 even:bg-gray-50/40 dark:odd:bg-gray-900/60 dark:even:bg-gray-800/40 hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition-colors duration-200">
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $customer->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $customer->id }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $customer->email }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                @class([
                                                    'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200' => $customer->subscription_plan === 'free',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200' => $customer->subscription_plan === 'pro',
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200' => $customer->subscription_plan === 'pro_plus',
                                                ])">
                                                {{ ucfirst(str_replace('_', ' ', $customer->subscription_plan)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                @class([
                                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-200' => $customer->subscription_status === 'active',
                                                    'bg-amber-100 text-amber-800 dark:bg-amber-800 dark:text-amber-200' => $customer->subscription_status === 'pending',
                                                    'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200' => $customer->subscription_status === 'canceled',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' => !in_array($customer->subscription_status, ['active','pending','canceled']),
                                                ])">
                                                {{ ucfirst($customer->subscription_status) }}
                                            </span>
                                            @if($customer->subscription_ends_at)
                                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Ends {{ $customer->subscription_ends_at->diffForHumans() }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 font-semibold">{{ $customer->xero_connections_count }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            @php $lastSync = $lastSyncByUser[$customer->id] ?? null; @endphp
                                            @if($lastSync)
                                                {{ \Carbon\Carbon::parse($lastSync)->diffForHumans() }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            @php $lastRfm = $latestRfmByUser[$customer->id] ?? null; @endphp
                                            @if($lastRfm)
                                                {{ \Carbon\Carbon::parse($lastRfm)->toFormattedDateString() }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right">
                                            @php $isSelf = auth()->id() === $customer->id; @endphp
                                            @if($isSelf)
                                                <button class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 text-gray-500 dark:text-gray-400 opacity-60 cursor-not-allowed" title="You cannot view as yourself" disabled>
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.618V18a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2h6"/>
                                                    </svg>
                                                    View as user
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('admin.impersonate.start', $customer->id) }}" class="inline">
                                                    @csrf
                                                    <button class="inline-flex items-center px-4 py-2 rounded-lg border border-blue-300 dark:border-blue-600 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-800 dark:to-blue-700 text-blue-700 dark:text-blue-200 hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-700 dark:hover:to-blue-600 transition-all duration-200 shadow-sm hover:shadow-md" title="View as user (read-only)">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.618V18a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2h6"/>
                                                        </svg>
                                                        View as user
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $customers->appends(['q' => $search ?? null])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


