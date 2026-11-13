@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
  <div class="admin-page-head admin-hero">
    <div>
      <div class="eyebrow">GPMO STAFF CONSOLE</div>
      <h1 class="page-title">Welcome back, {{ auth()->user()->name }}</h1>
      <p class="lead">{{ now()->format('l, F j, Y') }} · A quick view of new inquiries, active matters, and attorney availability.</p>
    </div>
  </div>

  @if(session('admin_status'))<div class="glance-card admin-flash" role="status">{{ session('admin_status') }}</div>@endif
  @if($errors->any())<div class="glance-card admin-errors" role="alert">{{ $errors->first() }}</div>@endif
  @if(session('attorney_recovery_codes'))
    <section class="glance-card admin-flash" role="status" aria-label="One-time attorney recovery codes">
      <strong>Save these recovery codes for {{ session('attorney_recovery_email') }}</strong>
      <p>They are displayed once. Give the codes and initial password to the attorney through a secure channel.</p>
      <ul style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;list-style:none;padding:0;margin:10px 0;">
        @foreach(session('attorney_recovery_codes') as $code)<li><code style="display:block;padding:8px;border:1px solid var(--line);border-radius:8px;text-align:center;user-select:all;">{{ $code }}</code></li>@endforeach
      </ul>
    </section>
  @endif

  <section class="admin-metrics" aria-label="Dashboard overview">
    <a class="admin-metric admin-metric--review" href="#admin-inquiries"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16v12H8l-4 4z"/><path d="M8 9h8M8 12h5"/></svg></i><span>Needs review</span><strong>{{ $pendingInquiryCount }}</strong><small>inquiries</small></a>
    <a class="admin-metric admin-metric--progress" href="#admin-cases"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h18v13H3z"/><path d="M8 7V4h8v3M3 13h18"/></svg></i><span>In progress</span><strong>{{ $openCaseCount }}</strong><small>matters</small></a>
    <a class="admin-metric admin-metric--available" href="#admin-attorneys"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></i><span>Available</span><strong>{{ $availableAttorneyCount }}</strong><small>attorneys</small></a>
    <a class="admin-metric admin-metric--total" href="#admin-cases"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h7"/></svg></i><span>Total</span><strong>{{ $cases->count() }}</strong><small>cases</small></a>
  </section>

  <div class="admin-tabs" data-admin-tabs>
    <div class="admin-tab-list" role="tablist" aria-label="Admin dashboard sections" aria-orientation="horizontal">
      <button class="admin-tab" id="tab-admin-inquiries" type="button" role="tab" aria-controls="admin-inquiries" aria-selected="true">Recent inquiries @if($pendingInquiryCount)<b>{{ $pendingInquiryCount }}</b>@endif</button>
      <button class="admin-tab" id="tab-admin-cases" type="button" role="tab" aria-controls="admin-cases" aria-selected="false" tabindex="-1">Case tracking</button>
      <button class="admin-tab" id="tab-admin-attorneys" type="button" role="tab" aria-controls="admin-attorneys" aria-selected="false" tabindex="-1">Attorney availability</button>
      <button class="admin-tab" id="tab-admin-attorney-accounts" type="button" role="tab" aria-controls="admin-attorney-accounts" aria-selected="false" tabindex="-1">Attorney accounts</button>
      <button class="admin-tab" id="tab-admin-accounts" type="button" role="tab" aria-controls="admin-accounts" aria-selected="false" tabindex="-1">Administrator accounts</button>
    </div>

  <section class="admin-section admin-tab-panel" id="admin-inquiries" role="tabpanel" aria-labelledby="tab-admin-inquiries" data-inquiry-inbox data-poll-url="{{ route('admin.inquiries.poll') }}" data-latest-id="{{ $inquiries->max('id') ?? 0 }}" data-attorney-updated-at="{{ $attorneys->max('updated_at')?->toISOString() ?? '' }}">
    <div class="admin-section-head"><div><div class="eyebrow">INBOX</div><h2 id="admin-inquiries-title">Recent inquiries</h2></div><span>{{ $inquiries->count() }} total · {{ $pendingInquiryCount }} to review</span></div>
    @forelse($inquiries as $inquiry)
      <article class="admin-card">
        <div class="admin-card-head">
          <div><h3>{{ $inquiry->full_name }}</h3><p>{{ $inquiry->practiceArea?->name ?? 'General inquiry' }} · {{ $inquiry->created_at->format('M j, Y') }} · {{ $inquiry->email }}</p></div>
          <span class="admin-status-pill admin-status-pill--{{ $inquiry->status }}">{{ ucfirst($inquiry->status) }}</span>
        </div>
        <details class="admin-record-details" @if((int) session('expanded_inquiry_id') === $inquiry->id) open @endif>
          <summary>Review details and manage referral</summary>
        <div class="admin-data-grid">
          <div><strong>Registration</strong><span>{{ ucfirst($inquiry->client_type ?? 'General inquiry') }} · {{ $inquiry->client_location ?? 'Location not provided' }}</span></div>
          <div><strong>Area verification</strong><span>{{ $inquiry->practiceArea?->name ?? 'Unselected' }}</span></div>
          <div><strong>Availability</strong><span>{{ $inquiry->preferred_date?->format('M j, Y') ?? 'Not requested' }} {{ $inquiry->preferred_time ?? '' }}</span></div>
          <div><strong>Nationality / consultation method</strong><span>{{ $inquiry->nationality ?? 'Nationality not provided' }} · {{ ucfirst(str_replace('_', ' ', $inquiry->consultation_method ?? 'Not specified')) }}</span></div>
          <div><strong>Submitted</strong><span>{{ $inquiry->created_at->format('M j, Y g:i A') }}</span></div>
          <div><strong>Legal profile</strong><span>{{ $inquiry->legal_profile ?: 'Not provided' }}</span></div>
          <div><strong>Case clarification</strong><span>{{ $inquiry->message }}</span></div>
          <div class="admin-data-wide"><strong>Client requirements</strong><span>{{ $inquiry->client_requirements ?: 'Not provided' }}</span></div>
        </div>
        <div class="admin-workflow-actions">
          <div class="admin-inquiry-chat professional-live-chat">
            <strong>Client conversation</strong>
            <small class="live-chat-translation-note">Non-English messages are translated into English for review; original messages are kept.</small>
            <div class="professional-chat-thread" data-message-thread data-status="{{ $inquiry->status }}" data-payment-status="{{ $inquiry->payment_status ?? '' }}" data-refund-requested-at="{{ $inquiry->refund_requested_at?->toISOString() ?? '' }}" data-latest-message-id="{{ $inquiry->messages->last()?->id ?? '' }}" data-translate-url="{{ route('chat.translate') }}" data-poll-url="{{ route('admin.inquiries.messages.poll', $inquiry) }}">
            @if($inquiry->messages->isNotEmpty())
            @foreach($inquiry->messages as $message)
              <article class="consultation-chat__message" data-role="{{ $message->sender_role }}" data-message-id="{{ $message->id }}"><strong>{{ $message->sender_role === 'admin' ? 'Firm staff' : ($message->sender_role === 'assistant' ? 'GPMO Assistant' : 'Client') }}</strong><small>{{ $message->created_at->format('M j, g:i A') }}</small><p>{{ $message->body }}</p></article>
            @endforeach
            @else <p>No follow-up messages.</p> @endif
            </div>
            <form class="admin-inline-form" method="POST" action="{{ route('admin.inquiries.messages.store', $inquiry) }}">@csrf<label>Reply to client <textarea name="body" maxlength="2000" required></textarea></label><button type="submit" class="btn-primary">Send reply</button></form>
          </div>
          @if($inquiry->type === \App\Models\Inquiry::TYPE_CONSULTATION)
            <form class="admin-inline-form admin-intake-form" method="POST" action="{{ route('admin.inquiries.intake.update', $inquiry) }}">
              @csrf
              <h3>Collect client information and identify the legal category</h3>
              <label>Phone <input type="text" name="phone" value="{{ old('phone', $inquiry->phone) }}" maxlength="30"></label>
              <label>Client type
                <select name="client_type"><option value="">Select</option>@foreach(['individual', 'business', 'organization'] as $type)<option value="{{ $type }}" @selected(old('client_type', $inquiry->client_type) === $type)>{{ ucfirst($type) }}</option>@endforeach</select>
              </label>
              <label>Location <input type="text" name="client_location" value="{{ old('client_location', $inquiry->client_location) }}" maxlength="120"></label>
              <label>Nationality <input type="text" name="nationality" value="{{ old('nationality', $inquiry->nationality) }}" maxlength="100" placeholder="Enter client nationality"></label>
              <label>Legal category / practice area
                <select name="practice_area_id"><option value="">Select a category</option>@foreach($practiceAreas as $area)<option value="{{ $area->id }}" @selected(old('practice_area_id', $inquiry->practice_area_id) == $area->id)>{{ $area->name }}</option>@endforeach</select>
              </label>
              <label>Legal profile<textarea name="legal_profile" maxlength="2000">{{ old('legal_profile', $inquiry->legal_profile) }}</textarea></label>
              <label>Budget (PHP)
                <select name="budget_range"><option value="">Select range</option>@foreach(['0-25000' => 'Up to ₱25,000', '25000-75000' => '₱25,000–₱75,000', '75000-150000' => '₱75,000–₱150,000', '150000-500000' => '₱150,000–₱500,000', '500000-999999999' => 'Above ₱500,000'] as $range => $label)<option value="{{ $range }}" @selected(old('budget_range', $inquiry->budget_range) === $range)>{{ $label }}</option>@endforeach</select>
              </label>
              <label>Preferred consultation method
                <select name="consultation_method"><option value="">Select method</option><option value="in_person" @selected(old('consultation_method', $inquiry->consultation_method) === 'in_person')>In person</option><option value="video" @selected(old('consultation_method', $inquiry->consultation_method) === 'video')>Video call</option><option value="phone" @selected(old('consultation_method', $inquiry->consultation_method) === 'phone')>Phone call</option></select>
              </label>
              <label>Preferred date <input type="date" name="preferred_date" value="{{ old('preferred_date', $inquiry->preferred_date?->format('Y-m-d')) }}"></label>
              <label>Preferred time
                <select name="preferred_time"><option value="">Select time</option>@foreach(['Morning (9:00 AM – 12:00 PM)', 'Afternoon (1:00 PM – 5:00 PM)'] as $slot)<option value="{{ $slot }}" @selected(old('preferred_time', $inquiry->preferred_time) === $slot)>{{ $slot }}</option>@endforeach</select>
              </label>
              <label>Client requirements<textarea name="client_requirements" maxlength="2000">{{ old('client_requirements', $inquiry->client_requirements) }}</textarea></label>
              <div class="matching-progress" data-matching-progress role="status" aria-live="polite" aria-atomic="true" hidden>
                <span class="matching-progress__ring"><span class="matching-progress__label">Matching…</span></span>
              </div>
              <div class="admin-workflow-actions">
                <button type="submit" class="btn-primary">Save details and find best match</button>
              </div>
            </form>
            @if($inquiry->recommended_attorney_ids !== null)
              <p>System found {{ count($inquiry->recommended_attorney_ids) }} available match(es). Automatic assignment: <strong>{{ $inquiry->attorney?->name ?? 'No connected attorney match yet' }}</strong></p>
            @endif
          @else
          <form class="admin-inline-form" method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">
            @csrf @method('PATCH')
            <label>Status
              <select name="status">
                @foreach(['new', 'reviewing', 'clarifying', 'referred', 'accepted', 'declined', 'closed'] as $status)
                  <option value="{{ $status }}" @selected($inquiry->status === $status)>{{ ucfirst($status) }}</option>
                @endforeach
              </select>
            </label>
            <label>Recommended / assigned attorney
              <select name="attorney_id">
                <option value="">Unassigned</option>
                @foreach($attorneys as $attorney)
                  @if(!$inquiry->practice_area_id || $attorney->practice_area_id === $inquiry->practice_area_id)
                    <option value="{{ $attorney->id }}" @selected($inquiry->attorney_id === $attorney->id)>{{ $attorney->name }}{{ $attorney->is_available ? '' : ' (unavailable)' }}</option>
                  @endif
                @endforeach
              </select>
            </label>
            <button type="submit" class="btn-primary">Save referral</button>
          </form>
          @endif
          @if($inquiry->type === \App\Models\Inquiry::TYPE_CONSULTATION && $inquiry->payment_status)
            <section class="admin-payment-workflow" aria-label="Consultation payment workflow">
              <div class="admin-card-head"><div><h3>Consultation payment</h3><p>Fee: ₱{{ number_format((float) $inquiry->consultation_fee, 2) }}</p></div><span class="admin-status-pill">{{ ucfirst(str_replace('_', ' ', $inquiry->payment_status)) }}</span></div>
              @if($inquiry->refund_requested_at)<p class="admin-payment-refund-note">Client requested cancellation / refund review {{ $inquiry->refund_requested_at->format('M j, Y g:i A') }}. Review under the firm’s policy; no refund or cancellation is automatic.</p>@endif
              @if(in_array($inquiry->payment_status, ['fee_proposed', 'canceled', 'expired'], true))
                @if($inquiry->payment_status === 'canceled')<p>The client canceled before reporting payment. A new fee and deadline will need confirmation before another payment request.</p>@endif
                @if($inquiry->payment_status === 'expired')<p>The unpaid request expired {{ $inquiry->payment_expired_at?->format('M j, Y g:i A') }}. You can renew the request by confirming a current fee, payment details, and a new firm-approved deadline.</p>@endif
                <p>Attorney’s proposed fee: <strong>₱{{ number_format((float) $inquiry->proposed_consultation_fee, 2) }}</strong></p>
                <p>The attorney proposed this fee. Confirm the amount, GCash or bank instructions, and a firm-approved expiry before the client can choose to pay.</p>
                <form class="admin-inline-form" method="POST" action="{{ route('admin.inquiries.payment.confirm', $inquiry) }}">
                  @csrf
                  <label>Attorney’s proposed fee (PHP)<input type="number" name="consultation_fee" min="0.01" max="9999999999" step="0.01" required value="{{ old('consultation_fee', $inquiry->proposed_consultation_fee) }}" readonly></label>
                  <label>GCash or bank payment instructions<textarea name="payment_instructions" maxlength="2000" required>{{ old('payment_instructions', $inquiry->payment_instructions) }}</textarea><small>Include the payee name and the exact transfer details the client needs.</small></label>
                  <label>Firm-approved cancellation / refund terms<textarea name="payment_refund_terms" maxlength="2000" required>{{ old('payment_refund_terms', $inquiry->payment_refund_terms) }}</textarea><small>Enter the firm's approved terms; these will be shown before the client reports payment.</small></label>
                  <label>Payment request expiry<input type="datetime-local" name="payment_expires_at" min="{{ now()->format('Y-m-d\\TH:i') }}" value="{{ old('payment_expires_at') }}" required><small>Choose the expiry period approved by the firm.</small></label>
                  <button type="submit" class="btn-primary">Confirm fee and send payment details</button>
                </form>
              @elseif($inquiry->payment_status === 'awaiting_client')
                <p>Waiting for the client to pay or cancel. Expires {{ $inquiry->payment_expires_at?->format('M j, Y g:i A') }}.</p>
                <p class="admin-data-wide">{{ $inquiry->payment_instructions }}</p>
                <p><strong>Cancellation / refund terms:</strong> {{ $inquiry->payment_refund_terms }}</p>
              @elseif($inquiry->payment_status === 'pending')
                <p>Client reported payment {{ $inquiry->payment_requested_at?->format('M j, Y g:i A') }}. Reference: <strong>{{ $inquiry->payment_reference ?: 'Not provided' }}</strong>.</p>
                <form method="POST" action="{{ route('admin.inquiries.payment.verify-start', $inquiry) }}">@csrf<button type="submit" class="btn-primary">Start payment verification</button></form>
              @elseif($inquiry->payment_status === 'verifying')
                <p>Payment is being checked against the firm account. Client reference: <strong>{{ $inquiry->payment_reference ?: 'Not provided' }}</strong>.</p>
                <form class="admin-inline-form admin-consultation-access-form" method="POST" action="{{ route('admin.inquiries.payment.verify', $inquiry) }}">
                  @csrf
                  <label>Verified GCash / bank transaction reference<input type="text" name="payment_reference" maxlength="120" required value="{{ old('payment_reference', $inquiry->payment_reference) }}"></label>
                  <label>Secure meeting link (for video, optional)<input type="url" name="meeting_access_url" maxlength="500" placeholder="https://…" value="{{ old('meeting_access_url') }}"></label>
                  <label>Meeting access instructions<textarea name="consultation_access_details" maxlength="2000" required placeholder="Enter the confirmed venue, call-in details, or instructions for arranging the meeting.">{{ old('consultation_access_details') }}</textarea><small>This will be shown to the client after you verify payment.</small></label>
                  <button type="submit" class="btn-primary">Verify payment and confirm consultation</button>
                </form>
              @elseif($inquiry->payment_status === 'verified')
                <p>Payment verified {{ $inquiry->payment_verified_at?->format('M j, Y g:i A') }} by {{ $inquiry->paymentVerifier?->name ?? 'an administrator' }}. Transaction reference: <strong>{{ $inquiry->payment_reference }}</strong>. Consultation confirmed.</p>
              @endif
            </section>
          @endif
          @if($inquiry->caseRecord)<p>Case opened: <strong>{{ $inquiry->caseRecord->reference_no }}</strong></p>@endif
        </div>
        </details>
      </article>
    @empty
      <div class="admin-empty">No intake records yet.</div>
    @endforelse
  </section>

  <section class="admin-section admin-tab-panel" id="admin-cases" role="tabpanel" aria-labelledby="tab-admin-cases" hidden>
    <div class="admin-section-head"><div><div class="eyebrow">CASEWORK</div><h2 id="admin-cases-title">Case tracking</h2></div><span>{{ $cases->count() }} total</span></div>
    @forelse($cases as $case)
      <article class="admin-card">
        <div class="admin-card-head"><div><h3>{{ $case->reference_no }}</h3><p>{{ $case->practiceArea?->name ?? 'Practice area not set' }} · {{ $case->attorney?->name ?? 'Unassigned' }} · Updated {{ $case->updated_at->format('M j, Y') }}</p></div><span class="admin-status-pill admin-status-pill--{{ $case->status ?? 'active' }}">{{ ucfirst(str_replace('_', ' ', $case->status ?? 'active')) }}</span></div>
        <details class="admin-record-details">
          <summary>View case details and update progress</summary>
          <p class="admin-case-title">{{ $case->title }}</p>
          <p class="admin-case-email">Client: {{ $case->client_email }}</p>
        <div class="admin-data-grid">
          <div><strong>Submitted</strong><span>{{ $case->submitted_at?->format('M j, Y g:i A') ?? 'Not recorded' }}</span></div>
          <div><strong>Accepted</strong><span>{{ $case->accepted_at?->format('M j, Y g:i A') ?? 'Pending' }}</span></div>
          <div><strong>Submission to acceptance</strong><span>{{ $case->submitted_at && $case->accepted_at ? $case->submitted_at->diffForHumans($case->accepted_at, true) : 'Pending acceptance' }}</span></div>
          <div><strong>Case tracking span</strong><span>{{ $case->submitted_at ? $case->submitted_at->diffForHumans($case->resolved_at ?? now(), true) . ($case->resolved_at ? ' until resolution' : ' so far') : 'Not recorded' }}</span></div>
          <div><strong>Resolved</strong><span>{{ $case->resolved_at?->format('M j, Y') ?? 'In progress' }}</span></div>
          <div><strong>Client-facing timeline</strong><span>@foreach($case->timeline ?? [] as $step){{ $step['label'] }}@if(!$loop->last) → @endif @endforeach</span></div>
        </div>
        <form class="admin-case-form" method="POST" action="{{ route('admin.cases.update', $case) }}">
          @csrf @method('PATCH')
          <label>Status<select name="status">@foreach(['active', 'on_hold', 'resolved', 'closed'] as $status)<option value="{{ $status }}" @selected(($case->status ?? 'active') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></label>
          <label>Case outcome<input type="text" name="outcome" value="{{ $case->outcome }}" placeholder="Settlement, decision, referral, or other outcome"></label>
          <label class="admin-private-note">Private recorded testimony / case note<textarea name="testimony" rows="2" placeholder="Internal sample note only; do not enter confidential testimony.">{{ $case->testimony }}</textarea></label>
          <button type="submit" class="btn-primary">Update case record</button>
        </form>
        </details>
      </article>
    @empty
      <div class="admin-empty">No accepted referrals have been opened as cases.</div>
    @endforelse
  </section>

  <section class="admin-section admin-tab-panel" id="admin-attorneys" role="tabpanel" aria-labelledby="tab-admin-attorneys" hidden>
    <details class="admin-attorney-section">
    <summary class="admin-section-head"><div><div class="eyebrow">TEAM · MATCHING</div><h2 id="admin-attorneys-title">Attorney availability</h2></div><span>{{ $attorneys->count() }} profiles · {{ $availableAttorneyCount }} available</span></summary>
    <p class="admin-section-note">Manage whether attorney profiles can receive new matches, their service areas, and indicative budget range. Account creation is managed separately below.</p>
    <div class="admin-attorney-list">
      @foreach($attorneys as $index => $attorney)
        <div class="admin-card">
        <form class="admin-attorney-row" method="POST" action="{{ route('admin.attorneys.update', $attorney) }}">
          @csrf @method('PATCH')
          <strong class="admin-rank">#{{ $index + 1 }}</strong>
          <div><strong>{{ $attorney->name }}</strong><small>{{ $attorney->practiceArea?->name ?? 'Unassigned area' }} · {{ $attorney->years_experience }} years' experience</small></div>
          <input type="hidden" name="is_available" value="{{ $attorney->is_available ? '1' : '0' }}">
          @php
            $includedInMatching = $attorney->is_available && $attorney->accepting_matches && $attorney->account;
            $matchingLabel = $includedInMatching
              ? 'Included in matching'
              : (! $attorney->account ? 'Account not linked' : (! $attorney->accepting_matches ? 'Paused by attorney' : 'Unavailable by firm'));
          @endphp
          <div class="admin-available admin-match-status" role="status">
            <span class="admin-availability-dot {{ $includedInMatching ? 'is-included' : 'is-paused' }}" aria-hidden="true"></span>
            <span>{{ $matchingLabel }}</span>
          </div>
          <label class="admin-coverage">Service areas<input type="text" name="coverage_areas" value="{{ implode(', ', $attorney->coverage_areas ?? []) }}" placeholder="All Philippines or city/region names"></label>
          <label>Indicative budget range (PHP)<span style="display:flex;gap:6px"><input type="number" min="0" name="budget_min" value="{{ $attorney->budget_min }}" placeholder="Minimum"><input type="number" min="0" name="budget_max" value="{{ $attorney->budget_max }}" placeholder="Maximum"></span></label>
          <div class="admin-attorney-contacts">
            <label>Client contact email<input type="email" name="contact_email" maxlength="150" value="{{ $attorney->contact_email }}" placeholder="Public contact email"></label>
            <label>Client contact phone<input type="tel" name="contact_phone" maxlength="40" value="{{ $attorney->contact_phone }}" placeholder="Public contact number"></label>
          </div>
          <button type="submit" class="btn-ghost">Save</button>
        </form>
        </div>
      @endforeach
    </div>
    </details>
  </section>

  <section class="admin-section admin-tab-panel" id="admin-attorney-accounts" role="tabpanel" aria-labelledby="tab-admin-attorney-accounts" hidden>
    @php
      $attorneysWithoutAccounts = $attorneys->filter(fn ($attorney) => ! $attorney->account);
      $attorneyWithAccount = $attorneys->first(fn ($attorney) => ! $attorney->account && $attorney->id == old('attorney_profile_id')) ?? $attorneysWithoutAccounts->first();
    @endphp
    <div class="admin-section-head">
      <div><div class="eyebrow">TEAM · ACCESS</div><h2 id="admin-attorney-accounts-title">Create an attorney account</h2></div>
      <span>{{ $attorneysWithoutAccounts->count() }} profiles need an account</span>
    </div>
    <p class="admin-section-note">Create sign-in credentials and link them to an existing attorney profile.</p>

    @if($attorneyWithAccount)
      <div class="attorney-create-card">
        <div class="attorney-create-card__intro">
          <span class="attorney-create-card__eyebrow">ATTORNEY ACCESS</span>
          <h3>Set up a team member</h3>
          <p>Enter their account details below. Their login will open the workspace for the selected attorney profile.</p>
        </div>
        <form class="attorney-create-form" method="POST" action="{{ route('admin.attorneys.account.store', $attorneyWithAccount) }}" data-attorney-account-form data-route-template="{{ route('admin.attorneys.account.store', ['attorney' => '__ATTORNEY_ID__']) }}">
          @csrf
          <label class="attorney-create-form__field attorney-create-form__field--profile" for="attorney-account-profile">
            <span>Attorney profile</span>
            <select id="attorney-account-profile" data-attorney-profile-select required>
              @foreach($attorneysWithoutAccounts as $attorney)
                <option value="{{ $attorney->id }}" data-profile-name="{{ $attorney->name }}" @selected($attorneyWithAccount->id === $attorney->id)>{{ $attorney->name }} · {{ $attorney->practiceArea?->name ?? 'Practice area not set' }}</option>
              @endforeach
            </select>
            <small>Choose the attorney profile this login belongs to.</small>
          </label>
          <input type="hidden" name="attorney_profile_id" value="{{ old('attorney_profile_id', $attorneyWithAccount->id) }}" data-attorney-profile-id>
          <label class="attorney-create-form__field" for="attorney-account-name">
            <span>Full name</span>
            <input id="attorney-account-name" type="text" name="display_name" value="{{ old('display_name', $attorneyWithAccount->name) }}" autocomplete="name" required maxlength="150" data-attorney-account-name>
            @error('display_name')<small class="form-error">{{ $message }}</small>@enderror
          </label>
          <label class="attorney-create-form__field" for="attorney-account-email">
            <span>Email</span>
            <input id="attorney-account-email" type="email" name="attorney_email" value="{{ old('attorney_email') }}" autocomplete="email" required maxlength="150" placeholder="name@lawfirm.com">
            @error('attorney_email')<small class="form-error">{{ $message }}</small>@enderror
          </label>
          <label class="attorney-create-form__field" for="attorney-account-password">
            <span>Password</span>
            <input id="attorney-account-password" type="password" name="password" autocomplete="new-password" minlength="12" required>
            <small>Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</small>
            @error('password')<small class="form-error">{{ $message }}</small>@enderror
          </label>
          <label class="attorney-create-form__field" for="attorney-account-confirm-password">
            <span>Confirm password</span>
            <input id="attorney-account-confirm-password" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required>
          </label>
          <button class="attorney-create-form__submit" type="submit">Create attorney account</button>
        </form>
      </div>
    @else
      <div class="admin-empty">Every attorney profile already has a login account.</div>
    @endif

    <div class="attorney-account-roster">
      <div class="attorney-account-roster__head"><div><div class="eyebrow">CURRENT ACCOUNTS</div><h3>Attorney logins</h3></div><span>{{ $attorneys->filter(fn ($attorney) => $attorney->account)->count() }} linked</span></div>
      @forelse($attorneys->filter(fn ($attorney) => $attorney->account) as $attorney)
        <div class="attorney-account-roster__row">
          <span class="attorney-account-roster__initials" aria-hidden="true">{{ $attorney->initials }}</span>
          <span class="attorney-account-roster__identity"><strong>{{ $attorney->name }}</strong><small>{{ $attorney->practiceArea?->name ?? 'Practice area not set' }}</small></span>
          <span class="attorney-account-roster__email">{{ $attorney->account->email }}</span>
          <span class="attorney-account-roster__badge">Account linked</span>
        </div>
      @empty
        <p class="attorney-account-roster__empty">No attorney logins have been created yet.</p>
      @endforelse
    </div>
  </section>

  <section class="admin-section admin-tab-panel" id="admin-accounts" role="tabpanel" aria-labelledby="tab-admin-accounts" hidden>
    <link rel="stylesheet" href="{{ asset('css/admin-accounts.css') }}?v={{ filemtime(public_path('css/admin-accounts.css')) }}">
    <div class="admin-section-head"><div><div class="eyebrow">ACCESS</div><h2 id="admin-accounts-title">Administrator accounts</h2></div><span>{{ $admins->whereNull('deactivated_at')->count() }} active</span></div>
    <p class="aa-note">Manage who can sign in to this console. Only signed-in administrators can add or deactivate administrators, and there is no public admin sign-up.</p>

    <div class="aa-invite">
      <div>
        <h3>Invite an administrator</h3>
        <p>They receive an email with a secure, time-limited link to choose their own password, so no password is ever shared.</p>
      </div>
      <form class="aa-invite__form" method="POST" action="{{ route('admin.admins.store') }}">
        @csrf
        <div class="aa-field"><label for="aa-name">Full name</label><input id="aa-name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Maria Santos" autocomplete="off" required maxlength="150"></div>
        <div class="aa-field"><label for="aa-email">Work email</label><input id="aa-email" type="email" name="email" value="{{ old('email') }}" placeholder="name@yourfirm.com" autocomplete="off" required maxlength="150"></div>
        <button type="submit" class="aa-btn aa-btn--primary"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>Send invitation</button>
      </form>
    </div>

    <h3 class="aa-list-title">Team access <span>{{ $admins->count() }}</span></h3>
    <ul class="aa-list">
      @foreach($admins as $adminUser)
        @php
          $isSelf = $adminUser->is(auth()->user());
          $initials = \Illuminate\Support\Str::of($adminUser->name)->explode(' ')->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('');
          $state = $adminUser->isDeactivated() ? 'off' : (! $adminUser->email_verified_at ? 'pending' : 'active');
        @endphp
        <li class="aa-row{{ $state === 'off' ? ' is-off' : '' }}">
          <span class="aa-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper($initials) }}</span>
          <div class="aa-who"><strong>{{ $adminUser->name }}@if($isSelf)<span class="aa-you">You</span>@endif</strong><span>{{ $adminUser->email }}</span></div>
          <span class="aa-meta">Added {{ $adminUser->created_at?->format('M j, Y') }}</span>
          <span class="aa-badge aa-badge--{{ $state }}">{{ $state === 'off' ? 'Deactivated' : ($state === 'pending' ? 'Invitation sent' : 'Active') }}</span>
          <div class="aa-actions">
            @if($state === 'off')
              <form method="POST" action="{{ route('admin.admins.reactivate', $adminUser) }}">@csrf<button type="submit" class="aa-btn">Reactivate</button></form>
            @elseif($state === 'pending')
              <form method="POST" action="{{ route('admin.admins.invite.resend', $adminUser) }}">@csrf<button type="submit" class="aa-btn">Resend link</button></form>
            @endif
            @if($state !== 'off' && ! $isSelf)
              <form method="POST" action="{{ route('admin.admins.deactivate', $adminUser) }}" onsubmit="return confirm('Deactivate this administrator? They will lose access immediately.');">@csrf<button type="submit" class="aa-btn aa-btn--danger">Deactivate</button></form>
            @endif
          </div>
        </li>
      @endforeach
    </ul>
  </section>
  </div>
@endsection
