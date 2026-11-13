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
      'Fee proposed',
      'Payment verification',
      'Consultation confirmed',
    ];
    $status = $inquiry?->status ?? 'new';
    $paymentStatus = $inquiry?->payment_status;
    $doneThrough = match(true) {
      $paymentStatus === 'verified' => 9,
      in_array($paymentStatus, ['pending', 'verifying'], true) => 7,
      in_array($paymentStatus, ['fee_proposed', 'awaiting_client', 'canceled', 'expired'], true) => 6,
      $status === 'clarifying' => 1,
      $status === 'reviewing' => 2,
      in_array($status, ['referred', 'declined'], true) => 5,
      $status === 'accepted' => 9,
      default => 1,
    };
    // Step 1 is complete for a signed-in client, so the conversation begins at step 2.
    $currentStep = $paymentStatus === 'verified' || $status === 'accepted' ? null : match(true) {
      $paymentStatus === 'fee_proposed' || $paymentStatus === 'awaiting_client' || $paymentStatus === 'canceled' || $paymentStatus === 'expired' => 7,
      $paymentStatus === 'pending' || $paymentStatus === 'verifying' => 8,
      $status === 'referred' || $status === 'declined' => 6,
      $status === 'new' => 2,
      $status === 'clarifying' => 2,
      $status === 'reviewing' => 3,
      default => 2,
    };
    $summaryNote = match(true) {
      $paymentStatus === 'fee_proposed' => 'The attorney accepted. Firm staff will confirm the fee and payment details in your chat.',
      $paymentStatus === 'awaiting_client' => 'Review the confirmed fee and payment instructions, then choose whether to pay or cancel.',
      $paymentStatus === 'pending' => 'The office is waiting to verify your reported payment.',
      $paymentStatus === 'verifying' => 'Firm staff are checking the payment against the office account.',
      $paymentStatus === 'verified' => 'Payment verified. Your consultation is confirmed.',
      $paymentStatus === 'canceled' => 'Payment request canceled before payment. Your consultation is not confirmed.',
      $paymentStatus === 'expired' => 'The unpaid payment request expired. Contact the office to continue.',
      $status === 'new' => 'Next: send your first message to our team.',
      $status === 'clarifying' => 'Our team is collecting the details needed to match you.',
      $status === 'reviewing' => 'We found attorneys who fit your matter.',
      $status === 'referred' => 'Your request is with the attorney. We’ll update you here.',
      $status === 'declined' => 'The firm will follow up with you directly.',
      $status === 'accepted' => 'Your consultation is confirmed.',
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
        <div class="matching-progress matching-progress--client" data-client-matching-transition role="status" aria-live="polite" aria-atomic="true" hidden>
          <span class="matching-progress__ring"><span class="matching-progress__label">Matching…</span></span>
          <span class="matching-progress__message">Finding the attorney best suited to your matter.</span>
        </div>
      @endif
      @if($inquiry)
        <section class="glance-card consultation-chat" data-client-live-chat-panel aria-labelledby="consultation-chat-title" @if($status === 'accepted') hidden @endif>
          <div class="consultation-chat__heading">
            <div class="k" id="consultation-chat-title">Conversation with our team</div>
            <small>Non-English messages are automatically translated into English. Original messages are kept.</small>
          </div>
          <div class="consultation-admin-presence" data-admin-presence data-status-url="{{ route('consultation.admin-status') }}" data-online="{{ $adminOnline ? 'true' : 'false' }}" role="status" aria-live="polite">
            <span class="consultation-admin-presence__dot {{ $adminOnline ? 'is-online' : 'is-offline' }}" aria-hidden="true"></span>
            <strong data-presence-label>{{ $adminOnline ? 'Admin is online' : 'Admin is offline' }}</strong>
            <small data-presence-note>{{ $adminOnline ? 'A staff member can reply here.' : 'The automated assistant will reply until staff are available.' }}</small>
          </div>
          <div class="consultation-chat__messages" aria-live="polite" data-message-thread data-status="{{ $inquiry->status }}" data-payment-status="{{ $inquiry->payment_status ?? '' }}" data-refund-requested-at="{{ $inquiry->refund_requested_at?->toISOString() ?? '' }}" data-assigned-attorney="{{ $inquiry->attorney_id ?? '' }}" data-latest-message-id="{{ $inquiry->messages->last()?->id ?? '' }}" data-translate-url="{{ route('chat.translate') }}" data-poll-url="{{ route('consultation.messages.poll', $inquiry) }}">
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

      @if($inquiry && $inquiry->payment_status)
        <section class="glance-card client-payment-card" aria-labelledby="client-payment-title">
          <div class="consultation-recommendations__heading"><div><div class="eyebrow">CONSULTATION PAYMENT</div><h2 id="client-payment-title">{{ ucfirst(str_replace('_', ' ', $inquiry->payment_status)) }}</h2></div><strong>₱{{ number_format((float) $inquiry->consultation_fee, 2) }}</strong></div>
          @if($inquiry->payment_status === 'fee_proposed')
            <p>The attorney proposed this fee. Firm staff will confirm the payment instructions here before you decide whether to pay.</p>
          @elseif($inquiry->payment_status === 'awaiting_client')
            <p>Review the fee and payment instructions. Your consultation will be confirmed only after the office verifies that the payment reached its account.</p>
            <div class="client-payment-card__instructions">{{ $inquiry->payment_instructions }}</div>
            <div class="client-payment-card__policy"><strong>Cancellation / refund terms</strong><p>{{ $inquiry->payment_refund_terms }}</p></div>
            <p class="client-payment-card__expiry">Please respond by {{ $inquiry->payment_expires_at?->format('M j, Y g:i A') }}.</p>
            <form class="client-payment-card__reference" method="POST" action="{{ route('consultation.payment.sent', $inquiry) }}">
              @csrf
              <label for="payment-reference">Payment reference (if available)</label>
              <input id="payment-reference" name="payment_reference" type="text" maxlength="120" autocomplete="off" placeholder="Enter the GCash or bank transaction reference">
              <label class="client-payment-card__ack"><input type="checkbox" name="acknowledge_terms" value="1" required> I’ve reviewed the fee, payment instructions, and cancellation / refund terms.</label>
              <button type="submit" class="btn-primary" onclick="return confirm('Only continue if you have already sent the payment. The office will verify it before confirming your consultation.')">I’ve sent the payment</button>
            </form>
            <form method="POST" action="{{ route('consultation.payment.cancel', $inquiry) }}" class="client-payment-card__cancel">@csrf<button type="submit" class="btn-ghost" onclick="return confirm('Cancel this payment request before paying? The consultation will not be confirmed.')">Cancel before paying</button></form>
          @elseif(in_array($inquiry->payment_status, ['pending', 'verifying'], true))
            <p>{{ $inquiry->payment_status === 'pending' ? 'Your payment notice was sent' : 'The office is checking its GCash or bank account' }}{{ $inquiry->payment_requested_at ? ' on '.$inquiry->payment_requested_at->format('M j, Y g:i A') : '' }}. The consultation is not confirmed until the office verifies receipt.</p>
            <p>Reference: <strong>{{ $inquiry->payment_reference ?: 'Not provided' }}</strong></p>
            <form method="POST" action="{{ route('consultation.payment.refund-review', $inquiry) }}">@csrf<button type="submit" class="btn-ghost">Request cancellation / refund review</button></form>
          @elseif($inquiry->payment_status === 'verified')
            <div class="confirmed-meeting-card" aria-labelledby="confirmed-meeting-title">
              <div class="confirmed-meeting-card__header"><span class="confirmed-meeting-card__icon" aria-hidden="true">✓</span><div><span class="eyebrow">CONSULTATION CONFIRMED</span><h3 id="confirmed-meeting-title">Your meeting details</h3></div></div>
              <div class="confirmed-meeting-card__attorney"><span class="recommended-attorney-card__avatar" aria-hidden="true">{{ $inquiry->attorney?->initials ?? 'AT' }}</span><div><strong>{{ $inquiry->attorney?->name ?? 'Assigned attorney' }}</strong><span>{{ $inquiry->practiceArea?->name ?? 'Your legal matter' }}</span></div></div>
              <dl class="confirmed-meeting-card__details">
                <div><dt>Consultation method</dt><dd>{{ ucfirst(str_replace('_', ' ', $inquiry->consultation_method ?? 'consultation')) }}</dd></div>
                @if($inquiry->preferred_date)<div><dt>Preferred date</dt><dd>{{ $inquiry->preferred_date->format('l, M j, Y') }}</dd></div>@endif
                @if($inquiry->preferred_time)<div><dt>Preferred time</dt><dd>{{ $inquiry->preferred_time }}</dd></div>@endif
                <div><dt>Case reference</dt><dd>{{ $inquiry->caseRecord?->reference_no ?? 'Being prepared' }}</dd></div>
              </dl>
              <div class="confirmed-meeting-card__contact">
                <strong>Contact your attorney</strong>
                @php($attorneyEmail = $inquiry->attorney?->contact_email)
                @if($attorneyEmail)<a href="mailto:{{ $attorneyEmail }}">{{ $attorneyEmail }}</a>@endif
                @if($inquiry->attorney?->contact_phone)<a href="tel:{{ preg_replace('/[^+0-9]/', '', $inquiry->attorney->contact_phone) }}">{{ $inquiry->attorney->contact_phone }}</a>@endif
                @if(!$attorneyEmail && !$inquiry->attorney?->contact_phone)<p>Contact the firm through this chat to coordinate directly with your attorney.</p>@endif
              </div>
              @if($inquiry->attorney)
                <div class="confirmed-meeting-card__profile">
                  @if($attorneyPracticeAreas->isNotEmpty())<div><strong>Areas of practice</strong><span>{{ $attorneyPracticeAreas->join(' · ') }}</span></div>@endif
                  @if($inquiry->attorney->specialization_note)<div><strong>Professional profile</strong><span>{{ $inquiry->attorney->specialization_note }}</span></div>@endif
                  @if($inquiry->attorney->years_experience !== null)<div><strong>Experience</strong><span>{{ $inquiry->attorney->years_experience }} {{ \Illuminate\Support\Str::plural('year', $inquiry->attorney->years_experience) }}</span></div>@endif
                  @if($inquiry->attorney->spoken_languages)<div><strong>Languages</strong><span>{{ implode(', ', $inquiry->attorney->spoken_languages) }}</span></div>@endif
                </div>
              @endif
              <div class="confirmed-meeting-card__access"><strong>How to meet</strong><p>{{ $inquiry->consultation_access_details ?: 'Contact your attorney using the details above to coordinate meeting access.' }}</p>@if($inquiry->meeting_access_url)<a class="btn-primary confirmed-meeting-card__join" href="{{ $inquiry->meeting_access_url }}" target="_blank" rel="noopener noreferrer">Open meeting link</a>@endif</div>
              <p class="confirmed-meeting-card__note">Payment verified {{ $inquiry->payment_verified_at?->format('M j, Y g:i A') }} · Reference {{ $inquiry->payment_reference }}{{ $inquiry->caseRecord ? ' · Case '.$inquiry->caseRecord->reference_no : '' }}</p>
              <p class="confirmed-meeting-card__note">Your date and time above are the preferences submitted with your request. Contact the attorney using the details above if the confirmed meeting time or access needs adjustment.</p>
              <div class="confirmed-meeting-card__next-request"><div><strong>Need help with another matter?</strong><p>Start a separate matching request. This confirmed consultation and its case history will remain saved.</p></div><a class="btn-primary" href="{{ route('consultation.new') }}">Start a new matching request</a></div>
            </div>
            <form method="POST" action="{{ route('consultation.payment.refund-review', $inquiry) }}">@csrf<button type="submit" class="btn-ghost">Request cancellation / refund review</button></form>
          @elseif($inquiry->payment_status === 'canceled')
            <p>You canceled before reporting payment. No consultation was confirmed. Your live chat remains available if you want to contact the office.</p>
            <a class="btn-ghost client-payment-card__new-request" href="{{ route('consultation.new') }}">Start a new matching request</a>
          @elseif($inquiry->payment_status === 'expired')
            <p>This unpaid payment request expired. The consultation was not confirmed. Message the office in the chat if you would like to continue.</p>
            <a class="btn-ghost client-payment-card__new-request" href="{{ route('consultation.new') }}">Start a new matching request</a>
          @endif
          @if($inquiry->refund_requested_at)<p class="client-payment-card__expiry">Your cancellation / refund review request was sent {{ $inquiry->refund_requested_at->format('M j, Y g:i A') }}. The office must review it; the payment status and confirmed consultation have not changed.</p>@endif
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
                  <div><span class="recommended-attorney-card__eyebrow">{{ $inquiry->practiceArea?->name ?? $attorney->practiceArea?->name }}</span><h3>{{ $attorney->name }}</h3><p>{{ $attorney->specialization_note }}</p></div>
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
