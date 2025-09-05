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

    // Keep Save & Recalculate hidden inputs in sync so server receives latest selections
    function syncSaveRecalcHidden() {
        const srRec = document.getElementById('sr_recency');
        const srFreq = document.getElementById('sr_frequency');
        const srMon = document.getElementById('sr_monetary');
        const srMode = document.getElementById('sr_mode');
        const srPercent = document.getElementById('sr_percent');
        const srValue = document.getElementById('sr_value');
        if (srRec && recencyWindow) srRec.value = (recencyWindow.value === 'custom' ? recencyWindowCustom?.value : recencyWindow.value) || srRec.value;
        if (srFreq && freqPeriod) srFreq.value = (freqPeriod.value === 'custom' ? freqPeriodCustom?.value : freqPeriod.value) || srFreq.value;
        if (srMon && monetaryWindow) srMon.value = (monetaryWindow.value === 'custom' ? monetaryWindowCustom?.value : monetaryWindow.value) || srMon.value;
        if (srMode && bmModeRadios?.length) srMode.value = Array.from(bmModeRadios).find(r => r.checked)?.value || srMode.value;
        if (srPercent && bmPercent) srPercent.value = bmPercent.value || srPercent.value;
        if (srValue) {
            const directVal = document.getElementById('bmValue');
            if (directVal) srValue.value = directVal.value || srValue.value;
        }
    }

    const saveRecalcForm = document.getElementById('save-recalc-form');
    const saveRecalcBtn = document.getElementById('save-recalc-btn');
    const saveRecalcText = document.getElementById('save-recalc-text');
    const saveRecalcLoading = document.getElementById('save-recalc-loading');
    const saveRecalcOverlay = document.getElementById('save-recalc-overlay');
    if (saveRecalcForm && saveRecalcBtn) {
        saveRecalcForm.addEventListener('submit', function() {
            // Ensure main hidden fields are in sync first
            updateHiddenFields();
            syncSaveRecalcHidden();
            // Show loading state
            saveRecalcBtn.disabled = true;
            if (saveRecalcText) saveRecalcText.classList.add('hidden');
            if (saveRecalcLoading) saveRecalcLoading.classList.remove('hidden');
            if (saveRecalcOverlay) saveRecalcOverlay.classList.remove('hidden');
            // Dim the row of buttons above for clarity
            const saveBtn = document.getElementById('save-btn');
            const gotoBtn = document.getElementById('goto-btn');
            const resetBtn = document.getElementById('reset-btn');
            [saveBtn, gotoBtn, resetBtn].forEach(el => { if (el) el.classList.add('opacity-50','pointer-events-none'); });
        });
    }

    // Ensure standard Save submission also syncs hidden values
    const mainForm = document.getElementById('rfm-config-form');
    if (mainForm) {
        mainForm.addEventListener('submit', function() {
            updateHiddenFields();
        });
    }
});
