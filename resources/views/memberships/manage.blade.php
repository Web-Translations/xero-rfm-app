<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Membership') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white/70 dark:bg-gray-900/80 backdrop-blur overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold">Subscription details</h3>
                        <a href="{{ route('memberships.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Change plan</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Current plan</div>
                            <div class="text-lg font-medium">{{ ucfirst($user->subscription_plan ?? 'free') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                            <div class="text-lg font-medium">{{ $user->subscription_status ?? '—' }}</div>
                        </div>
                        <div class="truncate">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Subscription ID</div>
                            <div class="text-lg font-mono truncate">{{ $user->gocardless_subscription_id ?? '—' }}</div>
                        </div>
                        @php
                            $hasPayment = $nextPayment && (!empty($nextPayment->charge_date) || isset($nextPayment->amount));
                        @endphp
                        @if($hasPayment)
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Next charge date</div>
                                <div class="text-lg font-medium">
                                    @if(!empty($nextPayment->charge_date))
                                        {{ \Illuminate\Support\Carbon::parse($nextPayment->charge_date)->toFormattedDateString() }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Next payment amount</div>
                                <div class="text-lg font-medium">
                                    @if(isset($nextPayment->amount))
                                        £{{ number_format(($nextPayment->amount ?? 0) / 100, 2) }} {{ $nextPayment->currency ?? 'GBP' }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Next payment status</div>
                                <div class="text-lg font-medium">{{ $nextPayment->status ?? '—' }}</div>
                            </div>
                        @else
                            <div class="md:col-span-2 text-sm text-gray-500 dark:text-gray-400">
                                No scheduled payment found yet. This is normal right after signup; the first charge appears here once created by GoCardless.
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 flex items-center justify-end">
                        @if(($user->subscription_plan ?? 'free') !== 'free')
                            <form method="POST" action="{{ route('memberships.cancel') }}" onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Cancel subscription</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


