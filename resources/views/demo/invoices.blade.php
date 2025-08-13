<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Invoices (last 90 days)</h2>
    </x-slot>

    <div class="p-6">
        @if (session('status'))
            <div class="mb-4 text-green-600">{{ session('status') }}</div>
        @endif

        <div class="flex items-center justify-between">
            <a class="underline text-blue-600" href="{{ route('xero.connect') }}">Reconnect Xero</a>
            <form method="GET" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Days:</label>
                <select name="days" class="border rounded px-2 py-1 text-sm" onchange="this.form.submit()">
                    @foreach ([30,60,90,180,365] as $d)
                        <option value="{{ $d }}" {{ request('days',90)==$d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm border divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="text-left">
                        <th class="p-2 border dark:border-gray-700">Number</th>
                        <th class="p-2 border dark:border-gray-700">Client</th>
                        <th class="p-2 border dark:border-gray-700">Date</th>
                        <th class="p-2 border dark:border-gray-700">Due</th>
                        <th class="p-2 border text-right dark:border-gray-700">Subtotal</th>
                        <th class="p-2 border text-right dark:border-gray-700">Total</th>
                        <th class="p-2 border dark:border-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($invoices as $inv)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="p-2 border dark:border-gray-700 font-mono">{{ $inv->getInvoiceNumber() ?: $inv->getInvoiceID() }}</td>
                        <td class="p-2 border dark:border-gray-700">{{ optional($inv->getContact())->getName() }}</td>
                        <td class="p-2 border dark:border-gray-700">{{ optional($inv->getDate())->format('Y-m-d') ?? optional($inv->getUpdatedDateUtc())->format('Y-m-d') }}</td>
                        <td class="p-2 border dark:border-gray-700">{{ optional($inv->getDueDate())->format('Y-m-d') }}</td>
                        <td class="p-2 border dark:border-gray-700 text-right">{{ number_format((float) $inv->getSubTotal(), 2) }}</td>
                        <td class="p-2 border dark:border-gray-700 text-right">{{ number_format((float) $inv->getTotal(), 2) }} {{ $inv->getCurrencyCode() }}</td>
                        <td class="p-2 border dark:border-gray-700">
                            <span class="px-2 py-1 rounded text-xs {{ $inv->getStatus()==='PAID' ? 'bg-green-100 text-green-800' : ($inv->getStatus()==='AUTHORISED' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $inv->getStatus() }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-2 border dark:border-gray-700" colspan="7">No invoices in the selected window.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

