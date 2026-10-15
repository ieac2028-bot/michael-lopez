@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
  <div class="admin-page-head">
    <div>
      <div class="eyebrow">SAFALAW STAFF</div>
      <h1 class="page-title">Dashboard</h1>
      <p class="lead">A quick view of new inquiries, active matters, and attorney availability.</p>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn-ghost" type="submit">Sign out</button></form>
  </div>

  <p class="admin-prototype-note">Prototype only. Use sample information; do not enter confidential client details.</p>
  @if(session('admin_status'))<div class="glance-card admin-flash" role="status">{{ session('admin_status') }}</div>@endif
  @if($errors->any())<div class="glance-card admin-errors" role="alert">{{ $errors->first() }}</div>@endif

  <section class="admin-metrics" aria-label="Dashboard overview">
    <a class="admin-metric" href="#admin-inquiries"><span>Needs review</span><strong>{{ $pendingInquiryCount }}</strong><small>inquiries</small></a>
    <a class="admin-metric" href="#admin-cases"><span>In progress</span><strong>{{ $openCaseCount }}</strong><small>matters</small></a>
    <a class="admin-metric" href="#admin-attorneys"><span>Available</span><strong>{{ $availableAttorneyCount }}</strong><small>attorneys</small></a>
    <a class="admin-metric" href="#admin-cases"><span>Total</span><strong>{{ $cases->count() }}</strong><small>cases</small></a>
  </section>

  <section class="admin-section" id="admin-inquiries" aria-labelledby="admin-inquiries-title">
    <div class="admin-section-head"><div><div class="eyebrow">INBOX</div><h2 id="admin-inquiries-title">Recent inquiries</h2></div><span>{{ $inquiries->count() }} total · {{ $pendingInquiryCount }} to review</span></div>
    @forelse($inquiries as $inquiry)
      <article class="admin-card">
        <div class="admin-card-head">
          <div><h3>{{ $inquiry->full_name }}</h3><p>{{ $inquiry->practiceArea?->name ?? 'General inquiry' }} · {{ $inquiry->created_at->format('M j, Y') }} · {{ $inquiry->email }}</p></div>
          <span class="admin-status-pill">{{ ucfirst($inquiry->status) }}</span>
        </div>
        <details class="admin-record-details">
          <summary>Review details and manage referral</summary>
        <div class="admin-data-grid">
          <div><strong>Registration</strong><span>{{ ucfirst($inquiry->client_type ?? 'General inquiry') }} · {{ $inquiry->client_location ?? 'Location not provided' }}</span></div>
          <div><strong>Area verification</strong><span>{{ $inquiry->practiceArea?->name ?? 'Unselected' }}</span></div>
          <div><strong>Availability</strong><span>{{ $inquiry->preferred_date?->format('M j, Y') ?? 'Not requested' }} {{ $inquiry->preferred_time ?? '' }}</span></div>
          <div><strong>Submitted</strong><span>{{ $inquiry->created_at->format('M j, Y g:i A') }}</span></div>
          <div><strong>Legal profile</strong><span>{{ $inquiry->legal_profile ?: 'Not provided' }}</span></div>
          <div><strong>Case clarification</strong><span>{{ $inquiry->message }}</span></div>
          <div class="admin-data-wide"><strong>Client requirements</strong><span>{{ $inquiry->client_requirements ?: 'Not provided' }}</span></div>
        </div>
        <div class="admin-workflow-actions">
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
          @if($inquiry->type === \App\Models\Inquiry::TYPE_CONSULTATION && $inquiry->status !== 'accepted')
            <form method="POST" action="{{ route('admin.inquiries.accept', $inquiry) }}">
              @csrf<button type="submit" class="btn-primary" @disabled(!$inquiry->attorney || !$inquiry->attorney->is_available)>Accept referral and open case</button>
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
        <div class="admin-card-head"><div><h3>{{ $case->reference_no }}</h3><p>{{ $case->practiceArea?->name ?? 'Practice area not set' }} · {{ $case->attorney?->name ?? 'Unassigned' }} · Updated {{ $case->updated_at->format('M j, Y') }}</p></div><span class="admin-status-pill">{{ ucfirst(str_replace('_', ' ', $case->status ?? 'active')) }}</span></div>
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
    <p class="admin-section-note">Update service coverage or issue an individual account for a real attorney. Example profiles and ratings are sample data.</p>
    <div class="admin-attorney-list">
      @foreach($attorneys as $index => $attorney)
        <div class="admin-card">
        <form class="admin-attorney-row" method="POST" action="{{ route('admin.attorneys.update', $attorney) }}">
          @csrf @method('PATCH')
          <strong class="admin-rank">#{{ $index + 1 }}</strong>
          <div><strong>{{ $attorney->name }}</strong><small>{{ $attorney->practiceArea?->name ?? 'Unassigned area' }} · {{ number_format((float) $attorney->sample_rating, 1) }}/5 sample rating · {{ $attorney->years_experience }} years</small></div>
          <input type="hidden" name="is_available" value="0">
          <label class="admin-available"><input type="checkbox" name="is_available" value="1" @checked($attorney->is_available)> Available</label>
          <label class="admin-coverage">Service areas<input type="text" name="coverage_areas" value="{{ implode(', ', $attorney->coverage_areas ?? []) }}" placeholder="All Philippines or city/region names"></label>
          <button type="submit" class="btn-ghost">Save</button>
        </form>
        <div class="admin-attorney-account">
          @if($attorney->account)
            <p>Attorney login: <strong>{{ $attorney->account->email }}</strong> · {{ $attorney->account->hasVerifiedEmail() ? 'Email verified' : 'Awaiting password setup and email verification' }}</p>
          @else
            <form class="admin-inline-form" method="POST" action="{{ route('admin.attorneys.account.store', $attorney) }}">
              @csrf
              <label>Attorney account email <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="150"></label>
              <button type="submit" class="btn-primary">Create login and email setup link</button>
            </form>
          @endif
        </div>
        </div>
      @endforeach
    </div>
    </details>
  </section>
@endsection
