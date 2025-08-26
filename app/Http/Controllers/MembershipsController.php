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
     * Subscribe to a plan
     */
    public function subscribe(SelectPlanRequest $request)
    {
        $user = Auth::user();
        $planId = $request->input('plan');

        Log::info('Plan selection request', ['plan' => $planId, 'user_id' => $user->id]);

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
                Log::info('Redirecting to payment page', ['plan' => $planId]);
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
        $user = Auth::user();
        
        // Get the redirect flow ID from the session or query parameter
        $flowId = $request->query('flow_id') ?? session('redirect_flow_id');
        
        Log::info('Payment success callback', ['flow_id' => $flowId, 'user_id' => $user->id]);

        // Handle test mode (when GoCardless is not configured)
        if (empty(config('gocardless.access_token'))) {
            Log::info('GoCardless not configured, handling test mode success');
            // In test mode, we assume the payment was successful
            $planId = $request->query('plan') ?? 'pro';
            
            $user->update([
                'subscription_plan' => $planId,
                'subscription_status' => 'active',
            ]);
            
            return redirect()->route('memberships.index')
                ->with('status', 'Subscription activated! (Test mode - GoCardless not configured)');
        }
        
        if (!$flowId) {
            return redirect()->route('memberships.index')
                ->with('error', 'Payment flow not found. Please try again.');
        }

        try {
            // Get the redirect flow from GoCardless
            $flow = $this->goCardlessService->client->redirect_flows()->get($flowId);
            
            if ($flow->status === 'succeeded') {
                // Payment was successful, update user's subscription
                $planId = $flow->metadata['plan_id'] ?? 'pro';
                
                $user->update([
                    'subscription_plan' => $planId,
                    'subscription_status' => 'active',
                ]);
                
                return redirect()->route('memberships.index')
                    ->with('status', 'Payment successful! Your subscription is now active.');
            } else {
                return redirect()->route('memberships.index')
                    ->with('error', 'Payment was not completed. Please try again.');
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

        Log::info('Payment page request', [
            'plan' => $planId, 
            'plan_data' => $plan,
            'user_id' => $user->id,
            'has_existing_customer' => $existingCustomer ? true : false
        ]);

        if (!$plan || $plan['price'] === 0) {
            Log::warning('Invalid plan for payment page', ['plan' => $planId]);
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

        // Ensure plan is a string
        $planId = (string) $planId;

        Log::info('Processing payment', ['plan' => $planId, 'plan_type' => gettype($planId), 'user_id' => $user->id]);
        Log::info('All request data', $request->all());

        try {
            // Create customer with the form data
            $customerData = $request->getCustomerData();
            Log::info('Customer data', $customerData);
            
            $customerResult = $this->goCardlessService->getOrCreateCustomer($user, $customerData);
            Log::info('Customer creation result', $customerResult);
            
            if (!$customerResult['success']) {
                Log::error('Customer creation failed', $customerResult);
                return back()->withErrors(['error' => 'Failed to create customer: ' . $customerResult['error']]);
            }
            
            // Check if GoCardless is properly configured
            if (empty(config('gocardless.access_token'))) {
                Log::info('GoCardless not configured, using test mode');
                // Fallback for development/testing
                $user->update([
                    'subscription_plan' => $planId,
                    'subscription_status' => 'active',
                ]);
                
                return redirect()->route('memberships.index')
                    ->with('status', 'Subscription activated! (GoCardless not configured - this is a test mode)');
            }
            
            // Create billing request flow and redirect to GoCardless
            $flowResult = $this->goCardlessService->createBillingRequestFlow($user, $planId);
            Log::info('Billing request flow result', $flowResult);
            
            if (!$flowResult['success']) {
                Log::error('Billing request flow failed', $flowResult);
                return back()->withErrors(['error' => 'Failed to create payment flow: ' . $flowResult['error']]);
            }
            
            // Redirect to GoCardless payment page
            return redirect()->away($flowResult['redirect_url']);

        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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
}
