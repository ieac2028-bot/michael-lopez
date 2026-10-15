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

  <p class="admin-prototype-note">Prototype workspace. Use sample information only; do not enter confidential client records.</p>
  @if(session('attorney_status'))<div class="glance-card admin-flash" role="status">{{ session('attorney_status') }}</div>@endif
  @if($errors->any())<div class="glance-card admin-errors" role="alert">{{ $errors->first() }}</div>@endif

  <section class="admin-section" aria-labelledby="attorney-cases-title">
    <div class="admin-section-head"><div><div class="eyebrow">CASE PROGRESS</div><h2 id="attorney-cases-title">Your assigned matters</h2></div><span>{{ $cases->count() }} assigned</span></div>
    @forelse($cases as $case)
      <article class="admin-card">
        <div class="admin-card-head">
          <div><h3>{{ $case->reference_no }} · {{ $case->practiceArea?->name ?? 'Practice area not set' }}</h3><p>Received {{ $case->submitted_at?->format('M j, Y') ?? 'date not recorded' }} · Accepted {{ $case->accepted_at?->format('M j, Y') ?? 'date not recorded' }}</p></div>
          <span class="admin-status-pill">{{ ucfirst(str_replace('_', ' ', $case->status ?? 'active')) }}</span>
        </div>
        <div class="admin-data-grid">
          <div class="admin-data-wide"><strong>Client-facing progress</strong><span>@forelse($case->timeline ?? [] as $step){{ $step['label'] }}@if(!$loop->last) → @endif @empty No progress updates yet. @endforelse</span></div>
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
