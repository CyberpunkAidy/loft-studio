{{-- "Email me a link for my computer" (decision 50). Shown on phones only (CSS). $page: home or demo. --}}
<section class="send-link" id="send-link" aria-labelledby="send-link-title">
    @if (session('link_sent'))
        <p class="send-link-done" role="status"><x-icon name="mail-check" /> <span><strong>Sent.</strong> Open the email on your computer to try Loft properly. It can take a minute to arrive.</span></p>
    @else
        <h2 id="send-link-title">On your phone? Try it on your computer.</h2>
        <p>Loft is easiest to look around on a bigger screen. We’ll email you a link to the demo and the free trial.</p>
        <form method="post" action="{{ route('send-link') }}" class="form">
            @csrf
            <input type="hidden" name="page" value="{{ $page }}">
            <div class="send-link-row">
                <label class="sr-only" for="link_email">Email</label>
                <input class="input" id="link_email" type="email" name="link_email" value="{{ old('link_email') }}" placeholder="you@yourstudio.com" autocomplete="email" required>
                <button class="btn">Email me the link</button>
            </div>
            @error('link_email')<small class="error">{{ $message }}</small>@enderror
            <x-checkbox name="follow_up" label="You can also email me about Loft later (optional)" />
            <div class="hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            <p class="small muted" style="margin:0">We use your email to send the link, to see whether you come back, and to follow up only if you tick the box. We delete it after {{ \App\Models\Lead::KEEP_DAYS }} days. <a href="{{ route('privacy') }}#links">Privacy Policy</a></p>
        </form>
    @endif
</section>
