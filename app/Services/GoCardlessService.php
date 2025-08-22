<?php

namespace App\Services;

use GoCardlessPro\Client;
use GoCardlessPro\Environment;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class GoCardlessService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->config = config('gocardless');
        
        $environment = $this->config['environment'] === 'live' 
            ? Environment::LIVE 
            : Environment::SANDBOX;
            
        $this->client = new Client([
            'access_token' => $this->config['access_token'],
            'environment' => $environment,
        ]);
    }

    /**
     * Create a subscription for a user
     */
    public function createSubscription(User $user, string $planId, string $mandateId = null)
    {
        try {
            $plan = $this->config['plans'][$planId] ?? null;
            
            if (!$plan) {
                throw new \Exception("Invalid plan: {$planId}");
            }

            if ($plan['price'] === 0) {
                // Free plan - just update user's plan
                $user->update(['subscription_plan' => $planId]);
                return ['success' => true, 'plan' => $planId];
            }

            // Create subscription in GoCardless
            $subscription = $this->client->subscriptions()->create([
                'params' => [
                    'amount' => $plan['price'],
                    'currency' => $plan['currency'],
                    'interval_unit' => $plan['interval'],
                    'links' => [
                        'mandate' => $mandateId,
                    ],
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $planId,
                    ],
                ],
            ]);

            // Update user's subscription details
            $user->update([
                'subscription_plan' => $planId,
                'gocardless_subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
            ]);

            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'plan' => $planId,
            ];

        } catch (\Exception $e) {
            Log::error('GoCardless subscription creation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cancel a user's subscription
     */
    public function cancelSubscription(User $user)
    {
        try {
            if (!$user->gocardless_subscription_id) {
                // No active subscription to cancel
                $user->update([
                    'subscription_plan' => 'free',
                    'subscription_status' => 'cancelled',
                ]);
                return ['success' => true];
            }

            // Cancel subscription in GoCardless
            $subscription = $this->client->subscriptions()->cancel($user->gocardless_subscription_id);

            // Update user's subscription details
            $user->update([
                'subscription_plan' => 'free',
                'subscription_status' => 'cancelled',
            ]);

            return ['success' => true, 'subscription_id' => $subscription->id];

        } catch (\Exception $e) {
            Log::error('GoCardless subscription cancellation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get subscription details
     */
    public function getSubscription(string $subscriptionId)
    {
        try {
            return $this->client->subscriptions()->get($subscriptionId);
        } catch (\Exception $e) {
            Log::error('GoCardless get subscription failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a mandate (payment authorization)
     */
    public function createMandate(User $user, string $bankAccountId)
    {
        try {
            $mandate = $this->client->mandates()->create([
                'params' => [
                    'links' => [
                        'creditor' => $this->getCreditorId(),
                        'customer_bank_account' => $bankAccountId,
                    ],
                    'scheme' => 'bacs',
                    'metadata' => [
                        'user_id' => $user->id,
                    ],
                ],
            ]);

            return ['success' => true, 'mandate_id' => $mandate->id];

        } catch (\Exception $e) {
            Log::error('GoCardless mandate creation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get available plans
     */
    public function getPlans()
    {
        return $this->config['plans'];
    }

    /**
     * Get plan details
     */
    public function getPlan(string $planId)
    {
        return $this->config['plans'][$planId] ?? null;
    }

    /**
     * Get creditor ID (you'll need to set this up in GoCardless)
     */
    protected function getCreditorId()
    {
        // You'll need to get this from your GoCardless dashboard
        // For now, we'll use a placeholder
        return env('GOCARDLESS_CREDITOR_ID', '');
    }

    /**
     * Handle webhook events
     */
    public function handleWebhook($payload, $signature)
    {
        try {
            // Verify webhook signature
            $expectedSignature = hash_hmac('sha256', $payload, $this->config['webhook_secret']);
            
            if (!hash_equals($expectedSignature, $signature)) {
                throw new \Exception('Invalid webhook signature');
            }

            $events = json_decode($payload, true);
            
            foreach ($events['events'] as $event) {
                $this->processWebhookEvent($event);
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('GoCardless webhook processing failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process individual webhook events
     */
    protected function processWebhookEvent($event)
    {
        $resourceType = $event['resource_type'];
        $action = $event['action'];
        $resource = $event['links'][$resourceType];

        switch ($resourceType) {
            case 'subscriptions':
                $this->handleSubscriptionEvent($action, $resource);
                break;
            case 'mandates':
                $this->handleMandateEvent($action, $resource);
                break;
            case 'payments':
                $this->handlePaymentEvent($action, $resource);
                break;
        }
    }

    /**
     * Handle subscription-related webhook events
     */
    protected function handleSubscriptionEvent($action, $subscriptionId)
    {
        $user = User::where('gocardless_subscription_id', $subscriptionId)->first();
        
        if (!$user) {
            return;
        }

        switch ($action) {
            case 'confirmed':
                $user->update(['subscription_status' => 'active']);
                break;
            case 'cancelled':
                $user->update([
                    'subscription_plan' => 'free',
                    'subscription_status' => 'cancelled',
                ]);
                break;
            case 'payment_created':
                // Handle payment creation
                break;
        }
    }

    /**
     * Handle mandate-related webhook events
     */
    protected function handleMandateEvent($action, $mandateId)
    {
        // Handle mandate events (e.g., mandate activated, failed, etc.)
        Log::info("Mandate event: {$action} for mandate {$mandateId}");
    }

    /**
     * Handle payment-related webhook events
     */
    protected function handlePaymentEvent($action, $paymentId)
    {
        // Handle payment events (e.g., payment confirmed, failed, etc.)
        Log::info("Payment event: {$action} for payment {$paymentId}");
    }
}
