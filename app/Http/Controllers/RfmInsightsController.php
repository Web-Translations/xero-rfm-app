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
            
            // Generate insight
            $insight = $this->insightService->generateInsight($section, $data, $user);
            
            $provider = method_exists($this->insightService, 'getLastProvider') ? $this->insightService->getLastProvider() : 'unknown';
            $payload = [
                'success' => true,
                'insight' => $insight,
                'section' => $section,
                'provider' => $provider,
                'insight_depth' => $this->getInsightDepth($user)
            ];
            if ($provider === 'deterministic' && method_exists($this->insightService, 'getLastError')) {
                $payload['fallback_error'] = $this->insightService->getLastError();
            }
            return response()->json($payload);
            
        } catch (\Exception $e) {
            // Return JSON with full error details (no need to read logs)
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? collect($e->getTrace())->take(3) : [],
            ], 200);
        }
    }

    /**
     * Check if user has access to specific insight types
     */
    private function checkInsightAccess($user, string $section): bool
    {
        // Temporarily allow all insights to be accessed by any user
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
