<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Update</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #1a1a1a; }
.outer { padding: 32px 16px; }
.wrapper { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.email-header { background: #1a1a1a; padding: 28px 32px; text-align: center; }
.store-logo { max-height: 56px; max-width: 200px; object-fit: contain; display: block; margin: 0 auto; }
.store-name-text { color: #ffffff; font-size: 20px; font-weight: 700; }
.contact-bar { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 10px 32px; display: flex; gap: 24px; flex-wrap: wrap; }
.contact-item { font-size: 12px; color: #6b7280; }
.contact-item a { color: #6b7280; text-decoration: none; }
.body { padding: 40px 32px; text-align: center; }
.status-icon { font-size: 72px; margin-bottom: 20px; line-height: 1; }
.status-title { font-size: 26px; font-weight: 800; margin-bottom: 10px; }
.status-msg { font-size: 15px; color: #6b7280; line-height: 1.6; max-width: 420px; margin: 0 auto 32px; }
.progress { display: flex; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; margin-bottom: 32px; }
.prog-step { flex: 1; text-align: center; padding: 10px 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #f9fafb; color: #d1d5db; border-right: 1px solid #e5e7eb; }
.prog-step:last-child { border-right: none; }
.prog-step.active { background: #e85d04; color: #fff; }
.prog-step.complete { background: #22c55e; color: #fff; }
.track-btn { display: inline-block; background: #e85d04; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-weight: 700; font-size: 15px; }
.review-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 18px 20px; margin: 24px 0; text-align: center; }
.review-title { color: #9a3412; font-size: 16px; font-weight: 800; margin-bottom: 8px; }
.review-copy { color: #7c2d12; font-size: 14px; line-height: 1.5; margin-bottom: 14px; }
.review-btn { display: inline-block; background: #0f766e; color: #ffffff; text-decoration: none; padding: 10px 22px; border-radius: 7px; font-weight: 700; font-size: 14px; }
.order-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin: 24px 0; text-align: left; font-size: 14px; }
.order-box .label { color: #9ca3af; font-size: 12px; margin-bottom: 2px; }
.order-box .value { font-weight: 600; color: #1a1a1a; }
.email-footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 32px; text-align: center; }
.footer-logo { max-height: 32px; max-width: 120px; object-fit: contain; margin: 0 auto 8px; display: block; opacity: 0.65; }
.footer-name { font-weight: 700; font-size: 13px; color: #374151; margin-bottom: 4px; }
.footer-detail { font-size: 12px; color: #9ca3af; margin-bottom: 2px; }
.footer-detail a { color: #9ca3af; text-decoration: none; }
.footer-powered { font-size: 11px; color: #d1d5db; margin-top: 10px; }
.footer-powered a { color: #d1d5db; }
</style>
</head>
<body>
<div class="outer">
<div class="wrapper">

    <div class="email-header">
        @if(!empty($settings['logo_url']))
            <img src="{{ $settings['logo_url'] }}" alt="{{ $settings['store_name'] }}" class="store-logo" />
        @else
            <div class="store-name-text">{{ $settings['store_name'] }}</div>
        @endif
    </div>

    @if(!empty($settings['store_phone']) || !empty($settings['store_address']['address']) || !empty($settings['order_email']))
    <div class="contact-bar">
        @if(!empty($settings['store_address']['address']))
            <div class="contact-item">📍 {{ $settings['store_address']['address'] }}, {{ $settings['store_address']['city'] }}</div>
        @endif
        @if(!empty($settings['store_phone']))
            <div class="contact-item">📞 <a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a></div>
        @endif
    </div>
    @endif

    @php
        $icons = [
            'received'  => '👨‍🍳',
            'ready'     => $order->method === 'delivery' ? '🚗' : '🎉',
            'complete'  => '⭐',
            'cancelled' => '❌',
        ];
        $titles = [
            'received'  => "We're on it!",
            'ready'     => $order->method === 'delivery' ? 'On its way!' : 'Ready for pickup!',
            'complete'  => 'Thanks for your order!',
            'cancelled' => 'Order Cancelled',
        ];
        $messages = [
            'received'  => "We've received your order and our team is preparing it now. We'll let you know when it's ready!",
            'ready'     => $order->method === 'delivery'
                ? "Your order is packed and on its way to you. Estimated delivery soon!"
                : "Your order is ready! Come pick it up at " . ($settings['store_address']['address'] ?? 'our location') . " in " . ($settings['store_address']['city'] ?? ''),
            'complete'  => "Your order has been completed. We hope you enjoyed it — thanks for choosing " . $settings['store_name'] . "!",
            'cancelled' => "Your order #" . $order->increment_id . " has been cancelled. Please contact us if you have any questions.",
        ];
        $statuses   = ['placed', 'received', 'ready', 'complete'];
        $currentIdx = array_search($order->status, $statuses) ?: 0;
    @endphp

    <div class="body">
        <div class="status-icon">{{ $icons[$order->status] ?? '📦' }}</div>
        <div class="status-title">{{ $titles[$order->status] ?? 'Order Update' }}</div>
        <div class="status-msg">{{ $messages[$order->status] ?? '' }}</div>

        @if($order->status !== 'cancelled')
        <div class="progress">
            @foreach($statuses as $i => $step)
                <div class="prog-step {{ $i < $currentIdx ? 'complete' : ($i === $currentIdx ? 'active' : '') }}">
                    {{ $step === 'ready' && $order->method === 'delivery' ? 'On Way' : ucfirst($step) }}
                </div>
            @endforeach
        </div>
        @endif

        <a href="{{ $trackingUrl }}" class="track-btn">View Live Order Status →</a>

        @if($order->status === 'complete' && !empty($settings['review_request_enabled']) && !empty($settings['review_url']))
        <div class="review-box">
            <div class="review-title">Enjoyed your order?</div>
            <div class="review-copy">{{ $settings['review_request_message'] ?? 'A quick review helps our restaurant grow.' }}</div>
            <a href="{{ $settings['review_url'] }}" class="review-btn">Leave a Review</a>
        </div>
        @endif
        <div class="order-box">
            <div style="display:flex;gap:24px;flex-wrap:wrap;">
                <div>
                    <div class="label">Order #</div>
                    <div class="value">{{ $order->increment_id }}</div>
                </div>
                <div>
                    <div class="label">Method</div>
                    <div class="value">{{ ucfirst($order->method) }}</div>
                </div>
                <div>
                    <div class="label">Items</div>
                    <div class="value">{{ $order->items->count() }}</div>
                </div>
                <div>
                    <div class="label">Total</div>
                    <div class="value">${{ number_format($order->total, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="email-footer">
        @if(!empty($settings['logo_url']))
            <img src="{{ $settings['logo_url'] }}" alt="{{ $settings['store_name'] }}" class="footer-logo" />
        @endif
        <div class="footer-name">{{ $settings['store_name'] }}</div>
        @if(!empty($settings['store_address']['address']))
            <div class="footer-detail">{{ $settings['store_address']['address'] }}, {{ $settings['store_address']['city'] }}, {{ $settings['store_address']['state'] }} {{ $settings['store_address']['zip'] }}</div>
        @endif
        @if(!empty($settings['store_phone']))
            <div class="footer-detail"><a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a></div>
        @endif
        @if(!empty($settings['order_email']))
            <div class="footer-detail"><a href="mailto:{{ $settings['order_email'] }}">{{ $settings['order_email'] }}</a></div>
        @endif
        <div class="footer-powered">Powered by <a href="{{ config('app.url') }}">SimpleOrder</a></div>
    </div>

</div>
</div>
</body>
</html>
