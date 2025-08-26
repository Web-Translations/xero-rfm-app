<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\GoCardlessCustomer;
use App\Services\GoCardlessService;

class WebhookController extends Controller
{
    public function __construct(private GoCardlessService $goCardlessService)
    {
    }
    /**
     * Handle GoCardless webhooks
     */
    public function gocardless(Request $request)
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Webhook-Signature');

        $result = $this->goCardlessService->handleWebhook($payload, $signature);

        if ($result['success'] ?? false) {
            return response()->json(['status' => 'success'], 200);
        }

        Log::error('Webhook processing failed', ['error' => $result['error'] ?? 'unknown']);
        return response()->json(['status' => 'error'], 400);
    }

    /**
     * Handle subscription events
     */
    private function handleSubscriptionEvent(array $event, string $action)
    {
        $subscription = $event['events'][0]['links']['subscription'] ?? null;
        $userId = $event['events'][0]['metadata']['user_id'] ?? null;

        if (!$subscription || !$userId) {
            Log::warning('Missing subscription or user_id in webhook', $event);
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            Log::warning('User not found for webhook', ['user_id' => $userId]);
            return;
        }

        switch ($action) {
            case 'created':
                Log::info('Subscription created', ['subscription_id' => $subscription, 'user_id' => $userId]);
                break;
                
            case 'activated':
                Log::info('Subscription activated', ['subscription_id' => $subscription, 'user_id' => $userId]);
                $user->update(['subscription_status' => 'active']);
                break;
                
            case 'cancelled':
                Log::info('Subscription cancelled', ['subscription_id' => $subscription, 'user_id' => $userId]);
                $user->update(['subscription_status' => 'cancelled']);
                break;
                
            case 'payment_failed':
                Log::warning('Subscription payment failed', ['subscription_id' => $subscription, 'user_id' => $userId]);
                $user->update(['subscription_status' => 'past_due']);
                break;
                
            default:
                Log::info('Unhandled subscription action', ['action' => $action, 'subscription_id' => $subscription]);
        }
    }

    /**
     * Handle mandate events
     */
    private function handleMandateEvent(array $event, string $action)
    {
        $mandate = $event['events'][0]['links']['mandate'] ?? null;
        
        Log::info('Mandate event', [
            'mandate_id' => $mandate,
            'action' => $action
        ]);

        switch ($action) {
            case 'created':
                Log::info('Mandate created', ['mandate_id' => $mandate]);
                break;
                
            case 'active':
                Log::info('Mandate activated', ['mandate_id' => $mandate]);
                break;
                
            case 'cancelled':
                Log::info('Mandate cancelled', ['mandate_id' => $mandate]);
                break;
                
            default:
                Log::info('Unhandled mandate action', ['action' => $action, 'mandate_id' => $mandate]);
        }
    }

    /**
     * Handle payment events
     */
    private function handlePaymentEvent(array $event, string $action)
    {
        $payment = $event['events'][0]['links']['payment'] ?? null;
        
        Log::info('Payment event', [
            'payment_id' => $payment,
            'action' => $action
        ]);

        switch ($action) {
            case 'created':
                Log::info('Payment created', ['payment_id' => $payment]);
                break;
                
            case 'confirmed':
                Log::info('Payment confirmed', ['payment_id' => $payment]);
                break;
                
            case 'failed':
                Log::warning('Payment failed', ['payment_id' => $payment]);
                break;
                
            default:
                Log::info('Unhandled payment action', ['action' => $action, 'payment_id' => $payment]);
        }
    }

    /**
     * Handle customer events
     */
    private function handleCustomerEvent(array $event, string $action)
    {
        $customer = $event['events'][0]['links']['customer'] ?? null;
        
        Log::info('Customer event', [
            'customer_id' => $customer,
            'action' => $action
        ]);

        switch ($action) {
            case 'created':
                Log::info('Customer created', ['customer_id' => $customer]);
                break;
                
            case 'updated':
                Log::info('Customer updated', ['customer_id' => $customer]);
                break;
                
            default:
                Log::info('Unhandled customer action', ['action' => $action, 'customer_id' => $customer]);
        }
    }

    /**
     * Verify webhook signature (TODO: Implement)
     */
    private function verifyWebhookSignature(Request $request)
    {
        // TODO: Implement webhook signature verification
        // This is important for security in production
        
        $webhookSecret = config('gocardless.webhook_secret');
        $signature = $request->header('Webhook-Signature');
        
        if (!$webhookSecret || !$signature) {
            Log::warning('Missing webhook secret or signature');
            return false;
        }
        
        // TODO: Implement proper signature verification
        // For now, just log that we would verify it
        Log::info('Webhook signature verification would happen here');
        
        return true;
    }
}
