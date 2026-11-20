@extends('mail.layout')

@section('title', 'Your link to try '.config('app.name'))
@section('preheader', 'Open this on your computer: try the demo studio, or start your own free for '.$trialDays.' days.')
@section('bar', 'background:#5b4bf5;background-image:linear-gradient(90deg,#5b4bf5,#c05ad8,#ff8a5c);')

@section('header')
    <a href="{{ $homeUrl }}" style="text-decoration:none;"><img src="{{ asset('images/loft-logo-email.png') }}" width="78" height="32" alt="{{ config('app.name') }}" style="display:block;border:0;"></a>
@endsection

@section('content')
    <x-mail.headline>Here’s your link</x-mail.headline>
    <p style="margin:0 0 12px;">Open this on your computer. The demo is a whole studio to click around: a month of classes, members, waivers and sales, as the owner or as a member. Nothing in it is real, and it takes one click.</p>
    <x-mail.button :url="$demoUrl" background="#5b4bf5">Try the demo</x-mail.button>
    <p style="margin:18px 0 6px;">When you’re ready, your own studio is free for {{ $trialDays }} days, with no card needed. Then it’s {{ $price }} a month, with unlimited members and staff, and members pay you straight into your own Stripe account.</p>
    <p style="margin:0 0 12px;"><a href="{{ $signupUrl }}" style="color:#5b4bf5;font-weight:600;">Start your free trial</a> · We’ll move your members over from your old system for free.</p>
    <p style="margin:18px 0 0;font-size:14px;color:#6c6776;">Questions? Reply to this email and a person will answer.</p>
@endsection

@section('footer')
    <p style="margin:0 0 6px;">You asked for this link on loftstudio.app. {{ $followUp ? 'You said we could also email you about Loft later; reply “stop” any time and we won’t.' : 'We won’t email you again unless you start a studio.' }}</p>
    <p style="margin:0;">{{ config('app.name') }} · Scheduling, memberships, waivers and billing for studios.</p>
@endsection
