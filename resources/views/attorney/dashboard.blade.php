@extends('layouts.app')

@section('title', 'Attorney workspace')

@section('content')
  <div class="admin-page-head">
    <div>
      <div class="eyebrow">ATTORNEY WORKSPACE · {{ auth()->user()->attorneyProfile->name }}</div>
      <h1 class="page-title">Assigned matters</h1>
      <p class="lead">This workspace is limited to cases assigned to your attorney profile.</p>
    </div>
    <form method="POST" action="{{ route('attorney.logout') }}">@csrf<button class="btn-ghost" type="submit">Sign out</button></form>
  </div>

  @if(session('attorney_status'))<div class="glance-card admin-flash" role="status">{{ session('attorney_status') }}</div>@endif
  @if($errors->any())<div class="glance-card admin-errors" role="alert">{{ $errors->first() }}</div>@endif

  @php($attorneyProfile = auth()->user()->attorneyProfile)
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
          <form method="POST" action="{{ route('attorney.inquiries.respond', $inquiry) }}">@csrf<input type="hidden" name="decision" value="accepted"><button type="submit" class="btn-primary">Accept and confirm</button></form>
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
@endsection
