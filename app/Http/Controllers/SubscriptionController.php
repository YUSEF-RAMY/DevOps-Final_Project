<?php

namespace App\Http\Controllers;

use App\Models\FlowerSubscription;
use App\Models\PaymentGatewaySetting;
use App\Support\ThemeHelper;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $subscriptions = auth()->user()->flowerSubscriptions()->latest()->get();
        $monthlyPrice = config('florist.subscription_monthly_price');
        $weeklyPrice = config('florist.subscription_weekly_price');

        return view(ThemeHelper::view('subscriptions.index'), compact('subscriptions', 'monthlyPrice', 'weeklyPrice'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'frequency' => 'required|in:weekly,monthly',
            'delivery_day' => 'required|string|max:20',
            'preferred_color_palette' => 'nullable|string|max:120',
        ]);

        $amount = $data['frequency'] === 'weekly'
            ? config('florist.subscription_weekly_price')
            : config('florist.subscription_monthly_price');

        $subscription = auth()->user()->flowerSubscriptions()->create([
            'frequency' => $data['frequency'],
            'delivery_day' => $data['delivery_day'],
            'preferred_color_palette' => $data['preferred_color_palette'] ?? null,
            'status' => 'active',
            'amount' => $amount,
            'next_delivery_at' => now()->addWeek(),
        ]);

        if ($this->stripeEnabled()) {
            $this->createStripeSubscription($subscription);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Your flower subscription is now active.');
    }

    public function pause(FlowerSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);
        $subscription->update(['status' => 'paused', 'paused_at' => now()]);

        return back()->with('success', 'Subscription paused.');
    }

    public function resume(FlowerSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);
        $subscription->update(['status' => 'active', 'paused_at' => null, 'next_delivery_at' => now()->addWeek()]);

        return back()->with('success', 'Subscription resumed.');
    }

    public function cancel(FlowerSubscription $subscription)
    {
        $this->authorizeSubscription($subscription);
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return back()->with('success', 'Subscription cancelled.');
    }

    private function authorizeSubscription(FlowerSubscription $subscription): void
    {
        abort_unless($subscription->user_id === auth()->id(), 403);
    }

    private function stripeEnabled(): bool
    {
        $settings = PaymentGatewaySetting::getGatewaySettings('stripe');
        return !empty($settings['enabled']);
    }

    private function createStripeSubscription(FlowerSubscription $subscription): void
    {
        $settings = PaymentGatewaySetting::getGatewaySettings('stripe');
        $secret = $settings['secret_key'] ?? null;
        if (!$secret) {
            return;
        }

        try {
            $stripe = new StripeClient($secret);
            $customer = $stripe->customers->create([
                'email' => $subscription->user->email,
                'name' => $subscription->user->name,
                'metadata' => ['user_id' => $subscription->user_id],
            ]);

            $subscription->update(['stripe_customer_id' => $customer->id]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
