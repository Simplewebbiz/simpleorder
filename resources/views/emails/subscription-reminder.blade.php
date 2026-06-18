<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SimpleOrder Subscription</title>
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
    .wrapper { max-width: 600px; margin: 0 auto; background: #fff; }
    .header { background: #1a1a1a; padding: 24px 32px; }
    .header h1 { color: #fff; margin: 0; font-size: 22px; }
    .body { padding: 32px; }
    h2 { margin: 0 0 16px; font-size: 24px; }
    p { color: #4b5563; line-height: 1.6; }
    .alert { border-radius: 8px; padding: 16px; margin-bottom: 24px; }
    .alert-warning { background: #fef3c7; border: 1px solid #fcd34d; }
    .alert-danger  { background: #fef2f2; border: 1px solid #fca5a5; }
    .alert-info    { background: #eff6ff; border: 1px solid #93c5fd; }
    .btn { display: inline-block; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 8px; }
    .btn-primary { background: #e85d04; color: #fff; }
    .btn-secondary { background: #f3f4f6; color: #1a1a1a; }
    .plan-box { background: #f9fafb; border-radius: 8px; padding: 16px; margin: 24px 0; }
    .footer { background: #f9fafb; padding: 24px 32px; text-align: center; font-size: 12px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>SimpleOrder</h1>
    </div>
    <div class="body">
        @if($type === 'upcoming')
            <h2>Your subscription renews soon</h2>
            <div class="alert alert-info">
                Your {{ $plan->name }} plan renews in 7 days. Make sure your payment method is up to date.
            </div>
            <p>Hi {{ $tenant->name }},</p>
            <p>Just a heads up — your SimpleOrder subscription will automatically renew in 7 days. No action needed if your payment method is current.</p>
            <div class="plan-box">
                <strong>{{ $plan->name }}</strong><br>
                ${{ number_format($plan->price_monthly / 100, 2) }}/month
            </div>
            <a href="{{ $portalUrl }}" class="btn btn-primary">Manage Billing</a>
            <a href="{{ $billingUrl }}" class="btn btn-secondary">View Plan Details</a>

        @elseif($type === 'past_due')
            <h2>Payment failed — action required</h2>
            <div class="alert alert-danger">
                Your last payment failed. Please update your payment method to avoid service interruption.
            </div>
            <p>Hi {{ $tenant->name }},</p>
            <p>We weren't able to process your SimpleOrder subscription payment. Your store will remain active for a short grace period, but you'll need to update your payment method to continue accepting orders.</p>
            <a href="{{ $portalUrl }}" class="btn btn-primary">Update Payment Method</a>

        @elseif($type === 'trial_ending')
            <h2>Your free trial ends in 3 days</h2>
            <div class="alert alert-warning">
                Add a payment method to continue using SimpleOrder after your trial.
            </div>
            <p>Hi {{ $tenant->name }},</p>
            <p>Your free trial of SimpleOrder ends in 3 days. To keep accepting orders, subscribe before your trial expires.</p>
            <div class="plan-box">
                <strong>{{ $plan->name }}</strong><br>
                ${{ number_format($plan->price_monthly / 100, 2) }}/month — cancel anytime
            </div>
            <a href="{{ $billingUrl }}" class="btn btn-primary">Subscribe Now</a>
        @endif
    </div>
    <div class="footer">
        <p>SimpleOrder · Questions? Reply to this email or visit your dashboard.</p>
        <p>You're receiving this because you have an active SimpleOrder account.</p>
    </div>
</div>
</body>
</html>
