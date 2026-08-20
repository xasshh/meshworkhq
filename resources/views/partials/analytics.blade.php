@php
    $measurementId = config('services.google_analytics.id');
@endphp

{{-- Nothing is loaded unless a measurement ID is configured, so development
     and test runs never pollute the property with fake traffic. --}}
@if(filled($measurementId) && app()->environment('production'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($measurementId), {
            // Livewire navigation swaps pages without a full load, so page
            // views are sent manually below instead.
            send_page_view: true,
        });

        document.addEventListener('livewire:navigated', () => {
            gtag('event', 'page_view', {
                page_location: window.location.href,
                page_title: document.title,
            });
        });
    </script>
@endif
