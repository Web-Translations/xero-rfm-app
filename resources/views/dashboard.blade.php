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
                    @php($user = Auth::user())
                    @php($activeConnection = $user->getActiveXeroConnection())
                    @php($allConnections = $user->getAllXeroConnections())

                    @if ($allConnections->isEmpty())
                        <h3 class="text-xl font-semibold mb-2">Xero Integration</h3>
                        <p class="text-sm text-gray-400 mb-4">Connect your Xero organisation to sync invoices and power RFM analysis.</p>
                        <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Connect Xero</a>
                    @else
                        <h3 class="text-xl font-semibold mb-2">Xero Organizations</h3>
                        
                        <!-- Organization Selector -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Active Organization</label>
                            <div class="flex items-center gap-3">
                                <select id="org-selector" class="flex-1 border rounded-md px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($allConnections as $connection)
                                        <option value="{{ $connection->id }}" {{ $connection->is_active ? 'selected' : '' }}>
                                            {{ $connection->org_name ?: 'Unknown Organization' }}
                                            @if($connection->isExpired())
                                                ⚠ (Token Expired)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium transition-colors">Add New</a>
                                <a href="{{ route('organizations.index') }}" class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium transition-colors">Manage</a>
                            </div>
                        </div>

                        @if($activeConnection)
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Active Organisation</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $activeConnection->org_name ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Tenant ID</dt>
                                <dd class="font-mono text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">{{ $activeConnection->tenant_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Token expires</dt>
                                <dd class="text-gray-800 dark:text-gray-200 {{ $activeConnection->isExpired() ? 'text-red-600 dark:text-red-400' : '' }}">
                                    {{ $activeConnection->expires_at?->diffForHumans() }}
                                </dd>
                            </div>
                        </dl>

                        @if($activeConnection->isExpired())
                            <div class="mt-4 p-3 bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <span class="text-red-700 dark:text-red-300 text-sm">Token expired — please reconnect this organization</span>
                            </div>
                        @endif
                        @endif
                    @endif
                </div>
            </div>

            @if($activeConnection)
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

    @if($allConnections->isNotEmpty())
    <script>
        document.getElementById('org-selector').addEventListener('change', function() {
            const connectionId = this.value;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("organizations.switch", ["connection" => ":id"]) }}'.replace(':id', connectionId);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        });
    </script>
    @endif
</x-app-layout>
