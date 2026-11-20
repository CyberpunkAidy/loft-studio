<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Meta's pixel, for advertising Loft to studios (decisions 49 and 50). It runs on Loft's own
 * marketing pages, and sends a few events on the page after something happens: a studio signed up
 * (CompleteRegistration), someone started the demo or asked for a link to Loft (Lead). It never
 * runs on a real studio's pages, where members book, pay, sign waivers and answer health questions.
 * It isn't loaded for a browser sending Global Privacy Control, or one that opted out on the privacy
 * page; the snippet itself also skips browsers set to a European time zone.
 */
final class MetaPixel
{
    /** The marketing pages it runs on (route names). Not sign-in, password resets or the studio list. */
    public const PAGES = ['home', 'pricing', 'demo', 'signup'];

    /** Set by "Don't share my visits with Meta" on the privacy page. */
    public const OPT_OUT_COOKIE = 'loft_no_ads';

    /** Events waiting for the next page that loads the pixel. */
    public const SESSION = 'meta_pixel_events';

    public static function id(): ?string
    {
        $id = (string) config('loft.marketing.meta_pixel_id');

        return preg_match('/^\d{5,20}$/', $id) ? $id : null;
    }

    /** Configured, and this browser hasn't asked not to be tracked. */
    public static function allowed(Request $request): bool
    {
        return self::id() !== null && ! self::optedOut($request);
    }

    /** It should run on this page: one of the marketing pages. */
    public static function onPage(Request $request): bool
    {
        return self::allowed($request) && $request->routeIs(...self::PAGES);
    }

    /** Global Privacy Control, or the opt-out on the privacy page. */
    public static function optedOut(Request $request): bool
    {
        return $request->header('Sec-GPC') === '1' || $request->cookie(self::OPT_OUT_COOKIE) === '1';
    }

    /** Send $event on the next page that loads the pixel (after a redirect), if this browser allows it. */
    public static function queue(Request $request, string $event, array $params = []): void
    {
        if (self::allowed($request) && $request->hasSession()) {
            $request->session()->push(self::SESSION, [$event, $params]);
        }
    }

    /** @return list<array{0: string, 1: array}> the waiting events, now taken */
    public static function pull(Request $request): array
    {
        return $request->hasSession() ? (array) $request->session()->pull(self::SESSION, []) : [];
    }
}
