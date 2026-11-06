@extends('layouts.app')

@section('title', 'Client and admin consultation chat')

@section('content')
  @php
    $steps = [
      'Client login',
      'Admin live chat',
      'Admin collects details',
      'Identify legal category',
      'Search matching attorneys',
      'Display recommendations',
      'System sends best match',
      'Attorney accepts or declines',
      'Consultation confirmed',
    ];
    $status = $inquiry?->status ?? 'new';
    $doneThrough = match($status) {
      'clarifying' => 2,
      'reviewing' => 5,
      'referred' => 7,
      'declined' => 6,
      'accepted' => 9,
      default => 1,
    };
    $currentStep = $status === 'accepted' ? null : match($status) {
      'new' => 1,
      'clarifying' => 3,
      'reviewing' => 6,
      'referred' => 8,
      'declined' => 8,
      default => 1,
    };
    $showMatches = $inquiry && in_array($status, ['reviewing', 'referred', 'declined', 'accepted'], true);
  @endphp

  <div class="eyebrow">CLIENT CONSULTATION</div>
  <h1 class="page-title">Talk with the firm and find your attorney</h1>
  <p class="lead">Start by telling the admin team what you need. Staff will gather the details, and the system will automatically send your request to the best available attorney match.</p>

  @if(session('status'))
    <div class="glance-card" role="status" style="margin-bottom:20px;max-width:760px;">{{ session('status') }}</div>
  @endif

  @include('partials.consultation-process', ['steps' => $steps, 'doneThrough' => $doneThrough, 'currentStep' => $currentStep, 'ariaLabel' => 'Attorney matching progress'])

  @if($inquiry)
    <section class="glance-card consultation-chat" aria-labelledby="consultation-chat-title">
      <div class="consultation-chat__heading">
        <div><div class="k" id="consultation-chat-title">Conversation with the admin team</div><small>Non-English messages are automatically translated into English. Original messages are kept.</small></div>
      </div>
      <div class="consultation-admin-presence" data-admin-presence data-status-url="{{ route('consultation.admin-status') }}" data-online="{{ $adminOnline ? 'true' : 'false' }}" role="status" aria-live="polite">
        <span class="consultation-admin-presence__dot {{ $adminOnline ? 'is-online' : 'is-offline' }}" aria-hidden="true"></span>
        <strong data-presence-label>{{ $adminOnline ? 'Admin is online' : 'Admin is offline' }}</strong>
        <small data-presence-note>{{ $adminOnline ? 'A staff member can reply here.' : 'The automated assistant will reply until staff are available.' }}</small>
      </div>
      <p class="v">Your first message started this request. Staff collect the details needed for matching. Please keep sensitive or confidential documents out of chat.</p>
      <div class="consultation-chat__messages" aria-live="polite" data-message-thread data-status="{{ $inquiry->status }}" data-assigned-attorney="{{ $inquiry->attorney_id ?? '' }}" data-latest-message-id="{{ $inquiry->messages->last()?->id ?? '' }}" data-translate-url="{{ route('chat.translate') }}" data-poll-url="{{ route('consultation.messages.poll', $inquiry) }}">
        @forelse($inquiry->messages as $message)
          <article class="consultation-chat__message" data-role="{{ $message->sender_role }}" data-message-id="{{ $message->id }}">
            <strong>{{ $message->sender_role === 'admin' ? 'Firm staff' : ($message->sender_role === 'assistant' ? 'SAFALAW Assistant' : 'You') }}</strong>
            <small>{{ $message->created_at->format('M j, g:i A') }}</small>
            <p>{{ $message->body }}</p>
          </article>
        @empty
          <p>Your conversation is ready. Send the admin team a message to begin.</p>
        @endforelse
      </div>
      <form class="consultation-chat__composer" method="POST" action="{{ route('consultation.messages.store', $inquiry) }}">
        @csrf
        <div class="form-field"><label for="client-reply">Message the admin team</label><textarea id="client-reply" name="body" maxlength="2000" required></textarea></div>
        @error('body')<p role="alert" style="color:var(--red);">{{ $message }}</p>@enderror
        <button type="submit" class="btn-primary">Send message</button>
      </form>
    </section>
  @else
    <section class="glance-card consultation-chat" aria-labelledby="start-chat-title">
      <div class="consultation-chat__heading">
        <div><div class="k" id="start-chat-title">Start an admin conversation</div><small>Non-English messages are automatically translated into English. Original messages are kept.</small></div>
      </div>
      <div class="consultation-admin-presence" data-admin-presence data-status-url="{{ route('consultation.admin-status') }}" data-online="{{ $adminOnline ? 'true' : 'false' }}" role="status" aria-live="polite">
        <span class="consultation-admin-presence__dot {{ $adminOnline ? 'is-online' : 'is-offline' }}" aria-hidden="true"></span>
        <strong data-presence-label>{{ $adminOnline ? 'Admin is online' : 'Admin is offline' }}</strong>
        <small data-presence-note>{{ $adminOnline ? 'A staff member can reply here.' : 'The automated assistant will reply until staff are available.' }}</small>
      </div>
      <p class="v">Briefly explain your legal concern. The admin team will collect your location, budget, and preferred consultation method. Please avoid confidential details.</p>
      <form class="consultation-chat__composer" method="POST" action="{{ route('consultation.store') }}">
        @csrf
        <div class="form-field"><label for="opening-message">Your legal concern</label><textarea id="opening-message" name="body" maxlength="2000" placeholder="Tell the firm what brings you here. Avoid confidential details for now." required>{{ old('body') }}</textarea></div>
        @error('body')<p role="alert" style="color:var(--red);">{{ $message }}</p>@enderror
        <button type="submit" class="btn-primary">Start conversation</button>
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
    <div class="glance-card" style="margin:0 0 24px;max-width:760px;">
      We identified {{ $inquiry->practiceArea?->name ?? 'your legal category' }}, but no connected attorney account currently matches this category and intake. The firm will follow up with you.
    </div>
  @endif

  <div class="contact-info" style="margin-top:24px;">
    <div><div class="k">How the process works</div><div class="v">You start the conversation; staff gather intake details; the system identifies the category and automatically sends the request to the best available match.</div></div>
    <div><div class="k">Consultation confirmation</div><div class="v">The consultation is confirmed after the matched attorney accepts. You can check the progress tracker in your Client Portal.</div></div>
  </div>
@endsection
