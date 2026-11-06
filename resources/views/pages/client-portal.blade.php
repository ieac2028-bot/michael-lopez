@extends('layouts.app')

@section('title', 'Client Portal')

@section('content')
  <div class="eyebrow">FOR EXISTING CLIENTS</div>
  <h1 class="page-title">Client portal</h1>
  <p class="lead">Check where your matter stands. Case lookups are matched to your verified client account email.</p>

  <div class="client-portal-toolbar">
    <span>Signed in as <strong>{{ auth()->user()->email }}</strong></span>
    <form method="POST" action="{{ route('client.logout') }}">
      @csrf
      <button class="btn-ghost" type="submit">Sign out</button>
    </form>
  </div>

  <section class="admin-section" aria-labelledby="client-requests-title">
    <div class="admin-section-head"><div><div class="eyebrow">REQUEST TRACKER</div><h2 id="client-requests-title">Your consultation requests</h2></div><a class="btn-primary" href="{{ route('consultation') }}">Start a request</a></div>
    @forelse($inquiries as $inquiry)
      @php
        $flow = [
          'Client login', 'Admin live chat', 'Admin collects client information',
          'Legal category identified', 'Search matching attorneys', 'Recommendations displayed',
          'System sends best match', 'Attorney accepts or declines', 'Consultation confirmed',
        ];
        $doneThrough = match($inquiry->status) {
          'clarifying' => 2,
          'reviewing' => 5,
          'referred' => 7,
          'declined' => 6,
          'accepted' => 9,
          default => 1,
        };
        $active = $inquiry->status === 'accepted' ? null : match($inquiry->status) {
          'new' => 2,
          'clarifying' => 3,
          'reviewing' => 6,
          'referred' => 8,
          'declined' => 8,
          default => 2,
        };
      @endphp
      <article class="admin-card client-request-card">
        <div class="admin-card-head"><div><h3>{{ $inquiry->practiceArea?->name }} · {{ $inquiry->created_at->format('M j, Y') }}</h3><p>{{ $inquiry->attorney?->name ? 'Automatically matched: '.$inquiry->attorney->name : 'Finding an available match' }}</p></div><span class="admin-status-pill">{{ ucfirst($inquiry->status) }}</span></div>
        @include('partials.consultation-process', ['steps' => $flow, 'doneThrough' => $doneThrough, 'currentStep' => $active, 'ariaLabel' => 'Consultation progress'])
        @if($inquiry->caseRecord)<p>Case reference <strong>{{ $inquiry->caseRecord->reference_no }}</strong> · {{ ucfirst(str_replace('_', ' ', $inquiry->caseRecord->status)) }}</p>@endif
        @if($inquiry->status === 'declined')<p>The matched attorney could not accept, and no other connected match is available. The firm will follow up.</p>@endif
        <a href="{{ route('consultation.open', $inquiry) }}">Open request and firm conversation →</a>
      </article>
    @empty
      <div class="admin-empty">You haven’t sent a consultation request yet. Start one to find a matching attorney.</div>
    @endforelse
  </section>

  <div class="contact-grid">
    <form method="POST" action="{{ route('client-portal.lookup') }}">
      @csrf
      <div class="form-field">
        <label for="case-reference">Case reference number</label>
        <input id="case-reference" type="text" name="reference_no" placeholder="e.g. SAFA-2026-ABC123" value="{{ old('reference_no') }}" maxlength="50" required>
      </div>
      @error('reference_no')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      <button type="submit" class="btn-primary" style="width:100%;">View case status</button>
      <p style="font-size:0.76rem;color:var(--ink-soft);margin-top:10px;">For your privacy, results are limited to cases associated with your verified account email.</p>
    </form>

    <div>
      @if($notFound)
        <div class="glance-card">
          <div class="k">No match found</div>
          <div class="v">Double-check your email and case reference number, or contact the office if you believe this is an error.</div>
        </div>
      @endif
      @if($caseRecord)
        <div class="glance-card" style="margin-bottom:12px;">
          <div class="k">Case reference</div><div class="v">{{ $caseRecord->reference_no }} — {{ $caseRecord->title }}</div>
        </div>
        <div class="glance-card" style="margin-bottom:12px;">
          <div class="k">Assigned attorney</div><div class="v">{{ $caseRecord->attorney->name ?? 'Unassigned' }}</div>
        </div>
        <div class="glance-card" style="margin-bottom:12px;">
          <div class="k">Practice area · case status</div><div class="v">{{ $caseRecord->practiceArea?->name ?? 'Under review' }} · {{ ucfirst(str_replace('_', ' ', $caseRecord->status ?? 'active')) }}</div>
        </div>
        @if($caseRecord->submitted_at && $caseRecord->accepted_at)
          <div class="glance-card" style="margin-bottom:12px;">
            <div class="k">Referral accepted</div><div class="v">{{ $caseRecord->accepted_at->format('M j, Y') }} · Response time {{ $caseRecord->submitted_at->diffForHumans($caseRecord->accepted_at, true) }}</div>
          </div>
        @endif
        <div class="glance-card">
          <div class="k">Status timeline</div>
          <div class="v" style="margin-top:8px;">
            @foreach($caseRecord->timeline as $step)
              <div style="margin-bottom:8px; {{ $step['state'] === 'pending' ? 'color:var(--ink-soft);' : '' }}">
                {{ $step['state'] === 'done' ? '✅' : ($step['state'] === 'current' ? '🔶' : '⬜') }}
                {{ $step['label'] }}
                @if($step['date']) — {{ \Carbon\Carbon::parse($step['date'])->format('M j, Y') }} @endif
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
