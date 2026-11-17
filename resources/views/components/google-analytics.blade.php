@php
    $measurementId = config('services.google_analytics.measurement_id');
    $analyticsEvent = session('analytics_event');
@endphp

@if (filled($measurementId) && ! app()->environment('testing'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ rawurlencode($measurementId) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
        window.kawalTrackEvent = function (eventName, parameters = {}) {
            if (typeof window.gtag === 'function') {
                window.gtag('event', eventName, parameters);
            }
        };
        window.gtag('js', new Date());
        window.gtag('config', @js($measurementId));
        @if (is_string($analyticsEvent) && $analyticsEvent !== '')
            window.kawalTrackEvent(@js($analyticsEvent));
        @endif
    </script>
@endif
