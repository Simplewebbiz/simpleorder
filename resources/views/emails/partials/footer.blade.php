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
        <div class="footer-phone">
            <a href="tel:{{ $settings['store_phone'] }}">{{ $settings['store_phone'] }}</a>
        </div>
    @endif
    @if(!empty($settings['order_email']))
        <div class="footer-email">
            <a href="mailto:{{ $settings['order_email'] }}">{{ $settings['order_email'] }}</a>
        </div>
    @endif
    <div class="footer-powered">Powered by <a href="{{ config('app.url') }}">SimpleOrder</a></div>
</div>
