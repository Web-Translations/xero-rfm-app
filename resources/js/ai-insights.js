// AI Insights functionality
function generateAIInsight(section, data) {
    console.log('generateAIInsight called with:', { section, data });
    
    const insightDiv = document.getElementById(`ai-insight-${section}`);
    const contentDiv = document.getElementById(`ai-content-${section}`);
    
    console.log('Found elements:', { insightDiv, contentDiv });
    
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
        // Parse JSON if possible and surface backend error details
        let json;
        try { json = await response.json(); } catch (_) { json = null; }
        if (!response.ok) {
            const msg = (json && (json.error || json.message)) ? `HTTP ${response.status}: ${(json.error || json.message)}` : `HTTP error! status: ${response.status}`;
            throw new Error(msg);
        }
        return json;
    })
    .then(result => {
        if (result.success) {
            contentDiv.innerHTML = `<p class="text-blue-800 dark:text-blue-200">${result.insight}</p>`;
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

// Toggle AI insight visibility
function toggleAIInsight(section) {
    const insightDiv = document.getElementById(`ai-insight-${section}`);
    if (insightDiv) {
        insightDiv.classList.toggle('hidden');
    }
}

// Hide AI insight
function hideAIInsight(section) {
    const insightDiv = document.getElementById(`ai-insight-${section}`);
    if (insightDiv) {
        insightDiv.classList.add('hidden');
    }
}
