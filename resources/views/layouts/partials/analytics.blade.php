@if(config('app.env') === 'production' && config('services.google_analytics.id'))
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ config('services.google_analytics.id') }}');
    </script>
@endif

@if(in_array(config('app.env'), ['staging', 'production']) && config('services.sentry.dsn'))
    <!-- Sentry Error Tracking -->
    <script src="https://browser.sentry-cdn.com/7.x/bundle.min.js" crossorigin="anonymous"></script>
    <script>
      Sentry.init({
        dsn: "{{ config('services.sentry.dsn') }}",
        environment: "{{ config('app.env') }}"
      });
    </script>
@endif