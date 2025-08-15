<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Organizations') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
                </div>
            @endif

            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold">Connected Organizations</h3>
                        <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                            Connect New Organization
                        </a>
                    </div>

                    @if($organizations->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400 mb-4">No organizations connected yet.</p>
                            <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                                Connect Your First Organization
                            </a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($organizations as $org)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 {{ $org->is_active ? 'bg-white dark:bg-gray-800 border-indigo-300 dark:border-indigo-600 shadow-md' : 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <!-- Organization Header -->
                                            <div class="flex items-center gap-3 mb-3">
                                                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $org->org_name ?: 'Unknown Organization' }}
                                                </h4>
                                                @if($org->is_active)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-700">
                                                        ✓ Active
                                                    </span>
                                                @endif
                                                @if($org->isExpired())
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border border-red-200 dark:border-red-700">
                                                        ⚠ Token Expired
                                                    </span>
                                                @endif
                                            </div>
                                            
                                                                                         <!-- Organization Details -->
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
                                                <form method="POST" action="{{ route('organizations.switch', $org->id) }}" class="inline">
                                                    @csrf
                                                                                                         <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white border-0 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                         </svg>
                                                         Switch to Active
                                                     </button>
                                                </form>
                                            @endif
                                                                                         <form method="POST" action="{{ route('organizations.disconnect', $org->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to disconnect from {{ $org->org_name }}? This will delete all associated data.')">
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

                        <div class="mt-6 p-6 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                About Organization Management
                            </h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>Only one organization can be active at a time</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>Switching organizations will change which data is displayed in Invoices and RFM Analysis</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>Disconnecting an organization will permanently delete all associated data</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 mt-1">•</span>
                                    <span>You can reconnect to the same organization later, but data will need to be re-synced</span>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 