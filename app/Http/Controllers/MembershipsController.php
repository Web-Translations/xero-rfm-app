<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GoCardlessService;
use Illuminate\Support\Facades\Log;

class MembershipsController extends Controller
{
    protected $goCardlessService;

    public function __construct(GoCardlessService $goCardlessService)
    {
        $this->goCardlessService = $goCardlessService;
    }

    public function index()
    {
        $user = Auth::user();
        $plans = $this->goCardlessService->getPlans();
        
        return view('memberships.index', [
            'currentPlan' => $user->subscription_plan ?? 'free',
            'user' => $user,
            'plans' => $plans,
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:free,pro,pro_plus',
        ]);

        $user = Auth::user();
        $planId = $request->input('plan');

        try {
            if ($planId === 'free') {
                // Handle free plan subscription
                $result = $this->goCardlessService->createSubscription($user, $planId);
                
                if ($result['success']) {
                    return redirect()->route('memberships.index')
                        ->with('status', 'Successfully subscribed to Free plan!');
                }
            } else {
                // For paid plans, redirect to GoCardless payment flow
                return redirect()->route('memberships.payment', ['plan' => $planId]);
            }

        } catch (\Exception $e) {
            Log::error('Subscription error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to process subscription. Please try again.']);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel()
    {
        $user = Auth::user();

        try {
            $result = $this->goCardlessService->cancelSubscription($user);
            
            if ($result['success']) {
                return redirect()->route('memberships.index')
                    ->with('status', 'Subscription cancelled successfully.');
            } else {
                return back()->withErrors(['error' => $result['error'] ?? 'Failed to cancel subscription.']);
            }

        } catch (\Exception $e) {
            Log::error('Subscription cancellation error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to cancel subscription. Please try again.']);
        }
    }

    /**
     * Show payment page for paid plans
     */
    public function payment(Request $request)
    {
        $planId = $request->input('plan');
        $plan = $this->goCardlessService->getPlan($planId);

        if (!$plan || $plan['price'] === 0) {
            return redirect()->route('memberships.index');
        }

        return view('memberships.payment', [
            'plan' => $plan,
            'planId' => $planId,
        ]);
    }

    /**
     * Handle GoCardless webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Webhook-Signature');

        $result = $this->goCardlessService->handleWebhook($payload, $signature);

        if ($result['success']) {
            return response('OK', 200);
        } else {
            Log::error('Webhook processing failed: ' . ($result['error'] ?? 'Unknown error'));
            return response('Error', 400);
        }
    }
}
