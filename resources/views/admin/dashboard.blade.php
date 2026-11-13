@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
  <div class="admin-page-head admin-hero">
    <div>
      <div class="eyebrow">GPMO STAFF CONSOLE</div>
      <h1 class="page-title">Welcome back, {{ auth()->user()->name }}</h1>
      <p class="lead">{{ now()->format('l, F j, Y') }} · A quick view of new inquiries, active matters, and attorney availability.</p>
    </div>
    <nav class="admin-quicklinks" aria-label="Jump to section">
      <a href="#admin-inquiries">Inquiries @if($pendingInquiryCount)<b>{{ $pendingInquiryCount }}</b>@endif</a>
      <a href="#admin-cases">Cases</a>
      <a href="#admin-attorneys">Attorneys</a>
      <a href="#admin-accounts">Admins</a>
    </nav>
  </div>

  @if(session('admin_status'))<div class="glance-card admin-flash" role="status">{{ session('admin_status') }}</div>@endif
  @if($errors->any())<div class="glance-card admin-errors" role="alert">{{ $errors->first() }}</div>@endif

  <section class="admin-metrics" aria-label="Dashboard overview">
    <a class="admin-metric admin-metric--review" href="#admin-inquiries"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16v12H8l-4 4z"/><path d="M8 9h8M8 12h5"/></svg></i><span>Needs review</span><strong>{{ $pendingInquiryCount }}</strong><small>inquiries</small></a>
    <a class="admin-metric admin-metric--progress" href="#admin-cases"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h18v13H3z"/><path d="M8 7V4h8v3M3 13h18"/></svg></i><span>In progress</span><strong>{{ $openCaseCount }}</strong><small>matters</small></a>
    <a class="admin-metric admin-metric--available" href="#admin-attorneys"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></i><span>Available</span><strong>{{ $availableAttorneyCount }}</strong><small>attorneys</small></a>
    <a class="admin-metric admin-metric--total" href="#admin-cases"><i class="admin-metric__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h7"/></svg></i><span>Total</span><strong>{{ $cases->count() }}</strong><small>cases</small></a>
  </section>

  <section class="admin-section" id="admin-inquiries" aria-labelledby="admin-inquiries-title" data-inquiry-inbox data-poll-url="{{ route('admin.inquiries.poll') }}" data-latest-id="{{ $inquiries->max('id') ?? 0 }}">
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
            <div class="professional-chat-thread" data-message-thread data-status="{{ $inquiry->status }}" data-latest-message-id="{{ $inquiry->messages->last()?->id ?? '' }}" data-translate-url="{{ route('chat.translate') }}" data-poll-url="{{ route('admin.inquiries.messages.poll', $inquiry) }}">
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
          @if($inquiry->caseRecord)<p>Case opened: <strong>{{ $inquiry->caseRecord->reference_no }}</strong></p>@endif
        </div>
        </details>
      </article>
    @empty
      <div class="admin-empty">No intake records yet.</div>
    @endforelse
  </section>

  <section class="admin-section" id="admin-cases" aria-labelledby="admin-cases-title">
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

  <section class="admin-section" id="admin-attorneys" aria-labelledby="admin-attorneys-title">
    <details class="admin-attorney-section">
    <summary class="admin-section-head"><div><div class="eyebrow">TEAM</div><h2 id="admin-attorneys-title">Attorney availability and accounts</h2></div><span>{{ $attorneys->count() }} profiles · {{ $availableAttorneyCount }} available</span></summary>
    <p class="admin-section-note">Update service coverage or issue an individual account for a real attorney. Example profiles are sample data.</p>
    <div class="admin-attorney-list">
      @foreach($attorneys as $index => $attorney)
        <div class="admin-card">
        <form class="admin-attorney-row" method="POST" action="{{ route('admin.attorneys.update', $attorney) }}">
          @csrf @method('PATCH')
          <strong class="admin-rank">#{{ $index + 1 }}</strong>
          <div><strong>{{ $attorney->name }}</strong><small>{{ $attorney->practiceArea?->name ?? 'Unassigned area' }} · {{ $attorney->years_experience }} years' experience</small></div>
          <input type="hidden" name="is_available" value="0">
          <label class="admin-available"><input type="checkbox" name="is_available" value="1" @checked($attorney->is_available)> Available</label>
          <label class="admin-coverage">Service areas<input type="text" name="coverage_areas" value="{{ implode(', ', $attorney->coverage_areas ?? []) }}" placeholder="All Philippines or city/region names"></label>
          <label>Indicative budget range (PHP)<span style="display:flex;gap:6px"><input type="number" min="0" name="budget_min" value="{{ $attorney->budget_min }}" placeholder="Minimum"><input type="number" min="0" name="budget_max" value="{{ $attorney->budget_max }}" placeholder="Maximum"></span></label>
          <button type="submit" class="btn-ghost">Save</button>
        </form>
        <div class="admin-attorney-account">
          @if($attorney->account)
            <p>Attorney login: <strong>{{ $attorney->account->email }}</strong> · Login account created</p>
          @else
            <form class="admin-inline-form" method="POST" action="{{ route('admin.attorneys.account.store', $attorney) }}">
              @csrf
              <label>Attorney account email <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="150"></label>
              <label>Initial password<input type="password" name="password" autocomplete="new-password" minlength="12" required></label>
              <label>Confirm password<input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required></label>
              <button type="submit" class="btn-primary">Create login</button>
            </form>
          @endif
        </div>
        </div>
      @endforeach
    </div>
    </details>
  </section>

  <section class="admin-section" id="admin-accounts" aria-labelledby="admin-accounts-title">
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
@endsection
