<div class="email-header">
    @if(!empty($settings['logo_url']))
        <div class="logo-wrap">
            <img src="{{ $settings['logo_url'] }}" alt="{{ $settings['store_name'] }} Logo" class="store-logo" />
        </div>
    @else
        <div class="store-name-text">{{ $settings['store_name'] }}</div>
    @endif
</div>
