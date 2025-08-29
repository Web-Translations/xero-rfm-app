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

                    <div class="mt-8">
                        <h4 class="text-lg font-semibold mb-3">Recent payment events</h4>
                        @php
                            $paymentEvents = \App\Models\GoCardlessPaymentEvent::orderByDesc('id')->limit(5)->get();
                        @endphp
                        @if($paymentEvents->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No payment events recorded yet.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="text-left text-gray-600 dark:text-gray-400">
                                        <tr>
                                            <th class="py-2 pr-4">Event ID</th>
                                            <th class="py-2 pr-4">Payment ID</th>
                                            <th class="py-2 pr-4">Status</th>
                                            <th class="py-2 pr-4">Charge date</th>
                                            <th class="py-2 pr-4">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-900 dark:text-gray-100">
                                        @foreach($paymentEvents as $e)
                                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                                <td class="py-2 pr-4 font-mono truncate">{{ Str::limit($e->event_id, 12) }}</td>
                                                <td class="py-2 pr-4 font-mono truncate">{{ Str::limit($e->payment_id, 12) }}</td>
                                                <td class="py-2 pr-4">{{ $e->status }}</td>
                                                <td class="py-2 pr-4">{{ optional($e->charge_date)->toFormattedDateString() ?: '—' }}</td>
                                                <td class="py-2 pr-4">@if($e->amount) £{{ number_format($e->amount/100, 2) }} {{ $e->currency ?? 'GBP' }} @else — @endif</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 flex items-center justify-end">
                        @if(($user->subscription_plan ?? 'free') !== 'free')
                            <form method="POST" action="{{ route('memberships.cancel') }}" onsubmit="return confirm('Cancel immediately? This ends access now. Continue?')">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Cancel immediately</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


