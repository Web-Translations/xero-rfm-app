<?php

namespace App\Services;

use GoCardlessPro\Client;
use GoCardlessPro\Environment;
use GoCardlessPro\Webhook as GoCardlessWebhook;
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

            // If user already has a subscription, cancel it to ensure only one active at a time
            if (!empty($user->gocardless_subscription_id)) {
                try {
                    $this->client->subscriptions()->cancel($user->gocardless_subscription_id);
                } catch (\Throwable $t) {
                    // ignore if already cancelled/invalid; we will overwrite below
                    Log::warning('Failed to cancel existing subscription before creating new one', [
                        'existing_subscription_id' => $user->gocardless_subscription_id,
                        'error' => $t->getMessage(),
                    ]);
                }
            }

            // Create subscription in GoCardless (SDK v7 requires 'params' envelope)
            $subscriptionParams = [
                'amount' => (int) $plan['price'],
                'currency' => (string) $plan['currency'],
                'interval_unit' => (string) $plan['interval'],
                'links' => [
                    'mandate' => (string) $mandateId,
                ],
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan_id' => (string) $planId,
                ],
            ];

            // Create subscription

            $subscription = $this->client->subscriptions()->create([
                'params' => $subscriptionParams,
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
     * Create a redirect flow for mandate setup
     */
    public function createRedirectFlow(User $user, string $planId, array $customerData = [])
    {
        try {
            $plan = $this->config['plans'][$planId] ?? null;
            
            if (!$plan) {
                throw new \Exception("Invalid plan: {$planId}");
            }

            if ($plan['price'] === 0) {
                throw new \Exception("Redirect flow not needed for free plans");
            }

            // Get or create customer
            $customerResult = $this->getOrCreateCustomer($user, $customerData);
            if (!$customerResult['success']) {
                throw new \Exception("Failed to create customer: " . $customerResult['error']);
            }

            // Determine success redirect URL (prefer explicit HTTPS URL when provided)
            $baseSuccessUrl = !empty($this->config['success_url'])
                ? $this->config['success_url']
                : route('memberships.success');

            // Create redirect flow (use SDK 'params' envelope)
            $sessionToken = uniqid('session_', true);
            // Include our session token in success URL so we don't rely solely on cookies
            $successRedirectUrl = $baseSuccessUrl . (str_contains($baseSuccessUrl, '?') ? '&' : '?') . 'gcst=' . urlencode($sessionToken);
            $redirectFlowParams = [
                'description' => "Subscription to {$plan['name']} plan",
                'session_token' => $sessionToken,
                'success_redirect_url' => $successRedirectUrl,
                'scheme' => 'bacs',
                'prefilled_customer' => [
                    'email' => (string) ($customerData['email'] ?? $user->email),
                    'given_name' => (string) ($customerData['given_name'] ?? ''),
                    'family_name' => (string) ($customerData['family_name'] ?? ''),
                    'address_line1' => (string) ($customerData['address_line1'] ?? ''),
                    // GoCardless requires strings only; send empty string if absent
                    'address_line2' => (string) ($customerData['address_line2'] ?? ''),
                    'city' => (string) ($customerData['city'] ?? ''),
                    'postal_code' => (string) ($customerData['postal_code'] ?? ''),
                    'country_code' => (string) ($customerData['country_code'] ?? 'GB'),
                ],
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan_id' => $planId,
                ],
            ];

            // Only include creditor link if configured
            if (!empty($this->config['creditor_id'])) {
                $redirectFlowParams['links'] = [
                    'creditor' => $this->config['creditor_id'],
                ];
            }

            Log::info('Creating GoCardless redirect flow with params:', $redirectFlowParams);

            try {
                $redirectFlow = $this->client->redirectFlows()->create([
                    'params' => $redirectFlowParams,
                ]);
                Log::info('GoCardless redirect flow created successfully', ['id' => $redirectFlow->id]);
            } catch (\Exception $e) {
                Log::error('GoCardless API error details:', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            return [
                'success' => true,
                'redirect_url' => $redirectFlow->redirect_url,
                'redirect_flow_id' => $redirectFlow->id,
                'session_token' => $sessionToken,
            ];

        } catch (\Exception $e) {
            Log::error('GoCardless redirect flow creation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Complete redirect flow and create mandate
     */
    public function completeRedirectFlow(string $redirectFlowId, string $sessionToken)
    {
        try {
            // Complete the redirect flow to generate a mandate
            $completed = $this->client->redirectFlows()->complete($redirectFlowId, [
                'params' => [
                    'session_token' => $sessionToken,
                ],
            ]);

            if (empty($completed->links) || empty($completed->links->mandate)) {
                throw new \Exception('Redirect flow completion did not return a mandate');
            }

            // Normalize metadata (SDK returns stdClass)
            $metadataArray = (array) ($completed->metadata ?? []);

            return [
                'success' => true,
                'mandate_id' => $completed->links->mandate,
                'customer_id' => $completed->links->customer ?? null,
                'plan_id' => $metadataArray['plan_id'] ?? null,
                'user_id' => isset($metadataArray['user_id']) ? (int) $metadataArray['user_id'] : null,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to complete redirect flow: ' . $e->getMessage());
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
     * Get next payment (if any) for a subscription
     */
    public function getNextPaymentForSubscription(string $subscriptionId)
    {
        try {
            // Fetch up to 50 payments for this subscription and pick the earliest
            // relevant upcoming payment (pending_submission/submitted), otherwise fall back
            // to the most recent payment with a charge_date if no upcoming item exists yet.
            $list = $this->client->payments()->list([
                'params' => [
                    'subscription' => $subscriptionId,
                    'limit' => 50,
                ],
            ]);

            $items = $list->items ?? [];
            if (!is_array($items)) {
                $items = [];
            }

            $upcomingStatuses = ['pending_submission', 'submitted'];
            $chosen = null;
            foreach ($items as $payment) {
                $status = $payment->status ?? null;
                $chargeDate = $payment->charge_date ?? null;
                if (!$status || !$chargeDate) {
                    continue;
                }
                if (in_array($status, $upcomingStatuses, true)) {
                    if ($chosen === null) {
                        $chosen = $payment;
                    } else {
                        if (strtotime((string) $payment->charge_date) < strtotime((string) $chosen->charge_date)) {
                            $chosen = $payment;
                        }
                    }
                }
            }

            if ($chosen) {
                return $chosen;
            }

            // Fallback: pick the most recent payment with a charge_date
            foreach ($items as $payment) {
                if (!empty($payment->charge_date)) {
                    if ($chosen === null) {
                        $chosen = $payment;
                    } else {
                        if (strtotime((string) $payment->charge_date) > strtotime((string) $chosen->charge_date)) {
                            $chosen = $payment;
                        }
                    }
                }
            }

            if ($chosen) {
                return $chosen;
            }
        } catch (\Exception $e) {
            Log::error('GoCardless get next payment failed: ' . $e->getMessage());
        }

        return null;
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
            // Check if customer already exists
            $existingCustomer = $user->gocardlessCustomer;
            if ($existingCustomer) {
                return ['success' => true, 'customer_id' => $existingCustomer->gocardless_customer_id];
            }

            // Create customer in GoCardless
            $customerParams = [
                'email' => (string) $customerData['email'],
                'given_name' => (string) ($customerData['given_name'] ?? ''),
                'family_name' => (string) ($customerData['family_name'] ?? ''),
                'address_line1' => (string) ($customerData['address_line1'] ?? ''),
                'address_line2' => (string) ($customerData['address_line2'] ?? ''),
                'city' => (string) ($customerData['city'] ?? ''),
                'region' => (string) ($customerData['region'] ?? ''),
                'postal_code' => (string) ($customerData['postal_code'] ?? ''),
                'country_code' => (string) ($customerData['country_code'] ?? 'GB'),
                'metadata' => [
                    'user_id' => (string) $user->id,
                ],
            ];

            // Only include company_name if present
            if (!empty($customerData['company_name'])) {
                $customerParams['company_name'] = (string) $customerData['company_name'];
            }

            // Create customer

            $customer = $this->client->customers()->create([
                'params' => $customerParams,
            ]);

            // Save customer to database
            GoCardlessCustomer::create([
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
        // Check if customer already exists
        $existingCustomer = $user->gocardlessCustomer;
        if ($existingCustomer) {
            return ['success' => true, 'customer_id' => $existingCustomer->gocardless_customer_id];
        }

        // Create new customer
        return $this->createCustomer($user, $customerData);
    }

    /**
     * Handle webhook events
     */
    public function handleWebhook(string $payload, string $signature)
    {
        try {
            // Verify and parse using GoCardless SDK helper
            $parsed = GoCardlessWebhook::parse($payload, $signature, (string) $this->config['webhook_secret']);

            foreach ($parsed->events as $event) {
                // Normalize to array for existing handlers
                $normalized = [
                    'action' => $event->action,
                    'resource_type' => $event->resource_type,
                    'links' => (array) $event->links,
                    'metadata' => (array) ($event->metadata ?? []),
                ];
                $this->processWebhookEvent($normalized);
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage());

            // In local/dev, fall back to unsigned processing to aid debugging
            if (config('app.env') === 'local') {
                try {
                    $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                    foreach (($decoded['events'] ?? []) as $event) {
                        $this->processWebhookEvent([
                            'action' => $event['action'] ?? null,
                            'resource_type' => $event['resource_type'] ?? null,
                            'links' => $event['links'] ?? [],
                            'metadata' => $event['metadata'] ?? [],
                        ]);
                    }
                    Log::warning('Processed webhook without signature verification (local env only)');
                    return ['success' => true, 'warning' => 'unsigned_webhook_processed_in_local'];
                } catch (\Throwable $te) {
                    Log::error('Unsigned webhook fallback also failed: ' . $te->getMessage());
                }
            }

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process individual webhook event
     */
    private function processWebhookEvent(array $event)
    {
        $action = $event['action'];
        $resourceType = $event['resource_type'];

        // Map links key safely (webhook uses singular keys in links)
        $links = $event['links'] ?? [];
        $resourceId = $links['subscription']
            ?? $links['mandate']
            ?? $links['payment']
            ?? $links['customer']
            ?? null;

        Log::info("Processing webhook event: {$action} for {$resourceType} " . ($resourceId ?? 'unknown'));

        switch ($resourceType) {
            case 'subscriptions':
                $this->handleSubscriptionEvent($action, $resourceId);
                break;
            case 'mandates':
                $this->handleMandateEvent($action, $resourceId);
                break;
            case 'payments':
                $this->handlePaymentEvent($action, $resourceId);
                break;
        }
    }

    /**
     * Handle subscription webhook events
     */
    private function handleSubscriptionEvent(string $action, string $subscriptionId)
    {
        // Find user by subscription ID
        $user = User::where('gocardless_subscription_id', $subscriptionId)->first();
        if (!$user) {
            Log::warning("No user found for subscription: {$subscriptionId}");
            return;
        }

        switch ($action) {
            case 'created':
            case 'submitted':
                $user->update(['subscription_status' => 'pending']);
                break;
            case 'activated':
            case 'confirmed':
                $user->update(['subscription_status' => 'active']);
                break;
            case 'cancelled':
                $user->update(['subscription_status' => 'cancelled']);
                break;
            case 'customer_approval_denied':
            case 'payment_failed':
                $user->update(['subscription_status' => 'past_due']);
                break;
        }
    }

    /**
     * Handle mandate webhook events
     */
    private function handleMandateEvent(string $action, string $mandateId)
    {
        Log::info("Mandate event: {$action} for mandate {$mandateId}");
    }

    /**
     * Handle payment webhook events
     */
    private function handlePaymentEvent(string $action, string $paymentId)
    {
        Log::info("Payment event: {$action} for payment {$paymentId}");
        // You can persist payment status here if desired
    }
}
