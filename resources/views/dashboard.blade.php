<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            Welcome back, {{ Auth::user()->name }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300">
                            Here's what's happening with your business today
                        </p>
                    </div>
                    <div class="hidden md:flex items-center space-x-6">
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current Plan</p>
                            <p class="font-semibold text-gray-900 dark:text-white">Free</p>
                        </div>
                        <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Last Updated</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ now()->format('M j, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Setup Checklist & Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Data Sync Status -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Data Sync Status</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $activeConnection?->org_name ? 'Connected: '.$activeConnection->org_name : 'Not connected' }}</p>
                            @if($lastSyncAt)
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Last invoice sync: {{ $daysSinceSync }} day{{ $daysSinceSync === 1 ? '' : 's' }} ago</p>
                            @else
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">No invoice sync yet</p>
                            @endif
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Platform Health -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Platform Health</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $platformStatus['platform_health'] ?? 'Excellent' }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400 mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                All systems operational
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Next Sync -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Next Sync</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $platformStatus['next_sync'] ?? '2:30 PM' }}</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                In 45 minutes
                            </p>
                        </div>
                        <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Setup Checklist Card -->
            <div class="mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Setup Checklist</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Step 1: Connect Xero</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Connect your Xero account to begin.</div>
                            </div>
                            <div class="ml-4">
                                @if($hasConnection)
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Done</span>
                                @else
                                    <a href="{{ route('xero.connect') }}" class="inline-flex items-center px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700">Connect</a>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Step 2: Sync invoices (all time)</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Import your invoices to get started.</div>
                            </div>
                            <div class="ml-4">
                                @if($hasInvoices)
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Done</span>
                                @else
                                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700">Sync invoices</a>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Step 3: Calculate RFM scores</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">See the RFM scoreboard along with reports and analysis.</div>
                            </div>
                            <div class="ml-4">
                                @if($hasRfm)
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Done</span>
                                @else
                                    <a href="{{ route('rfm.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700">Calculate</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($needsRecalc)
                        <div class="mt-4 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20 text-sm text-yellow-800 dark:text-yellow-200">
                            Your RFM settings or invoice exclusions changed. Recalculate RFM to apply the changes.
                            <a href="{{ route('rfm.index') }}" class="ml-2 underline">Recalculate RFM</a>
                        </div>
                    @endif

                    @if($lastSyncAt)
                        <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">Last invoice sync: {{ $lastSyncAt->format('M j, Y g:i A') }} ({{ $daysSinceSync }} day{{ $daysSinceSync === 1 ? '' : 's' }} ago)</div>
                    @endif
                </div>
            </div>

            <!-- Onboarding Buttons -->
            <div class="mb-8 flex flex-wrap gap-4">
                <div id="tour-restart-section" class="hidden">
                    <button onclick="restartDashboardTour()" class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 rounded-lg text-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Restart Onboarding Tour
                    </button>
                </div>
                
                @if(config('app.debug'))
                <button onclick="restartDashboardTour()" class="inline-flex items-center px-4 py-2 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900 dark:hover:bg-yellow-800 text-yellow-700 dark:text-yellow-300 rounded-lg text-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Test Onboarding (Dev)
                </button>
                @endif
            </div>
                     
                                 <!-- About Us Banner -->
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold mb-3">Advanced Customer Analytics Platform</h2>
                        <p class="text-blue-100 mb-4 text-lg">
                            We transform your Xero data into actionable customer insights using advanced RFM analysis, 
                            helping you identify your most valuable customers, predict churn, and optimize your marketing strategies.
                        </p>
                        <div class="bg-white/10 rounded-lg p-4 border border-white/20">
                            <h3 class="font-semibold mb-2">🚀 Custom Features Available</h3>
                            <p class="text-blue-100 text-sm">
                                Need a specific graph or analysis tailored to your business? Pro+ members can request custom features 
                                and we'll build them into your subscription. Contact us to discuss your requirements!
                            </p>
                        </div>
                    </div>
                    <div class="hidden lg:flex items-center space-x-6 ml-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold">RFM</div>
                            <div class="text-sm text-blue-100">Analysis</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">AI</div>
                            <div class="text-sm text-blue-100">Insights</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">Custom</div>
                            <div class="text-sm text-blue-100">Features</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Quick Actions -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Quick Actions</h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Get started with your analysis</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="{{ route('rfm.analysis.index') }}" class="group">
                                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-xl p-6 hover:shadow-lg transition-all duration-200 group-hover:scale-105">
                                    <div class="flex items-center mb-4">
                                        <div class="p-3 bg-blue-500 rounded-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">RFM Analysis</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Customer behavior insights</p>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm">Analyze customer behavior and identify growth opportunities with advanced RFM metrics.</p>
                                </div>
                            </a>

                            <a href="{{ route('memberships.index') }}" class="group">
                                <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-xl p-6 hover:shadow-lg transition-all duration-200 group-hover:scale-105">
                                    <div class="flex items-center mb-4">
                                        <div class="p-3 bg-green-500 rounded-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upgrade Plan</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Unlock premium features</p>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm">Unlock advanced features, AI insights, and deeper analytics with our Pro plans.</p>
                                </div>
                            </a>

                            <a href="#" class="group">
                                <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-xl p-6 hover:shadow-lg transition-all duration-200 group-hover:scale-105">
                                    <div class="flex items-center mb-4">
                                        <div class="p-3 bg-purple-500 rounded-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Help & Support</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Get assistance</p>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm">Get help with setup, learn best practices, and access our knowledge base.</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Custom Features Banner -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700 mt-8">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">🎯 Custom Features Available</h3>
                                <p class="text-gray-700 dark:text-gray-300 mb-4">
                                    Pro+ members can request custom graphs, analyses, and features tailored specifically to their business needs.
                                </p>
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Custom dashboards</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Industry-specific metrics</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Integration with other tools</span>
                                    </div>
                                </div>
                            </div>
                            <button onclick="openCustomFeaturesModal()" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 ml-6">
                                Enquire About Custom Features
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">


                    <!-- Feature Highlights -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Feature Highlights</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg border border-orange-200 dark:border-orange-700">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-orange-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Customer Segmentation</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Identify high-value customers</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-1 rounded-full">Free</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 rounded-lg border border-teal-200 dark:border-teal-700">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-teal-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Interactive Charts</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Visualize customer trends</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 px-2 py-1 rounded-full">Free</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 rounded-lg border border-violet-200 dark:border-violet-700">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-violet-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">AI Recommendations</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Smart business insights</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-violet-100 dark:bg-violet-900 text-violet-800 dark:text-violet-200 px-2 py-1 rounded-full">Pro+</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-lg border border-amber-200 dark:border-amber-700">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-amber-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Custom Features</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Tailored to your business</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 px-2 py-1 rounded-full">Pro+</span>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Status -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Platform Status</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">System Uptime</span>
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $platformStatus['system_uptime'] ?? '99.9%' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Data Security</span>
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $platformStatus['data_security'] ?? 'Enterprise' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Support Response</span>
                                <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">{{ $platformStatus['support_response'] ?? '< 2 hours' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pro Tip -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-6 border border-green-200 dark:border-green-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">💡 Pro Tip</h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                            Connect your Xero account to unlock powerful RFM analysis that can help you identify your most valuable customers and growth opportunities.
                        </p>
                        <button class="text-sm text-green-600 dark:text-green-400 font-medium hover:underline">
                            Learn more about RFM analysis →
                        </button>
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Onboarding System -->
    <div id="onboarding-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-2xl w-full mx-4 relative">
                <!-- Tour Content -->
                <div id="tour-content" class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4" id="tour-title">Welcome to Your Business Analytics Platform!</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-lg" id="tour-description">
                            Let's get you set up and show you how to unlock the full potential of your customer data.
                        </p>
                    </div>
                    
                    <!-- Tour Progress -->
                    <div class="mb-8">
                        <div class="flex justify-center space-x-3">
                            <div class="w-3 h-3 bg-blue-600 rounded-full tour-step" data-step="1"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="2"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="3"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="4"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="5"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="6"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="7"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="8"></div>
                            <div class="w-3 h-3 bg-gray-300 rounded-full tour-step" data-step="9"></div>
                        </div>
                    </div>
                    
                    <!-- Feature Preview -->
                    <div id="feature-preview" class="mb-8 hidden">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <div id="feature-icon" class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <h4 id="feature-title" class="ml-3 text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Analysis</h4>
                            </div>
                            <p id="feature-description" class="text-gray-600 dark:text-gray-400">
                                Discover powerful insights about your customers with our advanced RFM (Recency, Frequency, Monetary) analysis tools.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Tour Actions -->
                    <div class="flex justify-between items-center">
                        <button id="tour-skip" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm font-medium">
                            Skip Tour
                        </button>
                        <div class="flex space-x-4">
                            <button id="tour-prev" class="px-6 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-sm font-medium hidden">
                                Previous
                            </button>
                            <button id="tour-action" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 hidden">
                                Connect to Xero
                            </button>
                            <button id="tour-next" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Close button -->
                <button id="tour-close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Comprehensive Onboarding System
        class DashboardOnboarding {
            constructor() {
                this.currentStep = 0;
                this.steps = [
                    {
                        title: "Welcome to Your Business Analytics Platform!",
                        description: "Let's walk through how our platform works and get you set up for success with your customer data analysis.",
                        feature: null,
                        action: null
                    },
                    {
                        title: "Understanding the Invoices Page",
                        description: "The invoices page is where everything starts. This is where you'll see all your Xero invoice data and manage the foundation of your RFM analysis.",
                        feature: {
                            title: "Invoices Management",
                            description: "View and manage all your Xero invoices. This is the data source for all your customer analytics.",
                            icon: `<svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>`
                        },
                        action: null
                    },
                    {
                        title: "Step 1: Sync Your Invoices",
                        description: "First, you need to sync your invoices from Xero. This imports all your customer transaction data into our system for analysis.",
                        feature: {
                            title: "Data Sync",
                            description: "Connect your Xero account and sync all invoice data to build your customer database.",
                            icon: `<svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>`
                        },
                        action: {
                            text: "Go to Invoices",
                            url: "{{ route('invoices.index') }}",
                            type: "secondary",
                            isExternal: true
                        }
                    },
                    {
                        title: "Step 2: Calculate RFM Scores",
                        description: "Once your invoices are synced, our system automatically calculates RFM scores for each customer. This process analyzes Recency, Frequency, and Monetary values.",
                        feature: {
                            title: "RFM Calculation",
                            description: "Our system automatically processes your invoice data to calculate customer RFM scores.",
                            icon: `<svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>`
                        },
                        action: {
                            text: "View RFM Scores",
                            url: "{{ route('rfm.index') }}",
                            type: "secondary",
                            isExternal: true
                        }
                    },
                    {
                        title: "Step 3: Generate Reports",
                        description: "With RFM scores calculated, you can generate comprehensive reports that provide insights into customer segments and behavior patterns.",
                        feature: {
                            title: "Report Generation",
                            description: "Create detailed reports based on your RFM analysis to understand customer segments.",
                            icon: `<svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>`
                        },
                        action: {
                            text: "Generate Report",
                            url: "{{ route('rfm.reports.index') }}",
                            type: "secondary",
                            isExternal: true
                        }
                    },
                    {
                        title: "Step 4: Explore RFM Analysis Charts",
                        description: "Now your RFM analysis charts should work perfectly! Explore customer trends, segmentations, and insights to optimize your business strategy.",
                        feature: {
                            title: "Interactive Charts",
                            description: "Explore your customer data through interactive charts and visualizations.",
                            icon: `<svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>`
                        },
                        action: {
                            text: "View RFM Analysis",
                            url: "{{ route('rfm.analysis.index') }}",
                            type: "primary",
                            isExternal: true
                        }
                    },
                    {
                        title: "Troubleshooting & Support",
                        description: "If you encounter any issues with the charts or analysis, don't worry! Our support team is here to help. You can also enquire about custom features.",
                        feature: {
                            title: "Support Available",
                            description: "Get help when you need it. Our team is ready to assist with any questions or issues.",
                            icon: `<svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path>
                            </svg>`
                        },
                        action: null
                    },
                    {
                        title: "Upgrade Your Experience",
                        description: "Unlock advanced features with our Pro and Pro+ memberships. Get AI insights, deeper analytics, and custom features tailored to your business needs.",
                        feature: {
                            title: "Premium Features",
                            description: "Upgrade to Pro or Pro+ for advanced analytics, AI insights, and custom features.",
                            icon: `<svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>`
                        },
                        action: {
                            text: "View Plans",
                            url: "{{ route('memberships.index') }}",
                            type: "primary",
                            isExternal: true
                        }
                    },
                    {
                        title: "You're All Set!",
                        description: "Congratulations! You now understand how our platform works. Start exploring your customer data and discover insights that will help grow your business.",
                        feature: {
                            title: "Success!",
                            description: "Your account is ready. Follow the steps to sync data and start analyzing your customers.",
                            icon: `<svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>`
                        },
                        action: {
                            text: "Start Analysis",
                            url: "{{ route('rfm.analysis.index') }}",
                            type: "primary",
                            isExternal: true
                        }
                    }
                ];
                
                this.init();
            }
            
            init() {
                this.bindEvents();
                this.checkIfNewUser();
            }
            
            bindEvents() {
                document.getElementById('tour-next').addEventListener('click', () => this.nextStep());
                document.getElementById('tour-prev').addEventListener('click', () => this.prevStep());
                document.getElementById('tour-action').addEventListener('click', () => this.handleAction());
                document.getElementById('tour-skip').addEventListener('click', () => this.endTour());
                document.getElementById('tour-close').addEventListener('click', () => this.endTour());
                
                // Close on overlay click
                document.getElementById('onboarding-overlay').addEventListener('click', (e) => {
                    if (e.target.id === 'onboarding-overlay') {
                        this.endTour();
                    }
                });
            }
            
            checkIfNewUser() {
                // Check if user is new (first time visiting dashboard)
                const hasSeenTour = localStorage.getItem('dashboard_tour_completed');
                const isNewUser = !hasSeenTour;
                
                if (isNewUser) {
                    // Show tour after a short delay
                    setTimeout(() => {
                        this.startTour();
                    }, 1500);
                } else {
                    // Show restart button for returning users
                    this.showRestartButton();
                }
            }
            
            showRestartButton() {
                const restartSection = document.getElementById('tour-restart-section');
                if (restartSection) {
                    restartSection.classList.remove('hidden');
                }
            }
            
            startTour() {
                this.currentStep = 0;
                this.showStep();
                document.getElementById('onboarding-overlay').classList.remove('hidden');
            }
            
            showStep() {
                const step = this.steps[this.currentStep];
                const titleEl = document.getElementById('tour-title');
                const descEl = document.getElementById('tour-description');
                const nextBtn = document.getElementById('tour-next');
                const prevBtn = document.getElementById('tour-prev');
                const actionBtn = document.getElementById('tour-action');
                const featurePreview = document.getElementById('feature-preview');
                
                // Update content
                titleEl.textContent = step.title;
                descEl.textContent = step.description;
                
                // Update progress dots
                document.querySelectorAll('.tour-step').forEach((dot, index) => {
                    if (index <= this.currentStep) {
                        dot.classList.remove('bg-gray-300');
                        dot.classList.add('bg-blue-600');
                    } else {
                        dot.classList.remove('bg-blue-600');
                        dot.classList.add('bg-gray-300');
                    }
                });
                
                // Update buttons
                if (this.currentStep === 0) {
                    prevBtn.classList.add('hidden');
                } else {
                    prevBtn.classList.remove('hidden');
                }
                
                if (this.currentStep === this.steps.length - 1) {
                    nextBtn.textContent = 'Finish';
                } else {
                    nextBtn.textContent = 'Next';
                }
                
                // Show/hide feature preview
                if (step.feature) {
                    featurePreview.classList.remove('hidden');
                    document.getElementById('feature-title').textContent = step.feature.title;
                    document.getElementById('feature-description').textContent = step.feature.description;
                    document.getElementById('feature-icon').innerHTML = step.feature.icon;
                } else {
                    featurePreview.classList.add('hidden');
                }
                
                // Handle 3-button layout for steps with external actions
                if (step.action && step.action.isExternal) {
                    // Show 3 buttons: Previous, Action, Next
                    actionBtn.classList.remove('hidden');
                    actionBtn.textContent = step.action.text;
                    actionBtn.onclick = () => this.handleAction();
                    
                    // Set action button color based on type
                    if (step.action.type === 'primary') {
                        actionBtn.className = 'px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105';
                    } else {
                        actionBtn.className = 'px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105';
                    }
                    
                    // Reset next button to normal behavior
                    nextBtn.onclick = () => this.nextStep();
                } else {
                    // Hide action button for steps without external actions
                    actionBtn.classList.add('hidden');
                    
                    // Update next button for steps with internal actions
                    if (step.action) {
                        nextBtn.textContent = step.action.text;
                        nextBtn.onclick = () => {
                            if (step.action.url && step.action.url !== '#') {
                                window.location.href = step.action.url;
                            } else {
                                this.nextStep();
                            }
                        };
                    } else {
                        nextBtn.onclick = () => this.nextStep();
                    }
                }
            }
            
            nextStep() {
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                    this.showStep();
                } else {
                    this.endTour();
                }
            }
            
            prevStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                    this.showStep();
                }
            }
            
            handleAction() {
                const step = this.steps[this.currentStep];
                if (step.action && step.action.url) {
                    // Open action in new tab/window
                    window.open(step.action.url, '_blank');
                    
                    // Show appropriate message based on action type
                    if (this.currentStep === 1) {
                        // Xero connection
                        this.showConnectionMessage();
                    } else if (this.currentStep === 3) {
                        // View plans
                        this.showPlansMessage();
                    } else {
                        // General action
                        this.showActionMessage(step.action.text);
                    }
                }
            }
            
            showConnectionMessage() {
                this.showActionMessage('Xero connection opened!', 'You can continue the tour while connecting.');
            }
            
            showPlansMessage() {
                this.showActionMessage('Plans page opened!', 'Check out our Pro and Pro+ features.');
            }
            
            showActionMessage(title, subtitle = 'You can continue the tour.') {
                // Create a subtle notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
                notification.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">${title}</p>
                            <p class="text-sm opacity-90">${subtitle}</p>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Remove after 4 seconds
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 4000);
            }
            
            endTour() {
                document.getElementById('onboarding-overlay').classList.add('hidden');
                
                // Mark tour as completed
                localStorage.setItem('dashboard_tour_completed', 'true');
                
                // Show welcome message
                this.showWelcomeMessage();
            }
            
            showWelcomeMessage() {
                // Create a subtle welcome notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-gradient-to-r from-green-500 to-blue-600 text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
                notification.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">Welcome aboard!</p>
                            <p class="text-sm opacity-90">Start exploring your business analytics platform.</p>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 5000);
            }
        }
        
        // Initialize tour when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            window.dashboardOnboarding = new DashboardOnboarding();
        });
        
        // Global function to restart tour (for testing)
        function restartDashboardTour() {
            if (window.dashboardOnboarding) {
                localStorage.removeItem('dashboard_tour_completed');
                window.dashboardOnboarding.startTour();
            }
        }
    </script>

    <!-- Custom Features Enquiry Modal -->
    <div id="customFeaturesModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Custom Features Enquiry</h3>
                    <button onclick="closeCustomFeaturesModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="customFeaturesForm" class="space-y-4">
                    <div>
                        <label for="enquiryEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Email:</label>
                        <input type="email" id="enquiryEmail" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="your@email.com">
                    </div>
                    
                    <div>
                        <label for="enquiryMessage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Enquiry:</label>
                        <textarea id="enquiryMessage" name="message" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                  placeholder="Tell us about the custom features you need..."></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" onclick="closeCustomFeaturesModal()" 
                                class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                            Send Enquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCustomFeaturesModal() {
            document.getElementById('customFeaturesModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCustomFeaturesModal() {
            document.getElementById('customFeaturesModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('customFeaturesModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCustomFeaturesModal();
            }
        });

        // Handle form submission
        document.getElementById('customFeaturesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('enquiryEmail').value;
            const message = document.getElementById('enquiryMessage').value;
            
            // Here you would typically send this to your backend
            console.log('Custom Features Enquiry:', { email, message });
            
            // Show success message
            alert('Thank you for your enquiry! We\'ll get back to you soon.');
            
            // Close modal and reset form
            closeCustomFeaturesModal();
            this.reset();
        });
    </script>
</x-app-layout>
