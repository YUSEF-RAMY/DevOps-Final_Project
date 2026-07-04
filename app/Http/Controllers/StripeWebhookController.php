<?php

namespace App\Http\Controllers;

use App\Models\FlowerSubscription;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $settings = PaymentGatewaySetting::getGatewaySettings('stripe');
        $secret = $settings['webhook_secret'] ?? null;

        try {
            if ($secret) {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } else {
                $event = json_decode($payload, false);
            }
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook verification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $type = is_object($event) ? ($event->type ?? null) : ($event['type'] ?? null);
        $object = is_object($event) ? ($event->data->object ?? null) : ($event['data']['object'] ?? null);

        match ($type) {
            'invoice.payment_succeeded' => $this->handleInvoicePaid($object),
            'invoice.payment_failed' => $this->handleInvoiceFailed($object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handleInvoicePaid($invoice): void
    {
        $subscriptionId = is_object($invoice) ? ($invoice->subscription ?? null) : ($invoice['subscription'] ?? null);
        if (!$subscriptionId) {
            return;
        }

        FlowerSubscription::where('stripe_subscription_id', $subscriptionId)->update([
            'status' => 'active',
            'next_delivery_at' => now()->addWeek(),
        ]);
    }

    private function handleInvoiceFailed($invoice): void
    {
        $subscriptionId = is_object($invoice) ? ($invoice->subscription ?? null) : ($invoice['subscription'] ?? null);
        if (!$subscriptionId) {
            return;
        }

        FlowerSubscription::where('stripe_subscription_id', $subscriptionId)->update(['status' => 'past_due']);
    }

    private function handleSubscriptionDeleted($subscription): void
    {
        $id = is_object($subscription) ? $subscription->id : ($subscription['id'] ?? null);
        FlowerSubscription::where('stripe_subscription_id', $id)->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    private function handleSubscriptionUpdated($subscription): void
    {
        $id = is_object($subscription) ? $subscription->id : ($subscription['id'] ?? null);
        $status = is_object($subscription) ? $subscription->status : ($subscription['status'] ?? null);

        $mapped = match ($status) {
            'active' => 'active',
            'paused' => 'paused',
            'canceled', 'cancelled' => 'cancelled',
            'past_due' => 'past_due',
            default => 'active',
        };

        FlowerSubscription::where('stripe_subscription_id', $id)->update(['status' => $mapped]);
    }
}
