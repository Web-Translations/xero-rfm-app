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
                        
                        <!-- Customer Information Form -->
                        <form id="subscription-form" method="POST" action="{{ route('memberships.process-payment') }}" class="mt-6 space-y-6">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $planId }}">
                            
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Personal Information</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="given_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name *</label>
                                        <input type="text" name="given_name" id="given_name" 
                                               value="{{ old('given_name', $existingCustomer->given_name ?? auth()->user()->name) }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('given_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="family_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name *</label>
                                        <input type="text" name="family_name" id="family_name" 
                                               value="{{ old('family_name', $existingCustomer->family_name ?? '') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('family_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address *</label>
                                    <input type="email" name="email" id="email" 
                                           value="{{ old('email', $existingCustomer->email ?? auth()->user()->email) }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name (Optional)</label>
                                    <input type="text" name="company_name" id="company_name" 
                                           value="{{ old('company_name', $existingCustomer->company_name ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('company_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Address Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Billing Address</h4>
                                
                                <div>
                                    <label for="address_line1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address Line 1 *</label>
                                    <input type="text" name="address_line1" id="address_line1" 
                                           value="{{ old('address_line1', $existingCustomer->address_line1 ?? '') }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('address_line1')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="address_line2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address Line 2 (Optional)</label>
                                    <input type="text" name="address_line2" id="address_line2" 
                                           value="{{ old('address_line2', $existingCustomer->address_line2 ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('address_line2')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City *</label>
                                        <input type="text" name="city" id="city" 
                                               value="{{ old('city', $existingCustomer->city ?? '') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('city')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region/County</label>
                                        <input type="text" name="region" id="region" 
                                               value="{{ old('region', $existingCustomer->region ?? '') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('region')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code *</label>
                                        <input type="text" name="postal_code" id="postal_code" 
                                               value="{{ old('postal_code', $existingCustomer->postal_code ?? '') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('postal_code')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="country_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country *</label>
                                    <select name="country_code" id="country_code" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="GB" {{ old('country_code', $existingCustomer->country_code ?? 'GB') === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                        <option value="US" {{ old('country_code', $existingCustomer->country_code ?? '') === 'US' ? 'selected' : '' }}>United States</option>
                                        <option value="CA" {{ old('country_code', $existingCustomer->country_code ?? '') === 'CA' ? 'selected' : '' }}>Canada</option>
                                        <option value="AU" {{ old('country_code', $existingCustomer->country_code ?? '') === 'AU' ? 'selected' : '' }}>Australia</option>
                                        <option value="DE" {{ old('country_code', $existingCustomer->country_code ?? '') === 'DE' ? 'selected' : '' }}>Germany</option>
                                        <option value="FR" {{ old('country_code', $existingCustomer->country_code ?? '') === 'FR' ? 'selected' : '' }}>France</option>
                                    </select>
                                    @error('country_code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                                                    <!-- Payment Information -->
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Payment Method</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Your payment will be processed securely via Direct Debit through GoCardless. 
                                After submitting this form, you'll be redirected to GoCardless to complete your bank account setup.
                            </p>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Secure Direct Debit payment processing
                            </div>
                        </div>

                        <!-- Development Notice -->
                        @if(config('app.env') === 'local')
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-yellow-800 dark:text-yellow-200">
                                    <strong>Development Mode:</strong> GoCardless credentials are required for full payment processing. 
                                    Currently, this will create a customer record and simulate the payment flow.
                                </span>
                            </div>
                        </div>
                        @endif
                        </form>

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
                        
                        <button type="submit" form="subscription-form"
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Continue to Payment Setup
                            </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
