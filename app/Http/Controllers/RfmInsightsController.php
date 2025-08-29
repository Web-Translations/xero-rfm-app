<?php

namespace App\Http\Controllers;

use App\Services\Narrative\AiInsightService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RfmInsightsController extends Controller
{
    private AiInsightService $insightService;
    
    public function __construct(AiInsightService $insightService)
    {
        $this->insightService = $insightService;
    }
    
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'section' => 'required|string',
            'data' => 'required|array'
        ]);
        
        $user = $request->user();
        $section = $request->input('section');
        $data = $request->input('data');
        
        try {
            // Check subscription access based on insight type
            if (!$this->checkInsightAccess($user, $section)) {
                return response()->json([
                    'success' => false,
                    'error' => 'This insight type requires a higher subscription plan.',
                    'requires_upgrade' => true
                ], 403);
            }
            
            // Generate insight with appropriate depth based on subscription
            $insight = $this->insightService->generateInsight($section, $data, $user);
            
            return response()->json([
                'success' => true,
                'insight' => $insight,
                'section' => $section,
                'insight_depth' => $this->getInsightDepth($user)
            ]);
            
        } catch (\Exception $e) {
            Log::error('AI Insight generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate insight: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has access to specific insight types
     */
    private function checkInsightAccess($user, string $section): bool
    {
        // Basic insights available to all plans
        $basicInsights = ['executive-summary', 'customer-movement', 'risk-assessment'];
        
        // Enhanced insights require Pro or Pro+
        $enhancedInsights = ['growth-opportunities', 'revenue-concentration', 'customer-segments', 'historical-trends'];
        
        // AI-powered insights require Pro+
        $aiInsights = ['ai-predictive', 'ai-recommendations', 'ai-chat'];
        
        if (in_array($section, $basicInsights)) {
            return true; // Available to all plans
        }
        
        if (in_array($section, $enhancedInsights)) {
            return $user->canAccessDeeperInsights();
        }
        
        if (in_array($section, $aiInsights)) {
            return $user->canAccessAIInsights();
        }
        
        // Default to basic access
        return true;
    }

    /**
     * Get insight depth based on user's subscription
     */
    private function getInsightDepth($user): string
    {
        if ($user->canAccessAIInsights()) {
            return 'ai_powered';
        }
        
        if ($user->canAccessDeeperInsights()) {
            return 'enhanced';
        }
        
        return 'basic';
    }
}
