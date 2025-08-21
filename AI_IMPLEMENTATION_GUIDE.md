# AI Insights Implementation Guide

## Overview
This implementation adds AI-powered insights to the RFM reporting system. Currently, it uses deterministic text generation as a stepping stone, with the infrastructure in place to switch to OpenAI API calls.

## Current Status
✅ **Phase 1 Complete**: Interactive AI Insights Buttons
- AI insight buttons added to report sections
- Deterministic text generation working
- JavaScript infrastructure in place
- Backend API ready for OpenAI integration

## Files Created/Modified

### New Files:
- `app/Services/Narrative/AiInsightService.php` - Core AI service
- `app/Http/Controllers/RfmInsightsController.php` - API controller
- `resources/js/ai-insights.js` - Frontend JavaScript
- `config/ai.php` - AI configuration
- `AI_IMPLEMENTATION_GUIDE.md` - This guide

### Modified Files:
- `routes/web.php` - Added AI insights route
- `vite.config.js` - Added ai-insights.js
- `resources/views/rfm/reports/show.blade.php` - Added AI buttons

## How to Get OpenAI API Key

### Step 1: Create OpenAI Account
1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Sign up or log in
3. Complete account verification

### Step 2: Get API Key
1. Navigate to [API Keys](https://platform.openai.com/api-keys)
2. Click "Create new secret key"
3. Give it a name (e.g., "RFM Insights")
4. Copy the key (starts with `sk-`)

### Step 3: Add to Environment
Add to your `.env` file:
```env
OPENAI_API_KEY=sk-your-key-here
AI_PROVIDER=openai
AI_INSIGHTS_ENABLED=true
```

## Testing the Implementation

### Current (Deterministic Mode)
1. Go to any RFM report page
2. Click "AI Insights" button on any section
3. See deterministic insights generated

### Future (OpenAI Mode)
1. Add OpenAI API key to `.env`
2. Uncomment the OpenAI call in `AiInsightService.php`
3. Comment out the deterministic fallback
4. Test with real AI insights

## Next Steps

### Phase 2: OpenAI Integration
1. **Enable OpenAI**: Uncomment line in `AiInsightService.php`
2. **Add Error Handling**: Implement fallback mechanisms
3. **Add Caching**: Cache AI responses to reduce API calls
4. **Rate Limiting**: Implement request throttling

### Phase 3: Enhanced Features
1. **More Sections**: Add AI insights to all report sections
2. **Custom Prompts**: Allow users to customize AI prompts
3. **Historical Context**: Include trend data in AI analysis
4. **Action Recommendations**: Generate specific action items

## API Endpoints

### Generate AI Insight
```
POST /rfm/insights/generate
Content-Type: application/json

{
    "section": "executive-summary",
    "data": { ... }
}
```

Response:
```json
{
    "success": true,
    "insight": "AI generated insight text...",
    "section": "executive-summary"
}
```

## Configuration Options

### Environment Variables
```env
# OpenAI Configuration
OPENAI_API_KEY=sk-your-key-here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7

# AI Features
AI_PROVIDER=openai
AI_INSIGHTS_ENABLED=true
AI_INSIGHTS_CACHE_DURATION=3600
AI_INSIGHTS_FALLBACK=true
```

## Troubleshooting

### Common Issues:
1. **CSRF Token Error**: Ensure `@csrf` meta tag is in layout
2. **JavaScript Not Loading**: Check Vite compilation
3. **API Key Issues**: Verify OpenAI key is valid and has credits
4. **Rate Limiting**: Implement proper error handling for API limits

### Debug Mode:
Add to `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## Security Considerations

1. **API Key Security**: Never commit API keys to version control
2. **Rate Limiting**: Implement proper throttling to prevent abuse
3. **Input Validation**: Validate all data sent to AI APIs
4. **Error Handling**: Don't expose sensitive information in error messages
