<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Organisations') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
                </div>
            @endif

            <!-- Organisation Management Header -->
            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold">Connected Organisations</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage your Xero organisation connections and tokens</p>
                        </div>
                        <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Connect New Organisation
                        </a>
                    </div>

                    @if($organisations->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">No organisations connected yet.</p>
                            <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                                Connect Your First Organisation
                            </a>
                        </div>
                    @else
                        <!-- Active Organisation with Enhanced Token Management -->
                        @if($activeOrg)
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold mb-4">Active Organisation</h4>
                            
                            <!-- Enhanced Token Management Section -->
                            <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-blue-200 dark:border-gray-600">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $activeOrg->org_name ?: 'Unknown Organisation' }}</h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            Your Xero access token automatically expires every 30 minutes for security. 
                                            You can refresh it manually or reconnect if needed.
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div id="status-indicator" class="w-3 h-3 rounded-full {{ $activeOrg->isExpired() ? 'bg-red-500' : 'bg-green-500' }}"></div>
                                        <span id="status-text" class="text-sm font-medium {{ $activeOrg->isExpired() ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            {{ $activeOrg->isExpired() ? 'Expired' : 'Active' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Organisation Details -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <h6 class="font-medium text-gray-900 dark:text-gray-100">Organisation</h6>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            Name: <span class="font-semibold">{{ $activeOrg->org_name ?: 'Unknown' }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Tenant ID: <span class="font-mono">{{ $activeOrg->tenant_id }}</span>
                                        </p>
                                    </div>

                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <h6 class="font-medium text-gray-900 dark:text-gray-100">Access Token</h6>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            Expires: <span id="expires-time" class="font-mono">{{ $activeOrg->expires_at?->format('H:i:s') }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Valid for 30 minutes from creation
                                        </p>
                                    </div>

                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <h6 class="font-medium text-gray-900 dark:text-gray-100">Refresh Token</h6>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            Expires: <span class="font-mono">{{ $activeOrg->created_at->addDays(60)->format('M j, Y') }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Valid for 60 days from connection
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-3">
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button id="refresh-token-btn" onclick="refreshToken()" 
                                                class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                                >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <span id="refresh-btn-text">Refresh Access Token</span>
                                        </button>
                                        
                                        <button id="reconnect-btn" onclick="reconnectToXero()" 
                                                class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                            </svg>
                                            Reconnect to Xero
                                        </button>
                                    </div>
                                    
                                    <!-- Status Messages -->
                                    <div id="token-message" class="text-sm font-medium"></div>
                                    
                                    <!-- Help Text -->
                                    <div class="bg-blue-100 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                        <div class="flex">
                                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                                <p class="font-medium mb-1">How it works:</p>
                                                <ul class="space-y-1 text-xs">
                                                    <li>• <strong>Auto-refresh:</strong> The system automatically refreshes tokens for the active organisation when needed</li>
                                                    <li>• <strong>Manual Refresh:</strong> You can manually refresh tokens anytime using the button above</li>
                                                    <li>• <strong>Reconnect:</strong> Required after 60 days or if refresh fails - redirects to Xero login</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- All Organisations List -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold">All Organisations</h4>
                            @foreach($organisations as $org)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 {{ $org->is_active ? 'bg-white dark:bg-gray-800 border-indigo-300 dark:border-indigo-600 shadow-md' : 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <!-- Organisation Header -->
                                            <div class="flex items-center gap-3 mb-3">
                                                <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $org->org_name ?: 'Unknown Organisation' }}
                                                </h5>
                                                @if($org->is_active)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-700">
                                                        Active
                                                    </span>
                                                @endif
                                                @if($org->isExpired())
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-200 dark:border-red-700">
                                                        Token Expired
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <!-- Organisation Details -->
                                             <div class="space-y-2">
                                                 <div class="flex items-center gap-2 text-sm">
                                                     <span class="text-gray-700 dark:text-gray-300 font-medium">Tenant ID:</span>
                                                     <span class="font-mono text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs border border-gray-300 dark:border-gray-600 shadow-sm">{{ $org->tenant_id }}</span>
                                                 </div>
                                                 @if($org->expires_at)
                                                     <div class="flex items-center gap-2 text-sm">
                                                         <span class="text-gray-700 dark:text-gray-300 font-medium">Token expires:</span>
                                                         <span class="text-gray-900 dark:text-gray-100 {{ $org->isExpired() ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                                             {{ $org->expires_at->diffForHumans() }}
                                                         </span>
                                                     </div>
                                                 @endif
                                             </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex items-center gap-2 ml-4">
                                            @if(!$org->is_active)
                                                <form method="POST" action="{{ route('organisations.switch', $org->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white border-0 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Switch to Active
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('organisations.disconnect', $org->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to disconnect from {{ $org->org_name }}? This will delete all associated data.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white border-0 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Disconnect
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Help Section -->
                        <div class="mt-8 bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                About Organisation Management
                            </h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>Only one organisation can be active at a time</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>Switching organisations will change which data is displayed in Invoices and RFM Analysis</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>Disconnecting an organisation will permanently delete all associated data</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>You can reconnect to the same organisation later, but data will need to be re-synced</span>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($organisations->isNotEmpty() && $activeOrg)
    <script>
        // Token management functions
        function refreshToken() {
            console.log('Refresh token function called');
            const btn = document.getElementById('refresh-token-btn');
            const btnText = document.getElementById('refresh-btn-text');
            const message = document.getElementById('token-message');
            const statusIndicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('status-text');
            
            console.log('Button disabled state:', btn.disabled);
            console.log('Button element:', btn);
            
            // Disable button and show loading state
            btn.disabled = true;
            btnText.textContent = 'Refreshing...';
            message.innerHTML = '<span class="text-blue-600">Refreshing your access token...</span>';
            
            console.log('Making fetch request to:', '{{ route("token.refresh") }}');
            console.log('CSRF Token:', '{{ csrf_token() }}');
            fetch('{{ route("token.refresh") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                console.log('Response ok:', response.ok);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.status === 'success') {
                    message.innerHTML = '<span class="text-green-600">Token refreshed successfully! Refreshing page...</span>';
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-green-500';
                    statusText.textContent = 'Active';
                    statusText.className = 'text-sm font-medium text-green-600 dark:text-green-400';
                    
                    // Reload page after 2 seconds to show updated status
                    setTimeout(() => location.reload(), 2000);
                } else {
                    message.innerHTML = '<span class="text-red-600">' + (data.message || 'Failed to refresh token') + '</span>';
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                    statusText.textContent = 'Error';
                    statusText.className = 'text-sm font-medium text-red-600 dark:text-red-400';
                }
            })
            .catch(error => {
                console.error('Refresh error:', error);
                console.error('Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                message.innerHTML = '<span class="text-red-600">Network error. Please try again or reconnect to Xero.</span>';
                statusIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                statusText.textContent = 'Error';
                statusText.className = 'text-sm font-medium text-red-600 dark:text-red-400';
            })
            .finally(() => {
                // Re-enable button and restore text
                btn.disabled = false;
                btnText.textContent = 'Refresh Access Token';
            });
        }

        function reconnectToXero() {
            const message = document.getElementById('token-message');
            const statusIndicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('status-text');
            
            // Update UI immediately
            message.innerHTML = '<span class="text-purple-600">Redirecting to Xero...</span>';
            statusIndicator.className = 'w-3 h-3 rounded-full bg-purple-500';
            statusText.textContent = 'Redirecting';
            statusText.className = 'text-sm font-medium text-purple-600 dark:text-purple-400';
            
            // Use fetch to handle the reconnect and then redirect
            fetch('{{ route("token.reconnect") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (response.redirected) {
                    // If the response is a redirect, follow it
                    window.location.href = response.url;
                } else {
                    // If not redirected, assume we need to go to Xero connect
                    window.location.href = '{{ route("xero.connect") }}';
                }
            })
            .catch(error => {
                console.error('Reconnect error:', error);
                // Fallback to direct redirect
                window.location.href = '{{ route("xero.connect") }}';
            });
        }

        // Enhanced auto-check token status every 30 seconds
        function updateTokenStatus() {
            fetch('{{ route("token.status") }}')
            .then(response => response.json())
            .then(data => {
                const refreshBtn = document.getElementById('refresh-token-btn');
                const statusIndicator = document.getElementById('status-indicator');
                const statusText = document.getElementById('status-text');
                const expiresTime = document.getElementById('expires-time');
                
                // Update main status display
                if (data.status === 'valid') {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-green-500';
                    statusText.textContent = 'Active';
                    statusText.className = 'text-sm font-medium text-green-600 dark:text-green-400';
                    refreshBtn.disabled = true;
                } else if (data.status === 'access_token_expired') {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                    statusText.textContent = 'Expired';
                    statusText.className = 'text-sm font-medium text-red-600 dark:text-red-400';
                    refreshBtn.disabled = false;
                } else if (data.status === 'refresh_token_expired') {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-orange-500';
                    statusText.textContent = 'Needs Reconnect';
                    statusText.className = 'text-sm font-medium text-orange-600 dark:text-orange-400';
                    refreshBtn.disabled = true;
                }
                
                // Update expiry time display
                if (data.expires_at) {
                    const expiresDate = new Date(data.expires_at * 1000);
                    
                    // Update the time display
                    expiresTime.textContent = expiresDate.toLocaleTimeString('en-GB', { 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit' 
                    });
                }
            })
            .catch(error => {
                console.log('Failed to check token status:', error);
            });
        }

        // Initial status check and then every 30 seconds
        // updateTokenStatus(); // Temporarily disabled for testing
        // setInterval(updateTokenStatus, 30000); // Temporarily disabled for testing
    </script>
    @endif
</x-app-layout> 