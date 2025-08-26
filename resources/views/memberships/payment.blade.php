<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Complete Payment') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="text-red-800 dark:text-red-200">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Payment Summary -->
            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Complete Your Subscription</h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            You're subscribing to the <strong>{{ $plan['name'] }}</strong> plan for 
                            <strong>£{{ number_format($plan['price'] / 100, 2) }}</strong> per month.
                        </p>
                    </div>

                    <!-- Plan Details -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8">
                        <h3 class="text-xl font-semibold mb-4">Plan Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Plan:</span>
                                <span class="font-medium ml-2">{{ $plan['name'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Price:</span>
                                <span class="font-medium ml-2">£{{ number_format($plan['price'] / 100, 2) }}/month</span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Billing Cycle:</span>
                                <span class="font-medium ml-2">{{ ucfirst($plan['interval']) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Currency:</span>
                                <span class="font-medium ml-2">{{ $plan['currency'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- GoCardless Integration -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-blue-900 dark:text-blue-100">Payment Method</h3>
                        <p class="text-blue-800 dark:text-blue-200 mb-4">
                            We use GoCardless for secure, reliable payment processing. Your payment will be taken via Direct Debit.
                        </p>
                        
                        <!-- GoCardless Payment Form -->
                        <div id="gocardless-payment-form" class="mt-6">
                            <div class="text-center">
                                <div class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                    Secure Payment Processing
                                </div>
                            </div>
                            
                            <!-- Payment Form Placeholder -->
                            <div class="mt-6 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <p class="text-gray-600 dark:text-gray-400 text-center">
                                    GoCardless payment form will be integrated here.<br>
                                    For now, this is a placeholder for the payment flow.
                                </p>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                            <p class="mb-2">
                                By completing this payment, you agree to our 
                                <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Terms of Service</a> 
                                and authorize recurring payments.
                            </p>
                            <p>
                                You can cancel your subscription at any time from your account settings.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center mt-8">
                        <a href="{{ route('memberships.index') }}" 
                           class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Back to Plans
                        </a>
                        
                        <form method="POST" action="{{ route('memberships.subscribe') }}" class="inline">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $planId }}">
                            <button type="submit" 
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Complete Subscription
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
