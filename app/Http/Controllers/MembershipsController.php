<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GoCardlessService;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CreateSubscriptionRequest;
use App\Http\Requests\SelectPlanRequest;

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
     * Manage page: show subscription details (next charge etc.)
     */
    public function manage()
    {
        $user = Auth::user();

        $subscription = null;
        $nextPayment = null;
        if (!empty($user->gocardless_subscription_id)) {
            $subscription = $this->goCardlessService->getSubscription($user->gocardless_subscription_id);
            $nextPayment = $this->goCardlessService->getNextPaymentForSubscription($user->gocardless_subscription_id);
        }

        return view('memberships.manage', [
            'user' => $user,
            'subscription' => $subscription,
            'nextPayment' => $nextPayment,
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(SelectPlanRequest $request)
    {
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
                // For paid plans, redirect to payment page
                return redirect()->route('memberships.payment', ['plan' => $planId]);
            }

        } catch (\Exception $e) {
            Log::error('Subscription error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to process subscription. Please try again.']);
        }
    }

    /**
     * Handle successful payment completion
     */
    public function success(Request $request)
    {
        // Success handler (no debug logging)
        // We prefer using user from metadata, but if logged in we'll use it
        $user = Auth::user();
        
        // Get the redirect flow ID from GoCardless
        $flowId = $request->query('redirect_flow_id');
        $sessionToken = (string) ($request->query('gcst') ?: session('gc_session_token'));
        
        if (!$flowId || !$sessionToken) {
            return redirect()->route('memberships.index')
                ->with('error', 'Payment flow not found. Please try again.');
        }

        try {
            // Complete the redirect flow and create mandate
            $result = $this->goCardlessService->completeRedirectFlow($flowId, $sessionToken);
            
            if ($result['success']) {
                // Resolve user: prefer current auth, else metadata user_id
                if (!$user && !empty($result['user_id'])) {
                    $user = \App\Models\User::find((int) $result['user_id']);
                }
                if (!$user) {
                    return redirect()->route('memberships.index')
                        ->with('error', 'Could not identify user for subscription. Please log in and try again.');
                }
                // Create subscription using the mandate
                $subscriptionResult = $this->goCardlessService->createSubscription(
                    $user, 
                    $result['plan_id'] ?? (string) session('gc_plan_id', 'pro'), 
                    $result['mandate_id']
                );
                
                // Clear temp session state
                session()->forget(['gc_session_token', 'gc_plan_id']);

                if ($subscriptionResult['success']) {
                    return redirect()->route('memberships.index')
                        ->with('status', 'Payment successful! Your subscription is now active.');
                } else {
                    return redirect()->route('memberships.index')
                        ->with('error', 'Subscription creation failed: ' . $subscriptionResult['error']);
                }
            } else {
                return redirect()->route('memberships.index')
                    ->with('error', 'Payment setup failed: ' . $result['error']);
            }
            
        } catch (\Exception $e) {
            Log::error('Payment success handling failed: ' . $e->getMessage());
            return redirect()->route('memberships.index')
                ->with('error', 'There was an issue processing your payment. Please contact support.');
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
        $planId = $request->query('plan');
        $plan = $this->goCardlessService->getPlan($planId);
        $user = Auth::user();
        $existingCustomer = $user->gocardlessCustomer;

        if (!$plan || $plan['price'] === 0) {
            return redirect()->route('memberships.index')
                ->with('error', 'Invalid plan selected.');
        }

        return view('memberships.payment', [
            'plan' => $plan,
            'planId' => $planId,
            'existingCustomer' => $existingCustomer,
        ]);
    }

    /**
     * Process payment for paid plans
     */
    public function processPayment(CreateSubscriptionRequest $request)
    {
        $user = Auth::user();
        $planId = $request->input('plan');

        try {
            // Create customer with the form data
            $customerData = $request->getCustomerData();
            
            $customerResult = $this->goCardlessService->getOrCreateCustomer($user, $customerData);
            
            if (!$customerResult['success']) {
                return back()->withErrors(['error' => 'Failed to create customer: ' . $customerResult['error']]);
            }
            
            // Create redirect flow and redirect to GoCardless
            $flowResult = $this->goCardlessService->createRedirectFlow($user, $planId, $customerData);
            
            if (!$flowResult['success']) {
                return back()->withErrors(['error' => 'Failed to create payment flow: ' . $flowResult['error']]);
            }

            if (empty($flowResult['redirect_url'])) {
                Log::error('GC redirect flow missing redirect_url', $flowResult);
                return back()->withErrors(['error' => 'Payment provider did not return a redirect URL. Please try again.']);
            }
            
            // Persist session token and plan for completion step
            session([
                'gc_session_token' => $flowResult['session_token'] ?? null,
                'gc_plan_id' => $planId,
            ]);

            // Redirect to GoCardless payment page
            return redirect()->away($flowResult['redirect_url']);

        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to process payment: ' . $e->getMessage()]);
        }
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

    // Debug API endpoint removed
}
