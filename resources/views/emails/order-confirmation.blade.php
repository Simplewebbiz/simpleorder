<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order #{{ $order->increment_id }} Confirmed</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #1a1a1a; }
.outer { padding: 32px 16px; }
.wrapper { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }

/* Header */
.email-header { background: #1a1a1a; padding: 28px 32px; text-align: center; }
.store-logo { max-height: 64px; max-width: 240px; object-fit: contain; display: block; margin: 0 auto; }
.store-name-text { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.02em; }
.store-tagline { color: #9ca3af; font-size: 13px; margin-top: 4px; }

/* Contact bar */
.contact-bar { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 10px 32px; display: flex; gap: 24px; flex-wrap: wrap; }
.contact-item { font-size: 12px; color: #6b7280; }
.contact-item a { color: #6b7280; text-decoration: none; }
.contact-item strong { color: #374151; }

/* Body */
.body { padding: 32px; }
.order-badge { display: inline-block; background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 20px; margin-bottom: 16px; }
.order-number { font-size: 30px; font-weight: 800; color: #1a1a1a; margin-bottom: 6px; }
.confirmed-msg { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; color: #15803d; font-size: 14px; font-weight: 500; margin-bottom: 28px; }
.confirmed-msg span { margin-right: 6px; }

/* Progress bar */
.progress { display: flex; margin-bottom: 32px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
.prog-step { flex: 1; text-align: center; padding: 10px 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; background: #f9fafb; color: #9ca3af; border-right: 1px solid #e5e7eb; }
.prog-step:last-child { border-right: none; }
.prog-step.active { background: #e85d04; color: #fff; }
.prog-step.complete { background: #22c55e; color: #fff; }

/* Section */
.section { margin-bottom: 28px; }
.section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.info-item { font-size: 14px; }
.info-label { color: #6b7280; font-size: 12px; margin-bottom: 2px; }
.info-value { color: #1a1a1a; font-weight: 500; }

/* Method box */
.method-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; font-size: 14px; }
.method-box strong { display: block; margin-bottom: 4px; color: #1d4ed8; }
.method-box p { color: #374151; margin-top: 6px; }

/* Items table */
.items-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.items-table th { text-align: left; padding: 10px 12px; background: #f9fafb; color: #6b7280; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 2px solid #e5e7eb; }
.items-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
.items-table tr:last-child td { border-bottom: none; }
.item-name { font-weight: 600; margin-bottom: 4px; }
.item-option { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px; margin-top: 2px; }
.item-option::before { content: '›'; color: #d1d5db; }
.item-note { font-size: 12px; color: #9ca3af; font-style: italic; margin-top: 4px; }

/* Totals */
.totals { border-top: 2px solid #f3f4f6; margin-top: 4px; }
.total-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; font-size: 14px; }
.total-row.grand { background: #1a1a1a; color: #fff; font-size: 16px; font-weight: 700; border-radius: 0 0 8px 8px; padding: 12px; }

/* Track button */
.track-section { text-align: center; margin: 28px 0; }
.track-btn { display: inline-block; background: #e85d04; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-weight: 700; font-size: 15px; letter-spacing: 0.01em; }
.track-hint { color: #9ca3af; font-size: 12px; margin-top: 8px; }

/* Payment chip */
.payment-chip { display: inline-flex; align-items: center; gap: 8px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 14px; font-size: 14px; }
.card-brand { font-weight: 700; text-transform: uppercase; color: #374151; }
.card-last4 { color: #6b7280; }

/* Footer */
.email-footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 32px; text-align: center; }
.footer-logo { max-height: 36px; max-width: 140px; object-fit: contain; margin: 0 auto 10px; display: block; opacity: 0.7; }
.footer-name { font-weight: 700; font-size: 14px; color: #374151; margin-bottom: 6px; }
.footer-address, .footer-phone, .footer-email { font-size: 12px; color: #9ca3af; margin-bottom: 3px; }
.footer-address a, .footer-phone a, .footer-email a { color: #9ca3af; text-decoration: none; }
.footer-powered { font-size: 11px; color: #d1d5db; margin-top: 12px; }
.footer-powered a { color: #d1d5db; }
</style>
</head>
<body>
<div class="outer">
<div class="wrapper">

    {{-- ── Header with logo or store name ── --}}
    <div class="email-header">
        @if(!empty($settings['logo_url']))
            <img src="{{ $settings['logo_url'] }}" alt="{{ $settings['store_name'] }} Logo" class="store-logo" />
        @else
            <div class="store-name-text">{{ $settings['store_name'] }}</div>
        @endif
        <div class="store-tagline">Order Confirmation</div>
    </div>

    {{-- ── Contact bar ── --}}
    @if(!empty($settings['store_phone']) || !empty($settings['store_address']['address']) || !empty($settings['order_email']))
    <div class="contact-bar">
        @if(!empty($settings['store_address']['address']))
            <div class="contact-item">📍 {{ $settings['store_address']['address'] }}, {{ $settings['store_address']['city'] }}, {{ $settings['store_address']['state'] }}</div>
        @endif
        @if(!empty($settings['store_phone']))
            <div class="contact-item">📞 <a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a></div>
        @endif
        @if(!empty($settings['order_email']))
            <div class="contact-item">✉️ <a href="mailto:{{ $settings['order_email'] }}">{{ $settings['order_email'] }}</a></div>
        @endif
    </div>
    @endif

    <div class="body">

        <div class="order-badge">New Order</div>
        <div class="order-number">Order #{{ $order->increment_id }}</div>

        <div class="confirmed-msg"><span>✓</span> Your order is confirmed and being prepared!</div>

        {{-- Progress --}}
        <div class="progress">
            <div class="prog-step active">Placed</div>
            <div class="prog-step">Received</div>
            <div class="prog-step">{{ $order->method === 'delivery' ? 'On the Way' : 'Ready' }}</div>
            <div class="prog-step">Complete</div>
        </div>

        {{-- Method --}}
        <div class="section">
            <div class="section-title">Order Method</div>
            @if($order->method === 'pickup')
                <div class="method-box">
                    <strong>🏪 Pickup Order</strong>
                    @if(!empty($settings['store_address']['address']))
                        <p>Pick up at:<br>
                        <strong>{{ $settings['store_address']['address'] }}, {{ $settings['store_address']['city'] }}, {{ $settings['store_address']['state'] }} {{ $settings['store_address']['zip'] }}</strong></p>
                    @endif
                    <p>We'll email you when it's ready!</p>
                </div>
            @else
                <div class="method-box">
                    <strong>🚗 Delivery Order</strong>
                    <p>Delivering to:<br>
                    <strong>{{ $order->delivery_address }}, {{ $order->delivery_city }}, {{ $order->delivery_state }} {{ $order->delivery_zip }}</strong></p>
                    <p>We'll email you when it's on its way!</p>
                </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="section">
            <div class="section-title">Items Ordered</div>
            <table class="items-table">
                <thead>
                    <tr><th>Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Price</th></tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->name }}</div>
                            @foreach($item->options as $option)
                                @foreach($option->values as $val)
                                    <div class="item-option">{{ $option->label }}: {{ $val->label }}
                                        @if($val->price > 0) (+${{ number_format($val->price, 2) }})@endif
                                    </div>
                                @endforeach
                            @endforeach
                            @if($item->comments)
                                <div class="item-note">Note: {{ $item->comments }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;color:#6b7280;">{{ $item->qty }}</td>
                        <td style="text-align:right;font-weight:600;">${{ number_format($item->lineTotal(), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="totals">
                <div class="total-row"><span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
                <div class="total-row"><span>Tax</span><span>${{ number_format($order->tax, 2) }}</span></div>
                @if($order->delivery > 0)
                    <div class="total-row"><span>Delivery Fee</span><span>${{ number_format($order->delivery, 2) }}</span></div>
                @endif
                @if($order->tip > 0)
                    <div class="total-row"><span>Tip — Thank you! 🙏</span><span>${{ number_format($order->tip, 2) }}</span></div>
                @endif
                <div class="total-row grand"><span>Total Charged</span><span>${{ number_format($order->total, 2) }}</span></div>
            </div>
        </div>

        {{-- Track button --}}
        <div class="track-section">
            <a href="{{ $trackingUrl }}" class="track-btn">Track Your Order →</a>
            <div class="track-hint">Live status updates at the link above</div>
        </div>

        {{-- Contact info --}}
        <div class="section">
            <div class="section-title">Your Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value">{{ $order->contactName() }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $order->contact_email }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $order->contact_phone }}</div>
                </div>
            </div>
        </div>

        {{-- Payment --}}
        <div class="section">
            <div class="section-title">Payment</div>
            <div class="payment-chip">
                <span class="card-brand">{{ $order->card_brand }}</span>
                <span class="card-last4">•••• {{ $order->card_last4 }}</span>
                <span style="color:#9ca3af;">·</span>
                <span style="font-weight:600;">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        @if($order->comments)
        <div class="section">
            <div class="section-title">Order Notes</div>
            <p style="font-size:14px;color:#374151;">{{ $order->comments }}</p>
        </div>
        @endif

    </div>{{-- /body --}}

    {{-- ── Footer with logo + full contact ── --}}
    <div class="email-footer">
        @if(!empty($settings['logo_url']))
            <img src="{{ $settings['logo_url'] }}" alt="{{ $settings['store_name'] }}" class="footer-logo" />
        @endif
        <div class="footer-name">{{ $settings['store_name'] }}</div>
        @if(!empty($settings['store_address']['address']))
            <div class="footer-address">
                {{ $settings['store_address']['address'] }},
                {{ $settings['store_address']['city'] }},
                {{ $settings['store_address']['state'] }}
                {{ $settings['store_address']['zip'] }}
            </div>
        @endif
        @if(!empty($settings['store_phone']))
            <div class="footer-phone"><a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a></div>
        @endif
        @if(!empty($settings['order_email']))
            <div class="footer-email"><a href="mailto:{{ $settings['order_email'] }}">{{ $settings['order_email'] }}</a></div>
        @endif
        <div class="footer-powered">Powered by <a href="{{ config('app.url') }}">SimpleOrder</a></div>
    </div>

</div>{{-- /wrapper --}}
</div>
</body>
</html>
