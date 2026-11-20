<?php

namespace Tests\Feature;

use App\Support\MetaPixel;
use Tests\TestCase;

/** Decision 49: Meta's pixel on Loft's own marketing pages only, honouring opt-outs. */
class MetaPixelTest extends TestCase
{
    private const ID = '1477938097807772';

    private const SCRIPT = 'connect.facebook.net/en_US/fbevents.js';

    protected function setUp(): void
    {
        parent::setUp();
        config(['loft.marketing.meta_pixel_id' => self::ID]);
    }

    public function test_it_runs_on_the_marketing_pages_only(): void
    {
        foreach (['/', '/pricing', '/demo', '/signup'] as $page) {
            $this->get($page)->assertOk()->assertSee(self::SCRIPT, false)
                ->assertSee('fbq(\'init\', "'.self::ID.'");', false)->assertSee('fbq(\'track\', "PageView");', false)
                ->assertDontSee('autoConfig', false);
        }
        // Not where people sign in, reset passwords (the link carries a token) or read the policies.
        foreach (['/login', '/forgot-password', '/reset-password/some-token', '/terms', '/privacy'] as $page) {
            $this->get($page)->assertOk()->assertDontSee(self::SCRIPT, false);
        }

        // Never a studio's pages: the schedule, a member's account, or the admin.
        $studio = $this->studio(['slug' => 'skyline']);
        $this->get('/skyline')->assertOk()->assertDontSee(self::SCRIPT, false);
        $this->actingAs($this->userIn($studio, 'member'))->get('/skyline/account')->assertOk()->assertDontSee(self::SCRIPT, false);
        $this->actingAs($this->userIn($studio, 'owner'))->get('/skyline/admin')->assertOk()->assertDontSee(self::SCRIPT, false);
        $this->get('/studios')->assertOk()->assertDontSee(self::SCRIPT, false);
    }

    public function test_it_skips_browsers_that_opt_out_and_runs_nowhere_unless_configured(): void
    {
        // Global Privacy Control, on the request and in the page.
        $this->get('/', ['Sec-GPC' => '1'])->assertOk()->assertDontSee(self::SCRIPT, false);
        $this->get('/')->assertSee('navigator.globalPrivacyControl', false)->assertSee('^Europe\/', false);

        // The button on the privacy page.
        $this->get('/privacy')->assertSee('Don’t share my visits with Meta', false)->assertSee('id="ads"', false);
        $this->post('/privacy/ads')->assertRedirect(route('privacy').'#ads')->assertCookie(MetaPixel::OPT_OUT_COOKIE, '1');
        $this->withCookie(MetaPixel::OPT_OUT_COOKIE, '1')->get('/')->assertOk()->assertDontSee(self::SCRIPT, false);
        $this->withCookie(MetaPixel::OPT_OUT_COOKIE, '1')->get('/privacy')->assertSee('It’s off for this browser.', false)->assertDontSee('Don’t share my visits with Meta', false);
        // Every platform page links to the choice.
        $this->withCookie(MetaPixel::OPT_OUT_COOKIE, '0')->get('/pricing')->assertSee('Your privacy choices');

        // Not configured (anywhere but production, unless META_PIXEL_ID is set): nowhere.
        config(['loft.marketing.meta_pixel_id' => null]);
        $this->get('/')->assertOk()->assertDontSee(self::SCRIPT, false);
        config(['loft.marketing.meta_pixel_id' => 'not-an-id']);
        $this->get('/')->assertOk()->assertDontSee(self::SCRIPT, false);
    }

    public function test_a_new_studio_is_counted_once_without_the_page_view(): void
    {
        $this->post('/signup', [
            'first_name' => 'Olivia', 'last_name' => 'Owens', 'email' => 'olivia@example.com', 'password' => 'long-enough',
            'studio_name' => 'Skyline Aerial', 'slug' => 'skyline', 'timezone' => 'America/Denver', 'currency' => 'usd', 'terms' => '1', 'heard_from' => 'search',
        ])->assertRedirect('/skyline/admin')->assertSessionHas(MetaPixel::SESSION, [['CompleteRegistration', []]]);

        $this->get('/skyline/admin')->assertOk()
            ->assertSee('fbq(\'track\', "CompleteRegistration");', false)
            ->assertSee('fbq(\'set\', \'autoConfig\', false, "'.self::ID.'");', false) // no automatic button or page details on a studio's admin
            ->assertDontSee('PageView', false);
        $this->get('/skyline/admin')->assertOk()->assertDontSee(self::SCRIPT, false); // once

        // Opted out: not counted.
        $this->post('/logout');
        $this->withCookie(MetaPixel::OPT_OUT_COOKIE, '1')->post('/signup', [
            'first_name' => 'Sam', 'last_name' => 'Lee', 'email' => 'sam@example.com', 'password' => 'long-enough',
            'studio_name' => 'Silks by Sam', 'slug' => 'silks', 'timezone' => 'America/Denver', 'currency' => 'usd', 'terms' => '1', 'heard_from' => 'search',
        ])->assertRedirect('/silks/admin');
        $this->withCookie(MetaPixel::OPT_OUT_COOKIE, '1')->get('/silks/admin')->assertOk()->assertDontSee(self::SCRIPT, false);
    }

    public function test_the_privacy_policy_says_where_it_runs(): void
    {
        $this->get('/privacy')->assertOk()
            ->assertSee('Advertising on Loft’s own pages', false)
            ->assertSee('It’s never loaded on a studio’s pages (schedule, booking, payments, waivers, accounts), in the admin, or in the app.', false)
            ->assertSee('Nothing from a studio’s pages or the app, such as your bookings, payments, waivers, family or health answers, is ever used for advertising or shared with advertisers.', false)
            ->assertSee('Global Privacy Control, which we honour', false)
            ->assertDontSee('We don’t use advertising or analytics cookies', false);
    }
}
