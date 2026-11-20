<?php

namespace App\Http\Controllers;

use App\Mail\LoftLink;
use App\Models\Lead;
use App\Support\MetaPixel;
use App\Support\SignupSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * "Email me a link for my computer" (decision 50): most people who find Loft from an ad are on a
 * phone, and won't set up studio software there. They get one email with links to the demo and the
 * signup, and Loft keeps the request (Lead) to see whether they came back.
 */
class SendLinkController extends Controller
{
    /** One email to an address a day, however often it's asked for. */
    private const RESEND_AFTER_HOURS = 24;

    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'link_email' => ['required', 'email', 'max:190'],
            'page' => ['required', Rule::in(Lead::PAGES)],
            'follow_up' => ['nullable', 'boolean'],
        ], [], ['link_email' => 'email']);
        $back = redirect()->to(route($data['page'] === 'demo' ? 'demo' : 'home').'#send-link');
        if ($request->filled('website')) { // honeypot
            return $back->with('link_sent', true);
        }

        $email = strtolower($data['link_email']);
        $recent = Lead::where('email', $email)->where('link_sent_at', '>=', now()->subHours(self::RESEND_AFTER_HOURS))->exists();
        Lead::create([
            'email' => $email,
            'page' => $data['page'],
            'signup_source' => SignupSource::recorded($request),
            'follow_up_consent_at' => $request->boolean('follow_up') ? now() : null,
            'link_sent_at' => $recent ? null : now(),
        ]);
        if (! $recent) {
            Mail::to($email)->queue(new LoftLink($request->boolean('follow_up')));
        }
        MetaPixel::queue($request, 'Lead', ['content_name' => 'email_link']);

        // The same answer whether or not it was sent, so the form can't be used to check addresses.
        return $back->with('link_sent', true);
    }
}
