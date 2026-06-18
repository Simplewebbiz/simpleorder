<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Mail\SubscriptionReminderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig, config('stripe.webhook_secret'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        match ($event->type) {
            'customer.subscription.updated'  => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted'  => $this->handleSubscriptionDeleted($event->data->object),
            'invoice.payment_failed'         => $this->handlePaymentFailed($event->data->object),
            'invoice.payment_succeeded'      => $this->handlePaymentSucceeded($event->data->object),
            default                          => null,
        };

        return response()->json(['received' => true]);
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        $tenant = Tenant::where('stripe_customer_id', $subscription->customer)->first();
        if (!$tenant) return;

        $tenant->update([
            'stripe_subscription_id' => $subscription->id,
            'subscription_status'    => $subscription->status,
            'subscription_ends_at'   => $subscription->current_period_end
                ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_end)
                : null,
        ]);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $tenant = Tenant::where('stripe_subscription_id', $subscription->id)->first();
        if (!$tenant) return;

        $tenant->update([
            'subscription_status'  => 'canceled',
            'subscription_ends_at' => now(),
        ]);
    }

    private function handlePaymentFailed(object $invoice): void
    {
        $tenant = Tenant::where('stripe_customer_id', $invoice->customer)->first();
        if (!$tenant) return;

        $tenant->update(['subscription_status' => 'past_due']);

        // Email the store owner
        tenancy()->initialize($tenant);
        $owner = \App\Models\Tenant\User::where('role', 'owner')->first();
        tenancy()->end();

        if ($owner) {
            Mail::to($owner->email)->send(new SubscriptionReminderMail($tenant, 'past_due'));
        }
    }

    private function handlePaymentSucceeded(object $invoice): void
    {
        $tenant = Tenant::where('stripe_customer_id', $invoice->customer)->first();
        if (!$tenant) return;

        if ($tenant->subscription_status === 'past_due') {
            $tenant->update(['subscription_status' => 'active']);
        }
    }
}
