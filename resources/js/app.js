import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// AI Insights functionality
function generateAIInsight(section, data) {
    console.log('generateAIInsight called with:', { section, data });
    
    const insightDiv = document.getElementById(`ai-insight-${section}`);
    const contentDiv = document.getElementById(`ai-content-${section}`);
    
    console.log('Found elements:', { insightDiv, contentDiv });
    
    if (!insightDiv || !contentDiv) {
        console.error('Required elements not found');
        return;
    }
    
    // Show loading state
    insightDiv.classList.remove('hidden');
    contentDiv.innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2"></div>
            <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded mb-2 w-3/4"></div>
            <div class="h-4 bg-blue-200 dark:bg-blue-700 rounded w-1/2"></div>
        </div>
    `;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    console.log('CSRF Token:', csrfToken);
    
    // Call backend API
    fetch('/rfm/insights/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            section: section,
            data: data
        })
    })
    .then(async response => {
        let json;
        try { json = await response.json(); } catch (_) { json = null; }
        console.log('Response status:', response.status, 'json:', json);
        if (!response.ok) {
            const msg = (json && (json.error || json.message)) ? `HTTP ${response.status}: ${(json.error || json.message)}` : `HTTP error! status: ${response.status}`;
            throw new Error(msg);
        }
        return json;
    })
    .then(result => {
        console.log('API result:', result);
        if (result.success) {
            const provider = result.provider || '';

            // If deterministic fallback, format into readable bullet list and add error toggle
            if (provider === 'deterministic') {
                const raw = String(result.insight || '').trim();
                const lines = raw
                    .replace(/\s+\./g, '.')
                    .split(/\.(?:\s+|$)/)
                    .map(s => s.trim())
                    .filter(Boolean);
                const bullets = lines.map(l => `<li class=\"mb-1\">${l}${l.endsWith('.') ? '' : '.'}</li>`).join('');

                const errorDetail = result.fallback_error ? `
                    <button id=\"ai-toggle-error-${section}\" class=\"mt-3 text-xs text-blue-600 dark:text-blue-300 underline\">Show OpenAI error</button>
                    <pre id=\"ai-error-${section}\" class=\"mt-2 hidden whitespace-pre-wrap text-xs text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 p-2 rounded\">${result.fallback_error}</pre>
                ` : '';

                contentDiv.innerHTML = `
                    <ul class=\"list-disc pl-5 text-blue-800 dark:text-blue-200\">${bullets}</ul>
                    <div class=\"text-xs text-gray-500 mt-2\">Provider: deterministic</div>
                    ${errorDetail}
                `;

                // Wire up toggle if present
                const toggle = document.getElementById(`ai-toggle-error-${section}`);
                const pre = document.getElementById(`ai-error-${section}`);
                if (toggle && pre) {
                    toggle.addEventListener('click', () => {
                        const isHidden = pre.classList.contains('hidden');
                        pre.classList.toggle('hidden');
                        toggle.textContent = isHidden ? 'Hide OpenAI error' : 'Show OpenAI error';
                    });
                }
            } else {
                const providerHtml = provider ? `<div class=\"text-xs text-gray-500 mt-2\">Provider: ${provider}</div>` : '';
                contentDiv.innerHTML = `<p class=\"text-blue-800 dark:text-blue-200\">${result.insight}</p>${providerHtml}`;
            }
        } else {
            throw new Error(result.error || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error generating AI insight:', error);
        contentDiv.innerHTML = `
            <p class="text-red-600 dark:text-red-400">
                Error generating AI insight. Please try again.
                <br><small class="text-gray-500">${error.message}</small>
            </p>
        `;
    });
}

// Initialize AI Insights when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('AI Insights: DOM loaded, initializing...');
    
    // Get KPIs data from script tag
    const kpisElement = document.getElementById('kpis-data');
    let kpisData = {};
    
    if (kpisElement) {
        try {
            kpisData = JSON.parse(kpisElement.textContent);
            console.log('KPIs data loaded:', kpisData);
        } catch (e) {
            console.error('Failed to parse KPIs data:', e);
        }
    }
    
    // Add event listeners to AI insight buttons
    const aiButtons = document.querySelectorAll('[id^="ai-insights-btn-"]');
    console.log('Found AI buttons:', aiButtons.length);
    
    aiButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('AI button clicked:', this.id);
            const section = this.getAttribute('data-section');
            if (section) {
                generateAIInsight(section, kpisData);
            }
        });
    });
});
