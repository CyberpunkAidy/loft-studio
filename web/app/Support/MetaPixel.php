<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Meta's pixel, for advertising Loft to studios (decision 49). It runs only on Loft's own
 * marketing pages, and once on the page after someone starts a studio (to count the sign-up),
 * never on a studio's pages, where members book, pay, sign waivers and answer health questions.
 * It isn't loaded for a browser sending Global Privacy Control, or one that opted out on the
 * privacy page; the snippet itself also skips browsers set to a European time zone.
 */
final class MetaPixel
{
    /** The marketing pages it runs on (route names). Not sign-in, password resets or the studio list. */
    public const PAGES = ['home', 'pricing', 'demo', 'signup'];

    /** Set by "Don't share my visits with Meta" on the privacy page. */
    public const OPT_OUT_COOKIE = 'loft_no_ads';

    /** The session flash that counts a new studio on the page it lands on. */
    public const EVENT_FLASH = 'meta_pixel_event';

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
}
