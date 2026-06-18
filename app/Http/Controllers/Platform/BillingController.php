<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Cashier\Exceptions\IncompletePayment;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = auth('platform')->user()->tenant;
        $plans  = Plan::where('is_active', true)->orderBy('sort')->get();

        return Inertia::render('Platform/Billing/Index', [
            'tenant'       => $tenant->load('plan'),
            'plans'        => $plans,
            'subscription' => $tenant->subscription('default'),
            'upcomingInvoice' => $this->getUpcomingInvoice($tenant),
            'invoices'     => $tenant->invoices()->map(fn($i) => [
                'id'     => $i->id,
                'date'   => $i->date()->toFormattedDateString(),
                'total'  => $i->total(),
                'status' => $i->status,
                'pdf'    => $i->invoice_pdf,
            ]),
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id'         => 'required|exists:plans,id',
            'billing_cycle'   => 'required|in:monthly,yearly',
            'payment_method'  => 'required|string',
        ]);

        $tenant = auth('platform')->user()->tenant;
        $plan   = Plan::findOrFail($request->plan_id);

        $priceId = $request->billing_cycle === 'yearly'
            ? $plan->stripe_yearly_price_id
            : $plan->stripe_monthly_price_id;

        try {
            $tenant->newSubscription('default', $priceId)
                ->create($request->payment_method);

            $tenant->update([
                'plan_id'             => $plan->id,
                'subscription_status' => 'active',
            ]);

            return back()->with('success', 'Subscribed to ' . $plan->name . '!');
        } catch (IncompletePayment $e) {
            return back()->withErrors(['payment' => 'Payment incomplete. Please check your payment method.']);
        }
    }

    public function cancel(Request $request)
    {
        $tenant = auth('platform')->user()->tenant;
        $tenant->subscription('default')->cancel();
        $tenant->update(['subscription_status' => 'canceled']);

        return back()->with('success', 'Subscription cancelled. You have access until the end of your billing period.');
    }

    public function portal()
    {
        $tenant = auth('platform')->user()->tenant;

        return redirect($tenant->billingPortalUrl(route('dashboard.billing')));
    }

    private function getUpcomingInvoice($tenant): ?array
    {
        try {
            $invoice = $tenant->upcomingInvoice();
            return [
                'amount' => $invoice->total(),
                'date'   => $invoice->date()->toFormattedDateString(),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
