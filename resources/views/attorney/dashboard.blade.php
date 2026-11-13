@extends('layouts.app')

@section('title', 'Attorney workspace')

@section('content')
  @if(session('attorney_status'))<div class="glance-card admin-flash" role="status">{{ session('attorney_status') }}</div>@endif
  @if($errors->any())<div class="glance-card admin-errors" role="alert">{{ $errors->first() }}</div>@endif

  <div id="attorney-panel-matters" class="attorney-tab-panel" role="tabpanel" aria-labelledby="attorney-tab-matters" data-attorney-workspace-panel="matters">
  <div class="admin-page-head">
    <div>
      <div class="eyebrow">ATTORNEY WORKSPACE · {{ auth()->user()->attorneyProfile->name }}</div>
      <h1 class="page-title">Assigned matters</h1>
      <p class="lead">This workspace is limited to cases assigned to your attorney profile.</p>
    </div>
  </div>

  <section class="glance-card" aria-labelledby="matching-availability-title" style="margin:0 0 24px;">
    <div class="k" id="matching-availability-title">New client matching</div>
    <p class="v">Choose whether the system can send you new consultation requests. Turning this off will not change requests or cases already assigned to you.</p>
    <form method="POST" action="{{ route('attorney.matching-availability.update') }}" class="admin-workflow-actions">
      @csrf
      <input type="hidden" name="accepting_matches" value="{{ $attorneyProfile->accepting_matches ? '0' : '1' }}">
      <button type="submit" class="{{ $attorneyProfile->accepting_matches ? 'btn-ghost' : 'btn-primary' }}">
        {{ $attorneyProfile->accepting_matches ? 'Pause new matches' : 'Include me in matching' }}
      </button>
      <span class="admin-status-pill">{{ $attorneyProfile->accepting_matches ? 'Included in matching' : 'Paused' }}</span>
    </form>
  </section>

  <section class="admin-section" aria-labelledby="attorney-requests-title">
    <div class="admin-section-head"><div><div class="eyebrow">CLIENT MATCHES</div><h2 id="attorney-requests-title">Consultation requests awaiting your response</h2></div><span>{{ $inquiries->count() }} pending</span></div>
    @forelse($inquiries as $inquiry)
      <article class="admin-card">
        <div class="admin-card-head"><div><h3>{{ $inquiry->practiceArea?->name }} · {{ $inquiry->full_name }}</h3><p>{{ $inquiry->client_location }} · {{ $inquiry->preferred_date?->format('M j, Y') }} · {{ ucfirst(str_replace('_', ' ', $inquiry->consultation_method ?? 'consultation')) }}</p></div><span class="admin-status-pill">{{ ucfirst($inquiry->status) }}</span></div>
        <section class="attorney-conversation-summary" aria-label="Summary of client and firm conversation">
          <h4>Conversation summary</h4>
          <p>{{ $inquiry->conversation_summary }}</p>
        </section>
        <div class="admin-workflow-actions">
          <form class="attorney-fee-proposal" method="POST" action="{{ route('attorney.inquiries.respond', $inquiry) }}">@csrf<input type="hidden" name="decision" value="accepted"><label>Proposed consultation fee (PHP)<input type="number" name="consultation_fee" min="0.01" max="9999999999" step="0.01" inputmode="decimal" required placeholder="e.g. 2500.00"></label><button type="submit" class="btn-primary">Accept and propose fee</button></form>
          <form method="POST" action="{{ route('attorney.inquiries.respond', $inquiry) }}">@csrf<input type="hidden" name="decision" value="declined"><button type="submit" class="btn-ghost">Decline request</button></form>
        </div>
      </article>
    @empty
      <div class="admin-empty">No consultation requests are waiting for your response.</div>
    @endforelse
  </section>

  <section class="admin-section" aria-labelledby="attorney-cases-title">
    <div class="admin-section-head"><div><div class="eyebrow">CASE PROGRESS</div><h2 id="attorney-cases-title">Your assigned matters</h2></div><span>{{ $cases->count() }} assigned</span></div>
    @forelse($cases as $case)
      <article class="admin-card">
        <div class="admin-card-head">
          <div><h3>{{ $case->reference_no }} · {{ $case->practiceArea?->name ?? 'Practice area not set' }}</h3><p>Received {{ $case->submitted_at?->format('M j, Y') ?? 'date not recorded' }} · Accepted {{ $case->accepted_at?->format('M j, Y') ?? 'date not recorded' }}</p></div>
          <span class="admin-status-pill">{{ ucfirst(str_replace('_', ' ', $case->status ?? 'active')) }}</span>
        </div>
        <div class="admin-data-grid">
          <div class="admin-data-wide"><strong>Client-facing progress</strong><span>@if($case->timeline)@foreach($case->timeline as $step){{ $step['label'] }}@if(!$loop->last) → @endif @endforeach @else No progress updates yet. @endif</span></div>
        </div>
        <form class="admin-case-form" method="POST" action="{{ route('attorney.cases.update', $case) }}">
          @csrf @method('PATCH')
          <label>Case status<select name="status">@foreach(['active', 'on_hold', 'resolved', 'closed'] as $status)<option value="{{ $status }}" @selected(($case->status ?? 'active') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></label>
          <button type="submit" class="btn-primary">Update progress</button>
        </form>
      </article>
    @empty
      <div class="admin-empty">No matters are currently assigned to your account.</div>
    @endforelse
  </section>
  </div>

  @php($spokenLanguages = old('spoken_languages', $attorneyProfile->spoken_languages ?: ['English', 'Filipino/Tagalog']))
  <div id="attorney-panel-profile" class="attorney-tab-panel" role="tabpanel" aria-labelledby="attorney-tab-profile" data-attorney-workspace-panel="profile" hidden>
  <section class="admin-section attorney-profile-section" aria-labelledby="attorney-profile-title">
    <div class="admin-section-head"><div><div class="eyebrow">YOUR PROFESSIONAL DETAILS</div><h2 id="attorney-profile-title">Attorney profile</h2><p>Keep your matching details and client contact information up to date.</p></div></div>
    <form class="attorney-profile-form" method="POST" action="{{ route('attorney.profile.update') }}">
      @csrf @method('PATCH')
      <div class="attorney-profile-grid">
        <label class="attorney-profile-field">Primary area of practice
          <select name="primary_practice_area_id" required>
            <option value="">Select a practice area</option>
            @foreach($practiceAreas as $area)
              <option value="{{ $area->id }}" @selected((string) old('primary_practice_area_id', $attorneyProfile->practice_area_id) === (string) $area->id)>{{ $area->name }}</option>
            @endforeach
          </select>
        </label>
        <label class="attorney-profile-field">Minimum consultation budget (PHP)
          <input type="number" name="budget_min" min="0" max="100000000" step="1" value="{{ old('budget_min', $attorneyProfile->budget_min) }}" placeholder="e.g. 1500">
        </label>
        <label class="attorney-profile-field">Maximum consultation budget (PHP)
          <input type="number" name="budget_max" min="0" max="100000000" step="1" value="{{ old('budget_max', $attorneyProfile->budget_max) }}" placeholder="e.g. 5000">
        </label>
        <label class="attorney-profile-field">Client-facing email
          <input type="email" name="contact_email" maxlength="150" value="{{ old('contact_email', $attorneyProfile->contact_email) }}" placeholder="name@lawfirm.com">
          <small>This contact email is shown only after a consultation is confirmed. Your sign-in email stays private and cannot be changed here.</small>
        </label>
        <label class="attorney-profile-field">Client-facing phone
          <input type="tel" name="contact_phone" maxlength="40" value="{{ old('contact_phone', $attorneyProfile->contact_phone) }}" placeholder="+63 9xx xxx xxxx">
          <small>Shown to the assigned client after confirmation.</small>
        </label>
        <label class="attorney-profile-field attorney-profile-field--wide">Profile summary / specialization
          <textarea name="specialization_note" maxlength="1000" rows="3" placeholder="Describe your experience and the legal matters you handle.">{{ old('specialization_note', $attorneyProfile->specialization_note) }}</textarea>
        </label>
        <label class="attorney-profile-field">Years of experience
          <input type="number" name="years_experience" min="0" max="70" step="1" value="{{ old('years_experience', $attorneyProfile->years_experience) }}" placeholder="e.g. 8">
        </label>
        <label class="attorney-profile-field">Service locations
          <input type="text" name="coverage_areas" maxlength="500" value="{{ old('coverage_areas', implode(', ', $attorneyProfile->coverage_areas ?? [])) }}" placeholder="Manila, Makati, Remote / nationwide">
          <small>Separate locations with commas. Leave blank if you serve nationwide.</small>
        </label>
        <fieldset class="attorney-profile-field attorney-profile-field--wide">
          <legend>Languages spoken</legend>
          <div class="attorney-profile-checks attorney-profile-checks--languages">
            @foreach($languageOptions as $language)
              <label><input type="checkbox" name="spoken_languages[]" value="{{ $language }}" @checked(in_array($language, $spokenLanguages, true))><span>{{ $language }}</span></label>
            @endforeach
          </div>
          <label class="attorney-profile-other-language">Other languages
            <input type="text" name="other_languages" maxlength="300" value="{{ old('other_languages', collect($spokenLanguages)->reject(fn ($language) => in_array($language, $languageOptions, true))->implode(', ')) }}" placeholder="Separate other languages with commas">
          </label>
        </fieldset>
      </div>
      <div class="attorney-profile-footer"><p>Only relevant professional details and the contact method needed for the consultation are shared with the assigned client after confirmation.</p><button class="btn-primary" type="submit">Save profile</button></div>
    </form>
  </section>
  </div>
  <script src="{{ asset('js/attorney-workspace.js') }}?v={{ filemtime(public_path('js/attorney-workspace.js')) }}" defer></script>
@endsection
