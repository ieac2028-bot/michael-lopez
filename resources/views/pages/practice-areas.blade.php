@extends('layouts.app')

@section('title', 'Practice Areas')

@section('content')
  <div class="eyebrow">EXPLORE LEGAL TOPICS</div>
  <h1 class="page-title">Practice areas</h1>
  <p class="lead">These sample categories demonstrate how visitors could explore different legal topics. The listings are not verified legal services.</p>

  <div class="areas-grid">
    @foreach($practiceAreas as $i => $area)
      <details class="area-card" id="practice-area-{{ $area->id }}" {{ $i === 0 ? 'open' : '' }}>
        <summary><span class="t">{{ $area->name }}</span><span class="chev">▸</span></summary>
        <div class="d">
          {{ $area->description }}
          @if($area->attorneys->isNotEmpty())
            <div class="handled-by" style="margin-top:10px;">
              Handled by: {{ $area->attorneys->pluck('name')->join(', ') }}
            </div>
          @endif
        </div>
      </details>
    @endforeach
  </div>

  <section class="practice-contact-cta" aria-labelledby="practice-contact-title">
    <h2 id="practice-contact-title">Get Started with Your Case</h2>
    <p>Call us: <a href="tel:+63288970527">(+632) 8897 0527 to 30</a> or <a href="tel:+639171640184">(+63) 917 164-0184</a></p>
  </section>
@endsection
