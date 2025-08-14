<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    @php($conn = optional(Auth::user())->xeroConnection)
                    @php( $orgName = $conn?->org_name )

                    @if (!$conn)
                        <h3 class="text-xl font-semibold mb-2">Xero Integration</h3>
                        <p class="text-sm text-gray-400 mb-4">Connect your Xero organisation to sync invoices and power RFM analysis.</p>
                        <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Connect Xero</a>
                    @else
                        <h3 class="text-xl font-semibold mb-2">Xero Integration</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-400">Organisation</dt>
                                <dd class="font-medium">{{ $orgName ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Tenant ID</dt>
                                <dd class="font-mono">{{ $conn->tenant_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Token expires</dt>
                                <dd>{{ $conn->expires_at?->diffForHumans() }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-600/40 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Resync connection</a>
                            @if($conn->expires_at && $conn->expires_at->isPast())
                                <span class="inline-flex items-center px-3 py-1 rounded bg-red-100 text-red-700 text-xs">Token expired — please reconnect</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($conn)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('invoices.index') }}" class="block bg-white/70 dark:bg-gray-900/80 backdrop-blur shadow rounded-lg p-5 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <div class="text-gray-700 dark:text-gray-200 font-medium">Invoices</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Review recent invoices</div>
                </a>
                <a href="{{ route('rfm.index') }}" class="block bg-white/70 dark:bg-gray-900/80 backdrop-blur shadow rounded-lg p-5 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <div class="text-gray-700 dark:text-gray-200 font-medium">RFM Analysis</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Segment clients by behavior</div>
                </a>
                <a href="{{ route('profile.edit') }}" class="block bg-white/70 dark:bg-gray-900/80 backdrop-blur shadow rounded-lg p-5 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <div class="text-gray-700 dark:text-gray-200 font-medium">Profile</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Manage your account</div>
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
