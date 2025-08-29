<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Payment Successful') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Success Message -->
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-green-800 dark:text-green-200">
                            Payment Successful!
                        </h3>
                        <p class="mt-2 text-green-700 dark:text-green-300">
                            Your subscription has been activated successfully. You now have access to all the features of your chosen plan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-semibold mb-6">Your Subscription Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Current Plan</h4>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ ucfirst(auth()->user()->subscription_plan ?? 'Free') }}
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Status</h4>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Active
                            </span>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Billing Cycle</h4>
                            <p class="text-gray-900 dark:text-gray-100">Monthly</p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Next Payment</h4>
                            <p class="text-gray-900 dark:text-gray-100">
                                {{ now()->addMonth()->format('F j, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-4">What's Next?</h3>
                <ul class="space-y-3 text-blue-800 dark:text-blue-200">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>You can now access all premium features of your plan</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Your subscription will automatically renew each month</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>You can manage your subscription anytime from your account</span>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center">
                <a href="{{ route('dashboard') }}" 
                   class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Go to Dashboard
                </a>
                
                <a href="{{ route('memberships.index') }}" 
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Manage Subscription
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
