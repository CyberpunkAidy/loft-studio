@extends('layouts.platform')

{{-- The page ads point to (loftstudio.app/owners): for studio owners, phone first, one next step. --}}
@section('title', 'For studio owners')
@section('description', "Booking with apparatus spots, waivers, family accounts, check-in and payments for aerial and pole studios. {$price} a month, free for {$trialDays} days.")
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<section class="owners-hero" aria-labelledby="owners-title">
    <span class="eyebrow"><x-icon name="sparkles" /> For aerial and pole studio owners</span>
    <h1 id="owners-title">Your studio, run from <span class="gradient-text">one place.</span></h1>
    <p class="lead">Booking with apparatus spots, waivers, family accounts, check-in and payments, for {{ $price }} a month. Members book from their phone, and pay you straight into your own Stripe account.</p>
    <div class="actions">
        <a class="btn btn-large" href="{{ route('demo') }}">See it with a sample studio <x-icon name="arrow-right" /></a>
        <a class="btn btn-large btn-secondary" href="{{ route('signup') }}">Start free for {{ $trialDays }} days</a>
    </div>
    <p class="hero-note"><span><x-icon name="check" /> No card needed</span><span><x-icon name="check" /> We move your members over</span><span><x-icon name="check" /> Cancel any time</span></p>
    @guest
        @include('platform._send-link', ['page' => 'owners'])
    @endguest
</section>

<section class="owners-points" aria-label="What Loft does for your studio">
    <article class="owners-point">
        <div>
            <span class="feature-icon"><x-icon name="calendar-days" /></span>
            <h2>Every silk, hoop and pole, booked</h2>
            <p>Members choose their apparatus when they book. When one’s full they join its waitlist, and move up by themselves when someone cancels.</p>
        </div>
        <figure class="shot shot-phone owners-phone">
            <img src="{{ asset('images/home/book-phone.webp') }}" width="600" height="1271" loading="lazy"
                 alt="Booking Open Practice on a phone: choose who’s coming, then Silks (8 spots left) or Lyra (full, join the waitlist).">
        </figure>
    </article>

    <article class="owners-point">
        <div>
            <span class="feature-icon"><x-icon name="shield-check" /></span>
            <h2>Waivers and injuries, before anyone’s in the air</h2>
            <p>Everyone signs your waiver and answers your injury and photo questions before their first class. Parents book every child from one account.</p>
        </div>
        <figure class="shot shot-window">
            <img src="{{ asset('images/home/checkin.webp') }}" width="1200" height="1031" loading="lazy"
                 alt="The check-in roster for Kids Aerial Silks: each child with their age, parent’s phone number, silk number and a medical note.">
        </figure>
    </article>

    <article class="owners-point">
        <div>
            <span class="feature-icon"><x-icon name="wallet" /></span>
            <h2>Paid straight to you</h2>
            <p>Memberships, class packs, drop-ins and series, paid into your own Stripe account. {{ $fee > 0 ? 'Loft adds '.rtrim(rtrim(number_format($fee, 2), '0'), '.').'% to card sales.' : 'Loft takes nothing from your sales.' }}</p>
        </div>
        <figure class="shot shot-window">
            <img src="{{ asset('images/home/sales.webp') }}" width="1200" height="828" loading="lazy"
                 alt="The Sales page for November: total, online and in-person sales, and a list of membership, pack, drop-in and merchandise sales.">
        </figure>
    </article>

    <article class="owners-point">
        <div>
            <span class="feature-icon"><x-icon name="file-input" /></span>
            <h2>Switching is on us</h2>
            <p>Send your member list from your old software to <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> and we’ll move your members, families and class credits over for free.</p>
        </div>
        <figure class="shot shot-window">
            <img src="{{ asset('images/home/import.webp') }}" width="1100" height="803" loading="lazy"
                 alt="An import preview: 6 new accounts and 1 existing member, with each family’s children and any medical notes, before anything is imported.">
        </figure>
    </article>
</section>

<section class="owners-price card" aria-labelledby="owners-price-title">
    <h2 id="owners-price-title">{{ $price }} a month. <span class="gradient-text">Everything included.</span></h2>
    <ul class="ticks">
        <li><x-icon name="check" /> Unlimited members, staff and classes</li>
        <li><x-icon name="check" /> Free for {{ $trialDays }} days, no card needed</li>
        <li><x-icon name="check" /> Your price stays the same for as long as you subscribe</li>
    </ul>
    <div class="actions">
        <a class="btn btn-large" href="{{ route('demo') }}">See it with a sample studio</a>
        <a class="btn btn-large btn-secondary" href="{{ route('signup') }}">Start your free trial</a>
    </div>
</section>

<section class="owners-faq" aria-labelledby="owners-faq-title">
    <h2 id="owners-faq-title">Questions owners ask</h2>
    <div class="faq-list">
        <details open>
            <summary>Do my members need to download an app?</summary>
            <p>No. They book, sign and pay from your schedule link on any phone or computer. There’s an app too, for those who want it.</p>
        </details>
        <details>
            <summary>What happens after the free trial?</summary>
            <p>Subscribe for {{ $price }} a month to keep going, or don’t: nothing is charged without your card, and you can cancel any time.</p>
        </details>
        <details>
            <summary>Can I look around without setting anything up?</summary>
            <p>Yes. The sample studio has a month of classes, members, waivers and sales. Open it as the owner, switch to a member and book a class, and change anything you like. It’s yours for a day.</p>
        </details>
    </div>
    <p class="small muted">Something else? Email <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> and a person will answer.</p>
</section>
@endsection
