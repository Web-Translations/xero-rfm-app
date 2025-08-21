<?php

namespace App\Http\Controllers;

use App\Services\Narrative\AiInsightService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        
        try {
            $section = $request->input('section');
            $data = $request->input('data');
            
            $insight = $this->insightService->generateInsight($section, $data);
            
            return response()->json([
                'success' => true,
                'insight' => $insight,
                'section' => $section
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate AI insight: ' . $e->getMessage()
            ], 500);
        }
    }
}
