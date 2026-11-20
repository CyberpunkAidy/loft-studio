<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** loftstudio.app/owners: the page ads point to, for studio owners, phone first. */
class OwnersPageTest extends TestCase
{
    public function test_it_speaks_to_owners_with_one_next_step(): void
    {
        $this->get('/owners')->assertOk()
            ->assertSee('For aerial and pole studio owners')
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertSee('href="'.route('demo').'"', false)->assertSee('See it with a sample studio')
            ->assertSee('href="'.route('signup').'"', false)->assertSee('Start free for 30 days')
            ->assertSee('$49 a month', false)->assertSee('Loft takes nothing from your sales.')
            ->assertSee('Every silk, hoop and pole, booked')->assertSee('Switching is on us')
            ->assertSee('Do my members need to download an app?')
            ->assertSee('<input type="hidden" name="page" value="owners">', false);
    }

    public function test_a_phone_visitor_asks_for_the_link_from_it(): void
    {
        Mail::fake();
        $this->get('/owners?utm_source=larven&utm_campaign=owners')->assertOk();
        $this->post('/send-link', ['link_email' => 'ana@example.com', 'page' => 'owners'])
            ->assertRedirect(route('owners').'#send-link')->assertSessionHas('link_sent', true);
        $lead = Lead::sole();
        $this->assertSame(['owners', 'larven'], [$lead->page, $lead->signup_source['utm_source'] ?? null]);
        $this->get('/owners')->assertSee('Sent.');
    }

    public function test_the_pixel_runs_on_it_and_signed_in_people_skip_the_form(): void
    {
        config(['loft.marketing.meta_pixel_id' => '1477938097807772']);
        $this->get('/owners')->assertSee('fbq(\'track\', "PageView");', false);

        $this->actingAs($this->userIn($this->studio(), 'owner'));
        $this->get('/owners')->assertOk()->assertDontSee('id="send-link"', false);
    }
}
