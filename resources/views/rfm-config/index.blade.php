<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      RFM Configuration
    </h2>
  </x-slot>

  {{-- KaTeX for beautiful math rendering --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"
          onload="renderMathInElement(document.body, {delimiters:[{left:'$$',right:'$$',display:true},{left:'\\(',right:'\\)',display:false},{left:'\\[',right:'\\]',display:true}]});">
  </script>
  <style>
    /* Ensure KaTeX text is readable in light mode; keep default in dark mode */
    html:not(.dark) .katex, html:not(.dark) .katex * { color: #111 !important; }
  </style>

  @vite(['resources/js/rfm-config.js'])

  <div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Intro Card -->
      <div class="mb-6 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-800">
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 dark:bg-indigo-900 rounded-full mb-3">
            <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
            </svg>
          </div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">RFM Configuration</h1>
          <p class="text-gray-600 dark:text-gray-400">Adjust R, F, and M settings. After saving, recalculate RFM scores to apply changes.</p>
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">

          @if (session('status'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
              <div class="text-green-800 dark:text-green-200">{{ session('status') }}</div>
            </div>
          @endif

          @if ($errors->any())
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
              <div class="text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          @endif

          @if(isset($hasInvoices) && !$hasInvoices)
            <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
              <div class="text-yellow-800 dark:text-yellow-200">
                You need invoice data before you can calculate RFM scores. Please sync invoices first.
              </div>
            </div>
          @endif

          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">RFM Scoring Configuration</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Configure how Recency (R), Frequency (F), and Monetary (M) scores are calculated. All scores are capped between 0 and 10.
            </p>
          </div>

          <form id="rfm-config-form" method="POST" action="{{ route('rfm.config.store') }}" class="space-y-8">
            @csrf

            <!-- Recency (R) -->
            <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg">
              <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3">
                  <span class="text-blue-600 dark:text-blue-400 font-semibold">R</span>
                </div>
                <h4 class="text-lg font-semibold">Recency (R)</h4>
              </div>

              <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                  <strong>Formula:</strong>
                </p>
                <div class="text-center mb-2">
                  \[ R = 10 - \frac{10}{\text{Recency Window}} \times \text{Months Since Last Invoice} \]
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  <strong>How it works:</strong> Calculates how recently the client's last invoice was. Uses the actual invoice data from your Xero connection to determine months since last invoice. Higher scores for more recent activity.
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-2">Recency window (months)</label>
                  <select id="recencyWindow" name="recency_window_months"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="3" {{ $config->recency_window_months == 3 ? 'selected' : '' }}>3 months</option>
                    <option value="6" {{ $config->recency_window_months == 6 ? 'selected' : '' }}>6 months</option>
                    <option value="9" {{ $config->recency_window_months == 9 ? 'selected' : '' }}>9 months</option>
                    <option value="12" {{ $config->recency_window_months == 12 ? 'selected' : '' }}>12 months</option>
                    <option value="24" {{ $config->recency_window_months == 24 ? 'selected' : '' }}>24 months</option>
                    <option value="36" {{ $config->recency_window_months == 36 ? 'selected' : '' }}>36 months</option>
                    <option value="custom" {{ !in_array($config->recency_window_months, [3,6,9,12,24,36]) ? 'selected' : '' }}>Custom</option>
                  </select>
                  <input type="hidden" id="recencyWindowHidden" name="recency_window_months" 
                         value="{{ $config->recency_window_months }}">
                </div>
                <div id="recencyWindowCustomWrap" class="{{ in_array($config->recency_window_months, [3,6,9,12,24,36]) ? 'hidden' : '' }}">
                  <label class="block text-sm font-medium mb-2">Custom window (months)</label>
                  <input id="recencyWindowCustom" type="number" min="1" max="60" step="1" 
                         value="{{ !in_array($config->recency_window_months, [3,6,9,12,24,36]) ? $config->recency_window_months : 12 }}"
                         class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
              </div>
            </div>

            <!-- Frequency (F) -->
            <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg">
              <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                  <span class="text-green-600 dark:text-green-400 font-semibold">F</span>
                </div>
                <h4 class="text-lg font-semibold">Frequency (F)</h4>
              </div>

              <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                  <strong>Formula:</strong>
                </p>
                <div class="text-center mb-2">
                  \[ F = \min(\text{Number of Invoices in Analysis Period}, 10) \]
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  <strong>How it works:</strong> Counts how many invoices the client had in the analysis period, capped at 10. Uses actual invoice data from your Xero connection. Higher scores for more frequent activity.
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-2">Analysis period (months)</label>
                  <select id="freqPeriod" name="frequency_period_months"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="3" {{ $config->frequency_period_months == 3 ? 'selected' : '' }}>3 months</option>
                    <option value="6" {{ $config->frequency_period_months == 6 ? 'selected' : '' }}>6 months</option>
                    <option value="9" {{ $config->frequency_period_months == 9 ? 'selected' : '' }}>9 months</option>
                    <option value="12" {{ $config->frequency_period_months == 12 ? 'selected' : '' }}>12 months</option>
                    <option value="24" {{ $config->frequency_period_months == 24 ? 'selected' : '' }}>24 months</option>
                    <option value="36" {{ $config->frequency_period_months == 36 ? 'selected' : '' }}>36 months</option>
                    <option value="custom" {{ !in_array($config->frequency_period_months, [3,6,9,12,24,36]) ? 'selected' : '' }}>Custom</option>
                  </select>
                  <input type="hidden" id="freqPeriodHidden" name="frequency_period_months" 
                         value="{{ $config->frequency_period_months }}">
                </div>
                <div id="freqPeriodCustomWrap" class="{{ in_array($config->frequency_period_months, [3,6,9,12,24,36]) ? 'hidden' : '' }}">
                  <label class="block text-sm font-medium mb-2">Custom period (months)</label>
                  <input id="freqPeriodCustom" type="number" min="1" max="60" step="1" 
                         value="{{ !in_array($config->frequency_period_months, [3,6,9,12,24,36]) ? $config->frequency_period_months : 12 }}"
                         class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
              </div>
            </div>

            <!-- Monetary (M) -->
            <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg">
              <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3">
                  <span class="text-purple-600 dark:text-purple-400 font-semibold">M</span>
                </div>
                <h4 class="text-lg font-semibold">Monetary (M)</h4>
              </div>

              <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                  <strong>Formula:</strong>
                </p>
                <div class="text-center mb-2">
                  \[ M = \frac{\text{Client's Largest Invoice in Window}}{\text{Benchmark Value}} \times 10 \]
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  <strong>How it works:</strong> Compares the client's largest invoice amount in the analysis window against a benchmark value. Uses actual invoice data from your Xero connection. Higher scores for larger invoice amounts.
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="block text-sm font-medium mb-2">Analysis window (months)</label>
                  <select id="monetaryWindow" name="monetary_window_months"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="3" {{ $config->monetary_window_months == 3 ? 'selected' : '' }}>3 months</option>
                    <option value="6" {{ $config->monetary_window_months == 6 ? 'selected' : '' }}>6 months</option>
                    <option value="9" {{ $config->monetary_window_months == 9 ? 'selected' : '' }}>9 months</option>
                    <option value="12" {{ $config->monetary_window_months == 12 ? 'selected' : '' }}>12 months</option>
                    <option value="24" {{ $config->monetary_window_months == 24 ? 'selected' : '' }}>24 months</option>
                    <option value="36" {{ $config->monetary_window_months == 36 ? 'selected' : '' }}>36 months</option>
                    <option value="custom" {{ !in_array($config->monetary_window_months ?? 12, [3,6,9,12,24,36]) ? 'selected' : '' }}>Custom</option>
                  </select>
                  <input type="hidden" id="monetaryWindowHidden" name="monetary_window_months" 
                         value="{{ $config->monetary_window_months ?? 12 }}">
                </div>
                <div id="monetaryWindowCustomWrap" class="{{ in_array($config->monetary_window_months ?? 12, [3,6,9,12,24,36]) ? 'hidden' : '' }}">
                  <label class="block text-sm font-medium mb-2">Custom window (months)</label>
                  <input id="monetaryWindowCustom" type="number" min="1" max="60" step="1" 
                         value="{{ !in_array($config->monetary_window_months ?? 12, [3,6,9,12,24,36]) ? ($config->monetary_window_months ?? 12) : 12 }}"
                         class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Benchmark mode</label>
                <div class="flex gap-3">
                  <label class="inline-flex items-center">
                    <input type="radio" name="monetary_benchmark_mode" value="percentile" class="bmMode"
                           {{ $config->monetary_benchmark_mode === 'percentile' ? 'checked' : '' }}>
                    <span class="ml-2">Percentile (top X% of all invoices)</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input type="radio" name="monetary_benchmark_mode" value="direct_value" class="bmMode"
                           {{ $config->monetary_benchmark_mode === 'direct_value' ? 'checked' : '' }}>
                    <span class="ml-2">Direct value</span>
                  </label>
                </div>
              </div>

              <!-- Percentile path -->
              <div id="bmPercentileWrap" class="{{ $config->monetary_benchmark_mode !== 'percentile' ? 'hidden' : '' }}">
                <label class="block text-sm font-medium mb-2">Benchmark percentile (%)</label>
                <input id="bmPercent" type="number" name="monetary_benchmark_percentile" min="1" max="20" step="0.5" 
                       value="{{ $config->monetary_benchmark_percentile }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <p class="text-xs text-gray-500 mt-1">Top X% of all invoices (by amount). The benchmark will be the smallest amount in this top percentile.</p>

                <!-- Live preview of benchmark value -->
                <div id="bmPreview" class="mt-3 p-3 rounded-md border border-gray-200 dark:border-gray-600 bg-white/70 dark:bg-gray-800/70">
                  <div id="bmPreviewLoading" class="text-sm text-gray-500 dark:text-gray-400 hidden">Loading benchmark…</div>
                  <div id="bmPreviewContent" class="text-sm text-gray-800 dark:text-gray-200 hidden"></div>
                  <div id="bmPreviewEmpty" class="text-sm text-amber-700 dark:text-amber-300 hidden">No invoices found in this window. Add invoices or try a wider window.</div>
                  <div id="bmPreviewError" class="text-sm text-red-700 dark:text-red-300 hidden">Could not compute benchmark. Please try again.</div>
                </div>
              </div>

              <!-- Direct value path -->
              <div id="bmValueWrap" class="{{ $config->monetary_benchmark_mode !== 'direct_value' ? 'hidden' : '' }}">
                <label class="block text-sm font-medium mb-2">Benchmark value (£)</label>
                <input id="bmValue" type="number" name="monetary_benchmark_value" min="0" step="0.01" 
                       value="{{ $config->monetary_benchmark_value }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <p class="text-xs text-gray-500 mt-1">Direct monetary value in pounds (£) to use as benchmark.</p>
              </div>
            </div>

            <!-- Overall Score -->
            <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg">
              <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center mr-3">
                  <span class="text-orange-600 dark:text-orange-400 font-semibold">Σ</span>
                </div>
                <h4 class="text-lg font-semibold">Overall RFM Score</h4>
              </div>

              <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                  <strong>Formula:</strong>
                </p>
                <div class="text-center mb-2">
                  \[ \text{RFM Score} = \frac{R + F + M}{3} \]
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  <strong>How it works:</strong> Simple average of the three component scores, resulting in a value between 0 and 10. Higher scores indicate better overall customer value.
                </p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
              <button id="save-btn" type="submit"
                      class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Save Configuration
              </button>
              <a id="goto-btn" href="{{ route('rfm.index') }}"
                 class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Go to RFM Scores
              </a>
              <button id="reset-btn" type="button" onclick="document.getElementById('reset-form').submit()"
                      class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Reset to Defaults
              </button>
            </div>

          </form>

          <!-- Save & Recalculate - full width under the three buttons (separate form; avoid nested forms) -->
          <div class="mt-4">
            <form id="save-recalc-form" method="POST" action="{{ route('rfm.config.save-recalculate') }}">
              @csrf
              <!-- Mirror the key inputs so save+recalc receives the same payload -->
              <input type="hidden" name="recency_window_months" id="sr_recency" value="{{ $config->recency_window_months }}">
              <input type="hidden" name="frequency_period_months" id="sr_frequency" value="{{ $config->frequency_period_months }}">
              <input type="hidden" name="monetary_window_months" id="sr_monetary" value="{{ $config->monetary_window_months ?? 12 }}">
              <input type="hidden" name="monetary_benchmark_mode" id="sr_mode" value="{{ $config->monetary_benchmark_mode }}">
              <input type="hidden" name="monetary_benchmark_percentile" id="sr_percent" value="{{ $config->monetary_benchmark_percentile }}">
              <input type="hidden" name="monetary_benchmark_value" id="sr_value" value="{{ $config->monetary_benchmark_value }}">

              <button type="submit" id="save-recalc-btn" class="relative w-full inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:bg-indigo-500 disabled:cursor-not-allowed overflow-hidden" {{ (isset($hasInvoices) && !$hasInvoices) ? 'disabled' : '' }}>
                <div id="save-recalc-overlay" class="hidden absolute inset-0 bg-indigo-500/70 animate-pulse"></div>
                <span id="save-recalc-text" class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2z"></path>
                  </svg>
                  Save & Recalculate
                </span>
                <div id="save-recalc-loading" class="hidden flex items-center gap-2">
                  <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Processing…</span>
                </div>
              </button>
            </form>
          </div>

          <!-- Hidden reset form -->
          <form id="reset-form" method="POST" action="{{ route('rfm.config.reset') }}" class="hidden">
            @csrf
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
