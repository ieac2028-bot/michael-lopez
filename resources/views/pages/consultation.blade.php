@extends('layouts.app')

@section('title', 'Client and admin consultation chat')

@section('content')
  @php
    // Client-friendly wording (the old labels read like internal staff tasks).
    $steps = [
      'Signed in',
      'Chat with our team',
      'Finding attorneys',
      'Your matches',
      'Request sent',
      'Attorney response',
      'Consultation confirmed',
    ];
    $status = $inquiry?->status ?? 'new';
    $doneThrough = match($status) {
      'clarifying' => 1,
      'reviewing' => 2,
      'referred' => 5,
      'declined' => 5,
      'accepted' => 7,
      default => 1,
    };
    // Step 1 is complete for a signed-in client, so the conversation begins at step 2.
    $currentStep = $status === 'accepted' ? null : match($status) {
      'new' => 2,
      'clarifying' => 2,
      'reviewing' => 3,
      'referred' => 6,
      'declined' => 6,
      default => 2,
    };
    $summaryNote = match($status) {
      'new' => 'Next: send your first message to our team.',
      'clarifying' => 'Our team is collecting the details needed to match you.',
      'reviewing' => 'We found attorneys who fit your matter.',
      'referred' => 'Your request is with the attorney. We’ll update you here.',
      'declined' => 'The firm will follow up with you directly.',
      'accepted' => 'Your consultation is confirmed.',
      default => null,
    };
    $showMatches = $inquiry && in_array($status, ['reviewing', 'referred', 'declined', 'accepted'], true);
  @endphp

  <div class="cp-intro">
    <div class="eyebrow">CLIENT CONSULTATION</div>
    <h1 class="page-title">Let’s find the right attorney for you</h1>
    <p class="lead">Tell our team what you need. We’ll gather a few details, then send your request to the best available attorney match.</p>
  </div>

  @if(session('status'))
    <div class="glance-card cp-flash" role="status">{{ session('status') }}</div>
  @endif

  <section class="cp-progress" aria-label="Your consultation progress">
    @include('partials.consultation-process', ['steps' => $steps, 'doneThrough' => $doneThrough, 'currentStep' => $currentStep, 'ariaLabel' => 'Attorney matching progress', 'showSummary' => true, 'summaryNote' => $summaryNote])
  </section>

  <div class="cp-layout">
    <div class="cp-main">
      @if($inquiry)
        <section class="glance-card consultation-chat" aria-labelledby="consultation-chat-title">
          <div class="consultation-chat__heading">
            <div class="k" id="consultation-chat-title">Conversation with our team</div>
            <small>Non-English messages are automatically translated into English. Original messages are kept.</small>
          </div>
          <div class="consultation-admin-presence" data-admin-presence data-status-url="{{ route('consultation.admin-status') }}" data-online="{{ $adminOnline ? 'true' : 'false' }}" role="status" aria-live="polite">
            <span class="consultation-admin-presence__dot {{ $adminOnline ? 'is-online' : 'is-offline' }}" aria-hidden="true"></span>
            <strong data-presence-label>{{ $adminOnline ? 'Admin is online' : 'Admin is offline' }}</strong>
            <small data-presence-note>{{ $adminOnline ? 'A staff member can reply here.' : 'The automated assistant will reply until staff are available.' }}</small>
          </div>
          <div class="consultation-chat__messages" aria-live="polite" data-message-thread data-status="{{ $inquiry->status }}" data-assigned-attorney="{{ $inquiry->attorney_id ?? '' }}" data-latest-message-id="{{ $inquiry->messages->last()?->id ?? '' }}" data-translate-url="{{ route('chat.translate') }}" data-poll-url="{{ route('consultation.messages.poll', $inquiry) }}">
            @forelse($inquiry->messages as $message)
              <article class="consultation-chat__message" data-role="{{ $message->sender_role }}" data-message-id="{{ $message->id }}">
                <strong>{{ $message->sender_role === 'admin' ? 'Firm staff' : ($message->sender_role === 'assistant' ? 'GPMO Assistant' : 'You') }}</strong>
                <small>{{ $message->created_at->format('M j, g:i A') }}</small>
                <p>{{ $message->body }}</p>
              </article>
            @empty
              <p>Your conversation is ready. Send our team a message to begin.</p>
            @endforelse
          </div>
          <p class="cp-notice" role="note">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
            <span>Please keep sensitive or confidential documents out of chat.</span>
          </p>
          <form class="consultation-chat__composer" method="POST" action="{{ route('consultation.messages.store', $inquiry) }}">
            @csrf
            <div class="form-field"><label for="client-reply">Message our team</label><textarea id="client-reply" name="body" maxlength="2000" placeholder="Type your reply…" required></textarea></div>
            @error('body')<p role="alert" style="color:var(--red);">{{ $message }}</p>@enderror
            <div class="cp-composer__footer">
              <span class="cp-composer__hint">Up to 2,000 characters</span>
              <button type="submit" class="cp-btn">Send message <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
            </div>
          </form>
        </section>
      @else
        <section class="glance-card consultation-chat" aria-labelledby="start-chat-title">
          <div class="consultation-chat__heading">
            <div class="k" id="start-chat-title">Start a conversation with our team</div>
            <small>Non-English messages are automatically translated into English. Original messages are kept.</small>
          </div>
          <div class="consultation-admin-presence" data-admin-presence data-status-url="{{ route('consultation.admin-status') }}" data-online="{{ $adminOnline ? 'true' : 'false' }}" role="status" aria-live="polite">
            <span class="consultation-admin-presence__dot {{ $adminOnline ? 'is-online' : 'is-offline' }}" aria-hidden="true"></span>
            <strong data-presence-label>{{ $adminOnline ? 'Admin is online' : 'Admin is offline' }}</strong>
            <small data-presence-note>{{ $adminOnline ? 'A staff member can reply here.' : 'The automated assistant will reply until staff are available.' }}</small>
          </div>
          <div class="cp-guide">
            <p class="cp-guide__lead">Briefly explain your legal concern in your own words — no legal terms needed.</p>
            <p class="cp-guide__label">Our team will then ask about</p>
            <ul class="cp-guide__list">
              <li>Your location</li>
              <li>Your budget</li>
              <li>Your preferred consultation method</li>
            </ul>
            <p class="cp-notice" role="note">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
              <span>Please avoid confidential details for now.</span>
            </p>
          </div>
          <form class="consultation-chat__composer" method="POST" action="{{ route('consultation.store') }}">
            @csrf
            <div class="form-field"><label for="opening-message">Your legal concern</label><textarea id="opening-message" name="body" maxlength="2000" placeholder="Tell the firm what brings you here." required>{{ old('body') }}</textarea></div>
            <div class="cp-chips" role="group" aria-label="Starter suggestions">
              <span class="cp-chips__label">Not sure where to start?</span>
              <button type="button" class="cp-chip" data-fill="I need help with a business or contract matter.">Business or contract</button>
              <button type="button" class="cp-chip" data-fill="I have a family or personal legal concern.">Family or personal</button>
              <button type="button" class="cp-chip" data-fill="I need advice about property or real estate.">Property</button>
              <button type="button" class="cp-chip" data-fill="I’m not sure what kind of legal help I need.">Not sure yet</button>
            </div>
            @error('body')<p role="alert" style="color:var(--red);">{{ $message }}</p>@enderror
            <div class="cp-composer__footer">
              <span class="cp-composer__hint">Up to 2,000 characters</span>
              <button type="submit" class="cp-btn">Start conversation <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
            </div>
          </form>
        </section>
      @endif

      @if($showMatches && $recommendedAttorneys->isNotEmpty())
        <section class="glance-card consultation-recommendations" aria-labelledby="recommendations-title">
          <div class="consultation-recommendations__heading">
            <div><div class="eyebrow">YOUR ATTORNEY MATCH</div><h2 id="recommendations-title">A strong match for your matter</h2><p>The firm identified {{ $inquiry->practiceArea?->name }} and automatically routed your request based on practice focus, location, budget, and availability.</p></div>
          </div>
          <div class="attorney-grid consultation-recommendations__grid">
            @foreach($recommendedAttorneys as $attorney)
              <article class="recommended-attorney-card">
                <div class="recommended-attorney-card__identity">
                  <span class="recommended-attorney-card__avatar" aria-hidden="true">{{ $attorney->initials }}</span>
                  <div><span class="recommended-attorney-card__eyebrow">{{ $attorney->practiceArea?->name }}</span><h3>{{ $attorney->name }}</h3><p>{{ $attorney->specialization_note }}</p></div>
                </div>
                <div class="recommended-attorney-card__details">
                  <span>{{ $attorney->years_experience }} years' experience</span>
                  <strong>{{ $attorney->matching_score }}% match</strong>
                @if($attorney->budget_min !== null && $attorney->budget_max !== null)
                  <small>Indicative budget: ₱{{ number_format($attorney->budget_min) }}–₱{{ number_format($attorney->budget_max) }}</small>
                @endif
                @if($inquiry->attorney_id === $attorney->id)
                  <span class="recommended-attorney-card__status {{ $status === 'accepted' ? 'is-confirmed' : '' }}">{{ $status === 'accepted' ? 'Consultation confirmed' : ($status === 'declined' ? 'No match available · firm follow-up' : 'Request sent · awaiting response') }}</span>
                @endif
                </div>
              </article>
            @endforeach
          </div>
        </section>
      @elseif($showMatches)
        <div class="glance-card cp-flash">
          We identified {{ $inquiry->practiceArea?->name ?? 'your legal category' }}, but no connected attorney account currently matches this category and intake. The firm will follow up with you.
        </div>
      @endif
    </div>

    <aside class="cp-aside" aria-label="Helpful information">
      <div class="cp-aside__card">
        <h2>What happens next</h2>
        <ol class="cp-next">
          <li><strong>Tell us your concern</strong>Share a short summary in your own words.</li>
          <li><strong>We gather the details</strong>Our team asks about your location, budget, and preferred consultation method.</li>
          <li><strong>We find your match</strong>Your request goes to the best available attorney. Your consultation is confirmed once they accept.</li>
        </ol>
      </div>
      <div class="cp-aside__card">
        <h2>Track your request anytime</h2>
        <p>You can check this progress tracker from your <a href="{{ route('client-portal') }}">Client Portal</a>.</p>
      </div>
    </aside>
  </div>

  <script src="{{ asset('js/client-portal.js') }}" defer></script>
@endsection
