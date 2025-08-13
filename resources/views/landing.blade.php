<x-guest-layout>
    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-900 shadow rounded p-8">
        <h1 class="text-3xl font-bold mb-3 text-gray-900 dark:text-gray-100">Welcome to {{ config('app.name', 'Xero RFM App') }}</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-2">Connect your Xero and explore invoices with RFM insights.</p>
        @auth
            <p class="text-gray-700 dark:text-gray-200 mb-8">Welcome back, <span class="font-semibold">{{ Auth::user()->name }}</span>.</p>
        @else
            <div class="mb-6"></div>
        @endauth

        @auth
            <div class="flex flex-col gap-3 items-stretch">
                <a href="{{ route('dashboard') }}" class="text-center px-5 py-2.5 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                    Enter dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded border border-gray-300 text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Log out</button>
                </form>
            </div>
        @else
            <div class="flex flex-col gap-3 items-stretch">
                <a href="{{ route('login') }}" class="w-full text-center px-5 py-2.5 rounded border border-indigo-600 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30">Log in</a>
                <a href="{{ route('register') }}" class="w-full text-center px-5 py-2.5 rounded bg-indigo-600 text-white hover:bg-indigo-700">Register</a>
            </div>
        @endauth
    </div>
</x-guest-layout>

