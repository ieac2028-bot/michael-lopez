@extends('layouts.app')

@section('title', 'Client Portal')

@section('content')
  <section class="client-history-hero">
    <div>
      <div class="eyebrow">CLIENT PORTAL</div>
      <h1 class="page-title">Your consultation history</h1>
      <p class="lead">Review your requests, continue a conversation, or start a new attorney match. Confirmed consultations and their case records stay here.</p>
    </div>
    <a class="btn-primary" href="{{ route('consultation.new') }}">Start a new matching request</a>
  </section>

  <div class="client-portal-toolbar">
    <span>Signed in as <strong>{{ auth()->user()->email }}</strong></span>
    <form method="POST" action="{{ route('client.logout') }}">
      @csrf
      <button class="btn-ghost" type="submit">Sign out</button>
    </form>
  </div>

  <section class="client-history-list" aria-labelledby="client-requests-title">
    <div class="admin-section-head"><div><div class="eyebrow">REQUEST HISTORY</div><h2 id="client-requests-title">Your consultation requests</h2></div><span>{{ $inquiries->count() }} total</span></div>
    @forelse($inquiries as $inquiry)
      @php
        $paymentStatus = $inquiry->payment_status;
        $flow = ['Signed in', 'Chat with our team', 'Finding attorneys', 'Your matches', 'Request sent', 'Attorney response', 'Fee & payment', 'Payment verification', 'Consultation confirmed'];
        $doneThrough = match(true) {
          $paymentStatus === 'verified' || $inquiry->status === 'accepted' => 9,
          in_array($paymentStatus, ['pending', 'verifying'], true) => 7,
          in_array($paymentStatus, ['fee_proposed', 'awaiting_client', 'canceled', 'expired'], true) => 6,
          $inquiry->status === 'clarifying' => 1,
          $inquiry->status === 'reviewing' => 2,
          in_array($inquiry->status, ['referred', 'declined'], true) => 5,
          default => 1,
        };
        $active = match(true) {
          $paymentStatus === 'verified' || $inquiry->status === 'accepted' => null,
          in_array($paymentStatus, ['pending', 'verifying'], true) => 8,
          in_array($paymentStatus, ['fee_proposed', 'awaiting_client', 'canceled', 'expired'], true) => 7,
          $inquiry->status === 'referred' || $inquiry->status === 'declined' => 6,
          $inquiry->status === 'reviewing' => 3,
          default => 2,
        };
        $requestStatus = $paymentStatus ? ucfirst(str_replace('_', ' ', $paymentStatus)) : ucfirst($inquiry->status);
      @endphp
      <article class="client-history-card {{ $paymentStatus === 'verified' ? 'is-confirmed' : '' }}">
        <div class="client-history-card__header">
          <div><span class="eyebrow">REQUEST · {{ $inquiry->created_at->format('M j, Y') }}</span><h3>{{ $inquiry->practiceArea?->name ?? 'Legal consultation' }}</h3><p>{{ $inquiry->attorney?->name ? 'Matched attorney: '.$inquiry->attorney->name : 'Attorney matching in progress' }}</p></div>
          <span class="admin-status-pill">{{ $requestStatus }}</span>
        </div>
        @include('partials.consultation-process', ['steps' => $flow, 'doneThrough' => $doneThrough, 'currentStep' => $active, 'ariaLabel' => 'Consultation progress'])
        <div class="client-history-card__footer">
          <span>
            @if($inquiry->caseRecord)
              Case <strong>{{ $inquiry->caseRecord->reference_no }}</strong> · {{ ucfirst(str_replace('_', ' ', $inquiry->caseRecord->status)) }}
            @elseif($paymentStatus === 'verified')
              Payment verified · Consultation confirmed
            @else
              Consultation request · {{ $inquiry->updated_at->diffForHumans() }}
            @endif
          </span>
          <a href="{{ route('consultation.open', $inquiry) }}">Open request and conversation <span aria-hidden="true">→</span></a>
        </div>
      </article>
    @empty
      <div class="admin-empty">You haven’t sent a consultation request yet. Start one to find an attorney matched to your needs.</div>
    @endforelse
  </section>
@endsection
