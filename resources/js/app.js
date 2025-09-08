import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Simple global toast utility
window.showToast = function(message, type = 'info', durationMs = 3500) {
    try {
        const rootId = 'toast-root';
        let root = document.getElementById(rootId);
        if (!root) {
            root = document.createElement('div');
            root.id = rootId;
            root.className = 'fixed bottom-6 right-6 z-50 space-y-2';
            document.body.appendChild(root);
        }

        const color = {
            success: { base: 'bg-emerald-100 border-emerald-300 text-emerald-900', icon: 'text-emerald-700' },
            error: { base: 'bg-red-100 border-red-300 text-red-900', icon: 'text-red-700' },
            warning: { base: 'bg-amber-100 border-amber-300 text-amber-900', icon: 'text-amber-700' },
            info: { base: 'bg-blue-100 border-blue-300 text-blue-900', icon: 'text-blue-700' },
        }[type] || { base: 'bg-blue-100 border-blue-300 text-blue-900', icon: 'text-blue-700' };

        const el = document.createElement('div');
        el.className = `max-w-sm ${color.base} border rounded-lg shadow-lg overflow-hidden animate-fade-in`;
        el.innerHTML = `
            <div class="px-4 py-3 flex items-start space-x-3">
                <svg class="w-5 h-5 mt-0.5 ${color.icon}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18.5a6.5 6.5 0 110-13 6.5 6.5 0 010 13z" />
                </svg>
                <div class="text-sm">${message}</div>
                <button aria-label="Close" class="ml-2 opacity-70 hover:opacity-100">✕</button>
            </div>
        `;

        const closeBtn = el.querySelector('button');
        const remove = () => {
            if (!el.parentNode) return;
            el.classList.add('animate-fade-out');
            setTimeout(() => el.remove(), 200);
        };
        closeBtn.addEventListener('click', remove);

        root.appendChild(el);
        setTimeout(remove, durationMs);
    } catch (e) {
        console.warn('Toast failed, falling back to alert:', e);
        alert(message);
    }
}

// Intercept fetch to show toast on impersonation blocks
const originalFetch = window.fetch;
window.fetch = async function(input, init = {}) {
    const response = await originalFetch(input, init);
    try {
        const blocked = response.headers.get('X-Impersonation-Blocked') === '1' || response.status === 403;
        if (blocked) {
            let message = response.headers.get('X-Impersonation-Message');
            if (!message) {
                try {
                    const clone = response.clone();
                    const data = await clone.json().catch(() => null);
                    message = data?.message || 'Viewing as user (read-only). Changes are disabled.';
                } catch (_) {}
            }
            if (window.showToast) window.showToast(message, 'warning');
        }
    } catch (_) { /* no-op */ }
    return response;
}

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
