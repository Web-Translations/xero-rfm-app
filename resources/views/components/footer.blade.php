<footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <div class="flex items-center space-x-2">
                <x-application-logo class="block h-8 w-auto fill-current text-gray-800 dark:text-gray-200" />
                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">rfm-analysis.io</span>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-6 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('terms') }}" class="hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                    Terms of Service
                </a>
                <a href="{{ route('privacy') }}" class="hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                    Privacy Policy
                </a>
                <span class="hidden sm:inline">•</span>
                <span>&copy; {{ date('Y') }} rfm-analysis.io. All rights reserved.</span>
            </div>
        </div>
    </div>
</footer>
