<?php

namespace Tests\Feature;

use App\Mail\LoftLink;
use App\Models\Lead;
use App\Support\MetaPixel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Decision 50: "Email me a link for my computer", and Meta events for demos and link requests. */
class SendLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
    }

    public function test_a_phone_visitor_gets_a_link_for_their_computer(): void
    {
        $this->get('/?utm_source=larven&utm_medium=facebook&utm_campaign=fall')->assertOk()
            ->assertSee('On your phone? Try it on your computer.', false)->assertSee('action="'.route('send-link').'"', false)
            ->assertSee('You can also email me about Loft later (optional)');

        $this->post('/send-link', ['link_email' => 'nope', 'page' => 'home'])->assertSessionHasErrors('link_email');
        $this->post('/send-link', ['link_email' => 'Ana@Example.com', 'page' => 'home'])
            ->assertRedirect(route('home').'#send-link')->assertSessionHas('link_sent', true);

        Mail::assertQueued(LoftLink::class, fn (LoftLink $m) => $m->hasTo('ana@example.com') && ! $m->followUp
            && $m->hasReplyTo((string) config('loft.support_email')) && $m->envelope()->subject === 'Your link to try Loft');
        $lead = Lead::sole();
        $this->assertSame(['ana@example.com', 'home', null], [$lead->email, $lead->page, $lead->follow_up_consent_at]);
        $this->assertNotNull($lead->link_sent_at);
        $this->assertSame(['utm_source' => 'larven', 'utm_medium' => 'facebook', 'utm_campaign' => 'fall'], array_intersect_key($lead->signup_source ?? [], array_flip(['utm_source', 'utm_medium', 'utm_campaign'])));
        $this->get('/')->assertSee('Sent.')->assertDontSee('Email me the link');

        // The email: the demo and signup, tagged so the visit it starts is known to come from it.
        $html = (new LoftLink)->render();
        $this->assertStringContainsString(e(route('demo', LoftLink::TAGS)), $html);
        $this->assertStringContainsString(e(route('signup', LoftLink::TAGS)), $html);
        $this->assertStringContainsString('We won’t email you again unless you start a studio.', $html);

        // Asking again the same day sends nothing more, with the same answer.
        $this->post('/send-link', ['link_email' => 'ana@example.com', 'page' => 'demo', 'follow_up' => '1'])
            ->assertRedirect(route('demo').'#send-link')->assertSessionHas('link_sent', true);
        Mail::assertQueuedCount(1);
        $this->assertNotNull(Lead::latest('id')->first()->follow_up_consent_at);

        // A bot filling the hidden field gets the same answer and nothing.
        $this->post('/send-link', ['link_email' => 'bot@example.com', 'page' => 'home', 'website' => 'spam'])->assertSessionHas('link_sent', true);
        $this->assertSame(0, Lead::where('email', 'bot@example.com')->count());
    }

    public function test_starting_a_studio_marks_the_request_and_old_requests_are_deleted(): void
    {
        $this->post('/send-link', ['link_email' => 'olivia@example.com', 'page' => 'home', 'follow_up' => '1']);
        $this->post('/logout');
        $this->post('/signup', [
            'first_name' => 'Olivia', 'last_name' => 'Owens', 'email' => 'Olivia@example.com', 'password' => 'long-enough',
            'studio_name' => 'Skyline Aerial', 'slug' => 'skyline', 'timezone' => 'America/Denver', 'currency' => 'usd', 'terms' => '1', 'heard_from' => 'search',
        ])->assertRedirect('/skyline/admin');
        $lead = Lead::sole();
        $this->assertSame('skyline', $lead->studio->slug);
        $this->assertNotNull($lead->converted_at);

        $this->artisan('loft:leads')->expectsOutputToContain('Started a studio: 1. Said we could follow up: 1.')->assertSuccessful();

        Lead::create(['email' => 'old@example.com', 'page' => 'home'])->forceFill(['created_at' => now()->subDays(Lead::KEEP_DAYS + 1)])->save();
        $this->artisan('loft:housekeeping')->assertSuccessful();
        $this->assertSame(['olivia@example.com'], Lead::pluck('email')->all());
    }

    public function test_only_phones_see_it_and_signed_in_people_never_do(): void
    {
        $css = file_get_contents(public_path('css/app.css'));
        $this->assertMatchesRegularExpression('/\.send-link \{ display: none; \}\s*@media \(max-width: 760px\)/', $css);
        $this->get('/demo')->assertOk()->assertSee('id="send-link"', false);

        $studio = $this->studio();
        $this->actingAs($this->userIn($studio, 'owner'));
        $this->get('/')->assertOk()->assertDontSee('id="send-link"', false);
    }

    public function test_meta_hears_about_demos_and_link_requests_once(): void
    {
        config(['loft.marketing.meta_pixel_id' => '1477938097807772']);

        $this->post('/send-link', ['link_email' => 'ana@example.com', 'page' => 'home'])
            ->assertSessionHas(MetaPixel::SESSION, [['Lead', ['content_name' => 'email_link']]]);
        $this->get('/')->assertSee('fbq(\'track\', "PageView");', false)->assertSee('fbq(\'track\', "Lead", {"content_name":"email_link"});', false);
        $this->get('/')->assertDontSee('"Lead"', false); // once

        // Starting the demo: counted on the demo's first page, with automatic details off.
        $this->fakeStripe();
        $response = $this->post('/demo');
        $response->assertSessionHas(MetaPixel::SESSION, [['Lead', ['content_name' => 'demo']]]);
        $this->get($response->headers->get('Location'))->assertOk()
            ->assertSee('fbq(\'track\', "Lead", {"content_name":"demo"});', false)->assertSee('autoConfig', false)->assertDontSee('PageView', false);

        // Opted out: nothing is queued.
        $this->post('/logout');
        $this->withCookie(MetaPixel::OPT_OUT_COOKIE, '1')->post('/send-link', ['link_email' => 'sam@example.com', 'page' => 'home'])
            ->assertSessionMissing(MetaPixel::SESSION);
    }

    public function test_the_privacy_policy_explains_link_requests(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('id="links"', false)
            ->assertSee('We use it to send the link, to see whether you come back and start a studio, and to follow up only if you said we could. We delete it 90 days after you asked.', false)
            ->assertSee('starts the demo or asks for a link to Loft, the next page tells Meta once that it happened (not their email or anything they typed)', false);
    }
}
