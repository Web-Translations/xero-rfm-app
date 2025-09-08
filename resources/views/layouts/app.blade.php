<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- KaTeX for LaTeX rendering -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css" integrity="sha384-GvrOXuhMATgEsSwCs4smul74iXGOixntILdUW9XmUC6+HX0sLNAK3q71HotJqlAn" crossorigin="anonymous">
        <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js" integrity="sha384-cpW21h6RZv/phavutF+AuVYrr+dA8xD9zs6FwLpaCct6O9ctzYFfFr4dgmgccOTx" crossorigin="anonymous"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" integrity="sha384-+VBxd3r6XgURycqtZ117nYw44OOcIax56Z4dCRWbxyPt0Koah1uHoK0o4+/RRE05" crossorigin="anonymous"></script>

        <style>
            /* KaTeX color follows theme: black in light mode, white in dark mode */
            .katex,
            .katex-display {
                color: #111111;
            }
            @media (prefers-color-scheme: dark) {
                .katex,
                .katex-display {
                    color: #ffffff;
                }
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                @if(isset($impersonation) && $impersonation)
                    <div class="bg-amber-100 border-b border-amber-300 text-amber-900">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                            <div class="text-sm font-medium">
                                You are viewing as <span class="font-semibold">{{ $impersonation['target']->name }}</span>. Changes are disabled.
                                <span class="ml-2 text-amber-700">(You: {{ $impersonation['admin']?->name ?? 'Admin' }})</span>
                            </div>
                            <a href="{{ route('admin.impersonate.stop') }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-amber-200 hover:bg-amber-300 text-amber-900 border border-amber-400 shadow-sm">
                                Exit
                            </a>
                        </div>
                    </div>
                @endif
                {{ $slot }}
            </main>

            <!-- Footer -->
            <x-footer />
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: "$$", right: "$$", display: true},
                        {left: "$", right: "$", display: false},
                        {left: "\\(", right: "\\)", display: false},
                        {left: "\\[", right: "\\]", display: true}
                    ],
                    throwOnError: false
                });
            });
        </script>

        @if(session('impersonation_block'))
            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    var msg = @json(session('impersonation_block'));
                    if (window.showToast) {
                        window.showToast(msg, 'warning');
                        return;
                    }
                    try {
                        var rootId = 'toast-root';
                        var root = document.getElementById(rootId);
                        if (!root) {
                            root = document.createElement('div');
                            root.id = rootId;
                            root.className = 'fixed bottom-6 right-6 z-50 space-y-2';
                            document.body.appendChild(root);
                        }
                        var el = document.createElement('div');
                        el.className = 'max-w-sm bg-amber-100 border border-amber-300 text-amber-900 rounded-lg shadow-lg';
                        el.innerHTML = '<div class="px-4 py-3 flex items-start space-x-3">\
                            <svg class="w-5 h-5 mt-0.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">\
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18.5a6.5 6.5 0 110-13 6.5 6.5 0 010 13z" />\
                            </svg>\
                            <div class="text-sm">' + msg.replace(/</g,'&lt;') + '</div>\
                            <button aria-label="Close" class="ml-2 opacity-70 hover:opacity-100">✕</button>\
                        </div>';
                        var closeBtn = el.querySelector('button');
                        closeBtn.addEventListener('click', function(){ el.remove(); });
                        root.appendChild(el);
                        setTimeout(function(){ if (el && el.parentNode) el.remove(); }, 3500);
                    } catch (e) {
                        console.warn('Fallback toast failed');
                    }
                });
            </script>
        @endif
    </body>
</html>
