{{--
    Meta's pixel (decisions 49 and 50), only where App\Support\MetaPixel allows it. $pageView: send
    PageView (marketing pages). $events: [name, params] pairs waiting from the last request (a sign-up,
    a demo started, a link asked for). $conversionOnly: not a marketing page (a new studio's or a demo's
    admin), so automatic button and page details are switched off.
--}}
@php
    $pageView ??= false;
    $events ??= [];
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
    @if ($pageView)
    fbq('track', "PageView");
    @endif
    @foreach ($events as [$name, $params])
    fbq('track', @json($name){!! $params ? ', '.json_encode($params, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : '' !!});
    @endforeach
})();
</script>
<!-- End Meta Pixel Code -->
