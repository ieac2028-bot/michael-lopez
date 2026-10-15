@extends('layouts.app')

@section('title', 'The Firm')

@section('content')
  @include('partials.firm-profile')

  <section class="firm-reviews" aria-labelledby="firm-reviews-title">
    <div class="eyebrow">SAMPLE CLIENT FEEDBACK</div>
    <h2 class="firm-reviews__title" id="firm-reviews-title">What clients say</h2>
    <p class="firm-reviews__note">These illustrative reviews are sample content for the prototype, not verified client testimonials.</p>
    <div class="firm-review-grid">
      <article class="firm-review">
        <div class="firm-review__stars" role="img" aria-label="5 out of 5 stars">★★★★★</div>
        <blockquote>“The team took time to understand our concerns and explained the next steps clearly.”</blockquote>
        <div class="firm-review__label">Sample review</div>
      </article>
      <article class="firm-review">
        <div class="firm-review__stars" role="img" aria-label="5 out of 5 stars">★★★★★</div>
        <blockquote>“Professional, responsive, and thoughtful throughout the consultation process.”</blockquote>
        <div class="firm-review__label">Sample review</div>
      </article>
    </div>
    <details class="firm-reviews__more">
      <summary><span class="firm-reviews__more-open">See all reviews</span><span class="firm-reviews__more-close">Show fewer</span></summary>
      <div class="firm-review-grid firm-review-grid--more">
        <article class="firm-review">
          <div class="firm-review__stars" role="img" aria-label="5 out of 5 stars">★★★★★</div>
          <blockquote>“We appreciated the clear communication and careful attention to our matter.”</blockquote>
          <div class="firm-review__label">Sample review</div>
        </article>
        <article class="firm-review">
          <div class="firm-review__stars" role="img" aria-label="5 out of 5 stars">★★★★★</div>
          <blockquote>“The firm made a complicated process easier to understand and navigate.”</blockquote>
          <div class="firm-review__label">Sample review</div>
        </article>
      </div>
    </details>
  </section>
@endsection
