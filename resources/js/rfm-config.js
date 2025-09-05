// RFM Configuration Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const recencyWindow = document.getElementById('recencyWindow');
    const recencyWindowCustomWrap = document.getElementById('recencyWindowCustomWrap');
    const recencyWindowCustom = document.getElementById('recencyWindowCustom');

    const freqPeriod = document.getElementById('freqPeriod');
    const freqPeriodCustomWrap = document.getElementById('freqPeriodCustomWrap');
    const freqPeriodCustom = document.getElementById('freqPeriodCustom');

    const monetaryWindow = document.getElementById('monetaryWindow');
    const monetaryWindowCustomWrap = document.getElementById('monetaryWindowCustomWrap');
    const monetaryWindowCustom = document.getElementById('monetaryWindowCustom');

    const bmModeRadios = document.querySelectorAll('.bmMode');
    const bmPercentileWrap = document.getElementById('bmPercentileWrap');
    const bmValueWrap = document.getElementById('bmValueWrap');
    const bmPercent = document.getElementById('bmPercent');
    const bmPreview = document.getElementById('bmPreview');
    const bmPreviewLoading = document.getElementById('bmPreviewLoading');
    const bmPreviewContent = document.getElementById('bmPreviewContent');
    const bmPreviewEmpty = document.getElementById('bmPreviewEmpty');
    const bmPreviewError = document.getElementById('bmPreviewError');

    // Show/hide custom fields
    function toggleCustom(selectEl, wrapEl) {
        if (!selectEl || !wrapEl) return;
        wrapEl.classList.toggle('hidden', selectEl.value !== 'custom');
    }

    // Update hidden fields for form submission
    function updateHiddenFields() {
        const recencyHidden = document.getElementById('recencyWindowHidden');
        const freqHidden = document.getElementById('freqPeriodHidden');
        const monetaryHidden = document.getElementById('monetaryWindowHidden');
        
        if (recencyHidden && recencyWindow) {
            if (recencyWindow.value === 'custom' && recencyWindowCustom) {
                recencyHidden.value = recencyWindowCustom.value;
            } else {
                recencyHidden.value = recencyWindow.value;
            }
        }
        
        if (freqHidden && freqPeriod) {
            if (freqPeriod.value === 'custom' && freqPeriodCustom) {
                freqHidden.value = freqPeriodCustom.value;
            } else {
                freqHidden.value = freqPeriod.value;
            }
        }

        if (monetaryHidden && monetaryWindow) {
            if (monetaryWindow.value === 'custom' && monetaryWindowCustom) {
                monetaryHidden.value = monetaryWindowCustom.value;
            } else {
                monetaryHidden.value = monetaryWindow.value;
            }
        }
    }

    // Toggle benchmark mode UI
    function updateBmModeUI() {
        if (!bmModeRadios || !bmPercentileWrap || !bmValueWrap) return;
        
        const mode = Array.from(bmModeRadios).find(r => r.checked)?.value || 'percentile';
        bmPercentileWrap.classList.toggle('hidden', mode !== 'percentile');
        bmValueWrap.classList.toggle('hidden', mode !== 'direct_value');

        // Trigger preview when switching to percentile
        if (mode === 'percentile') {
            fetchBenchmarkPreview();
        }
    }

    // Event wiring
    if (recencyWindow) {
        recencyWindow.addEventListener('change', () => { 
            toggleCustom(recencyWindow, recencyWindowCustomWrap); 
            updateHiddenFields();
        });
    }
    
    if (recencyWindowCustom) {
        recencyWindowCustom.addEventListener('input', () => {
            updateHiddenFields();
        });
    }

    if (freqPeriod) {
        freqPeriod.addEventListener('change', () => { 
            toggleCustom(freqPeriod, freqPeriodCustomWrap); 
            updateHiddenFields();
        });
    }
    
    if (freqPeriodCustom) {
        freqPeriodCustom.addEventListener('input', () => {
            updateHiddenFields();
        });
    }

    if (monetaryWindow) {
        monetaryWindow.addEventListener('change', () => { 
            toggleCustom(monetaryWindow, monetaryWindowCustomWrap); 
            updateHiddenFields();
        });
    }
    
    if (monetaryWindowCustom) {
        monetaryWindowCustom.addEventListener('input', () => {
            updateHiddenFields();
        });
    }

    if (bmModeRadios.length > 0) {
        bmModeRadios.forEach(r => r.addEventListener('change', updateBmModeUI));
    }

    // Debounced preview fetcher
    let bmPreviewTimer = null;
    function fetchBenchmarkPreview() {
        if (!bmPercent || !monetaryWindow || !bmPreview) return;
        const mode = Array.from(bmModeRadios).find(r => r.checked)?.value || 'percentile';
        if (mode !== 'percentile') return;

        const percentile = parseFloat(bmPercent.value);
        const windowMonths = monetaryWindow.value === 'custom' && monetaryWindowCustom ? monetaryWindowCustom.value : monetaryWindow.value;
        if (!percentile || !windowMonths) return;

        // UI states
        bmPreviewLoading?.classList.remove('hidden');
        bmPreviewContent?.classList.add('hidden');
        bmPreviewEmpty?.classList.add('hidden');
        bmPreviewError?.classList.add('hidden');

        // Debounce network
        if (bmPreviewTimer) clearTimeout(bmPreviewTimer);
        bmPreviewTimer = setTimeout(async () => {
            try {
                const url = `/rfm/config/benchmark-preview?monetary_window_months=${encodeURIComponent(windowMonths)}&percentile=${encodeURIComponent(percentile)}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                bmPreviewLoading?.classList.add('hidden');

                if (!res.ok) {
                    bmPreviewError?.classList.remove('hidden');
                    return;
                }

                if (!data || data.sampleSize === 0 || data.benchmark == null) {
                    bmPreviewEmpty?.classList.remove('hidden');
                    return;
                }

                const formatter = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'GBP', maximumFractionDigits: 2 });
                const value = formatter.format(Number(data.benchmark));
                const content = `Benchmark ≈ ${value} (from ${data.sampleSize} customers, ${data.windowStart} → ${data.windowEnd})`;
                bmPreviewContent.textContent = content;
                bmPreviewContent?.classList.remove('hidden');
            } catch (e) {
                bmPreviewLoading?.classList.add('hidden');
                bmPreviewError?.classList.remove('hidden');
            }
        }, 300);
    }

    // Initialize
    if (recencyWindow && recencyWindowCustomWrap) {
        toggleCustom(recencyWindow, recencyWindowCustomWrap);
    }
    if (freqPeriod && freqPeriodCustomWrap) {
        toggleCustom(freqPeriod, freqPeriodCustomWrap);
    }
    if (monetaryWindow && monetaryWindowCustomWrap) {
        toggleCustom(monetaryWindow, monetaryWindowCustomWrap);
    }
    updateBmModeUI();

    // Re-run preview on changes
    if (bmPercent) bmPercent.addEventListener('input', fetchBenchmarkPreview);
    if (monetaryWindow) monetaryWindow.addEventListener('change', fetchBenchmarkPreview);
    if (monetaryWindowCustom) monetaryWindowCustom.addEventListener('input', fetchBenchmarkPreview);
});
