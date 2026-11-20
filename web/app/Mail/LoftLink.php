<?php

namespace App\Mail;

use App\Services\StudioBilling;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** The link someone asked for on their phone, to open Loft on their computer (decision 50). */
class LoftLink extends Mailable implements ShouldQueue
{
    use Queueable;

    /** Links back carry these tags, so the visit they start is known to have come from this email. */
    public const TAGS = ['utm_source' => 'loft', 'utm_medium' => 'email', 'utm_campaign' => 'send_link'];

    public function __construct(public bool $followUp = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), (string) config('app.name')),
            replyTo: [new Address((string) config('loft.support_email'), (string) config('app.name'))],
            subject: 'Your link to try '.config('app.name'),
            tags: ['loft', 'send_link'],
        );
    }

    public function content(): Content
    {
        $billing = app(StudioBilling::class);

        return new Content(view: 'mail.loft-link', text: 'mail.loft-link-text', with: [
            'price' => $billing->priceLabel(),
            'trialDays' => $billing->trialDays(),
            'demoUrl' => route('demo', self::TAGS),
            'signupUrl' => route('signup', self::TAGS),
            'homeUrl' => route('home', self::TAGS),
        ]);
    }
}
