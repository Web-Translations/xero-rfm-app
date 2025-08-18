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
                        <h3 class="text-xl font-semibold mb-2">Xero Integration</h3>
                        
                        @if($activeConnection)
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    Active: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $activeConnection->org_name ?: 'Unknown Organisation' }}</span>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Token status: 
                                    <span class="{{ $activeConnection->isExpired() ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $activeConnection->isExpired() ? 'Expired' : 'Valid' }}
                                    </span>
                                    <span class="text-gray-500">({{ $activeConnection->expires_at?->diffForHumans() }})</span>
                                </p>
                            </div>
                            <a href="{{ route('organisations.index') }}" class="inline-flex items-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Manage Organisations
                            </a>
                        </div>
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
</x-app-layout>
