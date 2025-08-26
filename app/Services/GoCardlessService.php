<?php

namespace App\Services;

use GoCardlessPro\Client;
use GoCardlessPro\Environment;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\GoCardlessCustomer;

class GoCardlessService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->config = config('gocardless');
        
        Log::info('GoCardlessService initialized', [
            'environment' => $this->config['environment'],
            'has_access_token' => !empty($this->config['access_token']),
            'access_token_length' => strlen($this->config['access_token'] ?? '')
        ]);
        
        $environment = $this->config['environment'] === 'live' 
            ? Environment::LIVE 
            : Environment::SANDBOX;
            
        try {
            $this->client = new Client([
                'access_token' => $this->config['access_token'],
                'environment' => $environment,
            ]);
            Log::info('GoCardless client created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create GoCardless client: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a subscription for a user
     */
    public function createSubscription(User $user, string $planId, ?string $mandateId = null)
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

            if (!$mandateId) {
                throw new \Exception("Mandate ID is required for paid plans");
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
     * Create a billing request for payment setup
     */
    public function createBillingRequest(User $user, string $planId)
    {
        try {
            $plan = $this->config['plans'][$planId] ?? null;
            
            if (!$plan) {
                throw new \Exception("Invalid plan: {$planId}");
            }

            if ($plan['price'] === 0) {
                throw new \Exception("Billing request not needed for free plans");
            }

            // Get or create customer
            $customerResult = $this->getOrCreateCustomer($user);
            if (!$customerResult['success']) {
                throw new \Exception("Failed to create customer: " . $customerResult['error']);
            }

            // Create billing request in GoCardless
            $billingRequest = $this->client->billing_requests()->create([
                'params' => [
                    'amount' => $plan['price'],
                    'currency' => $plan['currency'],
                    'links' => [
                        'customer' => $customerResult['customer_id'],
                    ],
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $planId,
                    ],
                ],
            ]);

            return [
                'success' => true,
                'billing_request_id' => $billingRequest->id,
                'customer_id' => $customerResult['customer_id'],
            ];

        } catch (\Exception $e) {
            Log::error('GoCardless billing request creation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a billing request flow (for payment setup)
     */
    public function createBillingRequestFlow(User $user, string $planId)
    {
        try {
            $plan = $this->config['plans'][$planId] ?? null;
            
            if (!$plan) {
                throw new \Exception("Invalid plan: {$planId}");
            }

            if ($plan['price'] === 0) {
                throw new \Exception("Billing request flow not needed for free plans");
            }

            // Check if GoCardless is configured
            if (empty($this->config['access_token'])) {
                Log::info('GoCardless not configured, using test mode');
                return [
                    'success' => true,
                    'redirect_url' => route('memberships.success', ['plan' => $planId]),
                    'redirect_flow_id' => 'test_flow_' . $user->id,
                ];
            }

            // Get or create customer
            $customerResult = $this->getOrCreateCustomer($user);
            if (!$customerResult['success']) {
                throw new \Exception("Failed to create customer: " . $customerResult['error']);
            }

            // For now, since we're in test mode, just return success
            // TODO: Implement proper GoCardless API integration when credentials are available
            Log::info('GoCardless API not fully implemented yet, using test mode');
            return [
                'success' => true,
                'redirect_url' => route('memberships.success', ['plan' => $planId]),
                'redirect_flow_id' => 'test_flow_' . $user->id,
            ];

        } catch (\Exception $e) {
            Log::error('GoCardless billing request flow creation failed: ' . $e->getMessage());
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
     * Create a customer in GoCardless
     */
    public function createCustomer(User $user, array $customerData)
    {
        try {
            Log::info('Creating customer in GoCardless', ['user_id' => $user->id, 'customer_data' => $customerData]);
            
            // Check if customer already exists
            $existingCustomer = $user->gocardlessCustomer;
            if ($existingCustomer) {
                Log::info('Customer already exists in database', ['customer_id' => $existingCustomer->gocardless_customer_id]);
                return ['success' => true, 'customer_id' => $existingCustomer->gocardless_customer_id];
            }

            // Check if GoCardless is configured
            if (empty($this->config['access_token'])) {
                Log::info('GoCardless not configured, creating customer in database only');
                // Create customer in database only (for testing)
                $gocardlessCustomer = GoCardlessCustomer::create([
                    'user_id' => $user->id,
                    'gocardless_customer_id' => 'test_customer_' . $user->id,
                    'email' => $customerData['email'],
                    'given_name' => $customerData['given_name'] ?? null,
                    'family_name' => $customerData['family_name'] ?? null,
                    'company_name' => $customerData['company_name'] ?? null,
                    'address_line1' => $customerData['address_line1'] ?? null,
                    'address_line2' => $customerData['address_line2'] ?? null,
                    'city' => $customerData['city'] ?? null,
                    'region' => $customerData['region'] ?? null,
                    'postal_code' => $customerData['postal_code'] ?? null,
                    'country_code' => $customerData['country_code'] ?? 'GB',
                    'metadata' => ['plan_id' => $planId ?? 'pro'],
                ]);
                
                return ['success' => true, 'customer_id' => $gocardlessCustomer->gocardless_customer_id];
            }

            // Create customer in GoCardless
            Log::info('Creating customer in GoCardless API');
            
            $params = [
                'params' => [
                    'email' => $customerData['email'],
                    'given_name' => $customerData['given_name'] ?? null,
                    'family_name' => $customerData['family_name'] ?? null,
                    'company_name' => !empty($customerData['company_name']) ? $customerData['company_name'] : null,
                    'address_line1' => $customerData['address_line1'] ?? null,
                    'address_line2' => !empty($customerData['address_line2']) ? $customerData['address_line2'] : null,
                    'city' => $customerData['city'] ?? null,
                    'region' => !empty($customerData['region']) ? $customerData['region'] : null,
                    'postal_code' => $customerData['postal_code'] ?? null,
                    'country_code' => $customerData['country_code'] ?? 'GB',
                    'metadata' => [
                        'plan_id' => $planId,
                    ],
                ],
            ];
            
            Log::info('GoCardless customer params', $params);
            
            $customer = $this->client->customers()->create($params);

            // Save customer to database
            $gocardlessCustomer = GoCardlessCustomer::create([
                'user_id' => $user->id,
                'gocardless_customer_id' => $customer->id,
                'email' => $customer->email,
                'given_name' => $customer->given_name,
                'family_name' => $customer->family_name,
                'company_name' => $customer->company_name,
                'address_line1' => $customer->address_line1,
                'address_line2' => $customer->address_line2,
                'city' => $customer->city,
                'region' => $customer->region,
                'postal_code' => $customer->postal_code,
                'country_code' => $customer->country_code,
                'metadata' => $customer->metadata,
            ]);

            return ['success' => true, 'customer_id' => $customer->id];

        } catch (\Exception $e) {
            Log::error('GoCardless customer creation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get or create customer for a user
     */
    public function getOrCreateCustomer(User $user, array $customerData = [])
    {
        Log::info('Getting or creating customer', ['user_id' => $user->id, 'customer_data' => $customerData]);
        
        // Check if customer already exists
        $existingCustomer = $user->gocardlessCustomer;
        if ($existingCustomer) {
            Log::info('Customer already exists', ['customer_id' => $existingCustomer->gocardless_customer_id]);
            return ['success' => true, 'customer_id' => $existingCustomer->gocardless_customer_id];
        }

        // Use user data as fallback
        $customerData = array_merge([
            'email' => $user->email,
            'given_name' => $user->name,
        ], $customerData);

        Log::info('Creating new customer', ['customer_data' => $customerData]);
        return $this->createCustomer($user, $customerData);
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
