@extends('layouts.app')

@section('title', 'Attorneys')

@section('content')
  <div class="eyebrow">ATTORNEY DIRECTORY</div>
  <h1 class="page-title">Explore attorney profiles</h1>
  <p class="lead">Browse example profiles by practice area, specialties, and experience.</p>
  <div class="roster-note">Showing {{ $attorneyCount }} fictional sample profiles. Names, portraits, practice dates, and case experience are demo content; they do not identify actual GPMO attorneys or represent verified credentials or case records.</div>

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
              <div class="attorney-specialization-title">Specialization practice areas</div>
              <ul class="attorney-specializations">
                @foreach(preg_split('/,\\s*/', $attorney->specialization_note) as $specialization)
                  <li>{{ trim(preg_replace('/^and\\s+/i', '', $specialization)) }}</li>
                @endforeach
              </ul>
              @if($attorney->years_experience)
                <div class="attorney-activity">Started practicing around <strong>{{ now()->subYears((int) $attorney->years_experience)->format('Y') }}</strong></div>
                <div class="attorney-meta">{{ $attorney->years_experience }} years' experience <span>(sample)</span></div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
@endsection
