@extends('layouts.app')

@section('title', 'Attorneys')

@section('content')
  <div class="eyebrow">ATTORNEY DIRECTORY</div>
  <h1 class="page-title">Explore attorney profiles</h1>
  <p class="lead">Browse example profiles by practice area, including specialties, sample ratings, and recent practice activity.</p>
  <div class="roster-note">All names, portraits, ratings, and practice dates below are fictional demo content. They do not represent real or verified attorneys, reviews, or case records.</div>

  @foreach($attorneys as $rank => $group)
    <div class="rank-group">
      <div class="rank-title">{{ $rank }}{{ $group->count() > 1 ? 's' : '' }}</div>
      <div class="attorney-grid">
        @foreach($group as $attorney)
          <div class="attorney-card">
            <img class="attorney-photo" src="{{ asset($attorney->photo_path) }}" alt="Illustrated sample portrait for {{ $attorney->name }}" loading="lazy">
            <div class="attorney-info">
              <div class="attorney-name">{{ $attorney->name }}</div>
              <div class="attorney-role">{{ $attorney->rank }} · Sample profile</div>
              @if($attorney->sample_rating !== null)
                <div class="attorney-rating" aria-label="Sample rating {{ number_format($attorney->sample_rating, 1) }} out of 5">
                  <span aria-hidden="true">★★★★★</span> {{ number_format($attorney->sample_rating, 1) }} <small>sample</small>
                </div>
              @else
                <div class="attorney-rating">Not rated</div>
              @endif
              <div class="attorney-meta">{{ $attorney->specialization_note }}</div>
              <div class="attorney-activity">Practice activity date <strong>{{ $attorney->sample_last_practiced_on?->format('M j, Y') ?? 'Not provided' }}</strong> <span>(sample)</span></div>
              @if($attorney->years_experience)
                <div class="attorney-meta">{{ $attorney->years_experience }} years practicing <span>(sample)</span></div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
@endsection
