<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Memberships') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
                </div>
            @endif

            <!-- Header Section -->
            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Choose Your Plan</h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                            We want to use GoCardless to allow customers to manage their subscription level. 
                            Choose the plan that best fits your business needs.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subscription Tiers -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Free Tier -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border-2 {{ $currentPlan === 'free' ? 'border-blue-500' : 'border-gray-200 dark:border-gray-700' }} overflow-hidden">
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Free</h3>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">Perfect for getting started</p>
                        </div>

                        <div class="text-center mb-8">
                            <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">£0</span>
                            <span class="text-gray-600 dark:text-gray-400">/month</span>
                        </div>

                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Analysis reports</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Insights</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Recommendations for improvements</span>
                            </li>
                        </ul>

                        @if($currentPlan === 'free')
                            <button class="w-full py-3 px-4 rounded-lg border-2 border-blue-500 bg-blue-50 text-blue-700 font-medium cursor-default">
                                Current Plan
                            </button>
                        @else
                            <form method="POST" action="{{ route('memberships.subscribe') }}" class="w-full">
                                @csrf
                                <input type="hidden" name="plan" value="free">
                                <button type="submit" class="w-full py-3 px-4 rounded-lg border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Switch to Free
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Pro Tier -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border-2 {{ $currentPlan === 'pro' ? 'border-blue-500' : 'border-gray-200 dark:border-gray-700' }} overflow-hidden relative">
                    @if($currentPlan === 'pro')
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-medium">Current Plan</span>
                        </div>
                    @endif
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pro</h3>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">For growing businesses</p>
                        </div>

                        <div class="text-center mb-8">
                            <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">£5.99</span>
                            <span class="text-gray-600 dark:text-gray-400">/month</span>
                        </div>

                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Everything in Free</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Deeper Insights</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Enhanced Recommendations</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">1 additional user</span>
                            </li>
                        </ul>

                                                    @if($currentPlan === 'pro')
                                <button class="w-full py-3 px-4 rounded-lg bg-blue-500 text-white font-medium cursor-default">
                                    Current Plan
                                </button>
                            @else
                                <form method="POST" action="{{ route('memberships.subscribe') }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="plan" value="pro">
                                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                                        {{ $currentPlan === 'free' ? 'Upgrade to Pro' : 'Switch to Pro' }}
                                    </button>
                                </form>
                            @endif
                    </div>
                </div>

                <!-- Pro+ Tier -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl border-2 {{ $currentPlan === 'pro_plus' ? 'border-blue-500' : 'border-gray-200 dark:border-gray-700' }} overflow-hidden relative">
                    @if($currentPlan === 'pro_plus')
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-medium">Current Plan</span>
                        </div>
                    @endif
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pro+</h3>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">For advanced businesses</p>
                        </div>

                        <div class="text-center mb-8">
                            <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">£11.99</span>
                            <span class="text-gray-600 dark:text-gray-400">/month</span>
                        </div>

                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Everything in Pro</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">AI insights</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">AI recommendations</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">AI Chat feature for interactive analysis</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Unlimited additional users</span>
                            </li>
                        </ul>

                                                    @if($currentPlan === 'pro_plus')
                                <button class="w-full py-3 px-4 rounded-lg bg-purple-500 text-white font-medium cursor-default">
                                    Current Plan
                                </button>
                            @else
                                <form method="POST" action="{{ route('memberships.subscribe') }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="plan" value="pro_plus">
                                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-purple-600 text-white font-medium hover:bg-purple-700 transition-colors">
                                        {{ $currentPlan === 'free' ? 'Upgrade to Pro+' : 'Switch to Pro+' }}
                                    </button>
                                </form>
                            @endif
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-xl font-semibold mb-4">Customer Management</h3>
                            <ul class="space-y-3 text-gray-600 dark:text-gray-400">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Customers should be able to choose their package as part of the onboarding.</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Customers should be able to change their package from within the app.</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold mb-4">Payment Integration</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                All subscriptions are managed through GoCardless for secure, reliable payment processing.
                            </p>
                            <div class="flex items-center space-x-2">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Secure payment processing</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancel Subscription Section -->
                @if($currentPlan !== 'free')
                    <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                            <h3 class="text-xl font-semibold mb-4">Manage Subscription</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">
                                You're currently on the <strong>{{ ucfirst($currentPlan) }}</strong> plan. 
                                You can cancel your subscription at any time.
                            </p>
                            
                            <form method="POST" action="{{ route('memberships.cancel') }}" 
                                  onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
                                @csrf
                                <button type="submit" 
                                        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    Cancel Subscription
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
