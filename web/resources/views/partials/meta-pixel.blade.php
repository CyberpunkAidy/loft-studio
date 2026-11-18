{{--
    Meta's pixel (decision 49), only where App\Support\MetaPixel allows it. $event: the event to
    send (PageView on marketing pages, CompleteRegistration after starting a studio). $conversionOnly:
    the page isn't a marketing page, so automatic button and page details are switched off.
--}}
@php
    $conversionOnly ??= false;
@endphp
<!-- Meta Pixel Code -->
<script>
(function () {
    // Not for browsers asking not to be tracked (Global Privacy Control), or set to a time zone in
    // Europe: Loft has no cookie banner for the EEA and UK, so it doesn't run there.
    var zone = '';
    try { zone = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
    if (navigator.globalPrivacyControl || /^Europe\/|^Atlantic\/(Reykjavik|Canary|Madeira|Azores|Faroe)$/.test(zone)) return;
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    @if ($conversionOnly)
    fbq('set', 'autoConfig', false, @json(\App\Support\MetaPixel::id()));
    @endif
    fbq('init', @json(\App\Support\MetaPixel::id()));
    fbq('track', @json($event));
})();
</script>
<!-- End Meta Pixel Code -->
