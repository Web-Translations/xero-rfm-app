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
});
