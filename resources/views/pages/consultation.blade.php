@extends('layouts.app')

@section('title', 'Request Demo')

@section('content')
  <div class="eyebrow">CLIENT INTAKE</div>
  <h1 class="page-title">Register a legal inquiry</h1>
  <p class="lead">Share a non-confidential overview of your legal concern, location, and requirements. This prototype saves the intake locally and suggests attorneys by practice area and stated coverage.</p>

  @if(session('status'))
    <div class="glance-card" style="border-left:3px solid var(--green);margin-bottom:24px;max-width:560px;">
      {{ session('status') }}
    </div>
  @endif

  @if(isset($recommendedAttorneys) && $recommendedAttorneys->isNotEmpty())
    <section class="glance-card" style="margin:0 0 24px;max-width:760px;">
      <div class="k">Suggested attorneys</div>
      <div class="v" style="margin-top:8px;">Suggestions are based on the selected practice area, stated coverage, sample rating, and experience. Availability and rankings are demonstration data and do not constitute a referral.</div>
      <div class="attorney-grid" style="margin-top:16px;">
        @foreach($recommendedAttorneys as $attorney)
          <article class="glance-card">
            <div class="k">{{ $attorney->rank }} · {{ $attorney->practiceArea?->name }}</div>
            <div class="v">{{ $attorney->name }}</div>
            <small>{{ $attorney->years_experience }} years' experience · {{ number_format((float) $attorney->sample_rating, 1) }}/5 sample rating</small>
          </article>
        @endforeach
      </div>
    </section>
  @elseif(session()->has('recommended_attorney_ids'))
    <div class="glance-card" style="margin:0 0 24px;max-width:760px;">No available sample attorney matched that practice area and coverage. The intake is saved for administrative review.</div>
  @endif

  <div class="cross-link">
    Exploring a general inquiry instead? <a href="{{ route('contact') }}">View the sample contact form →</a>
  </div>

  <div class="contact-grid">
    <div class="contact-info">
      <div><div class="k">Prototype notice</div><div class="v">Submitting this form demonstrates the planned request flow. It does not schedule a real legal consultation.</div></div>
      <div><div class="k">Before a real consultation</div><div class="v">Confirm attorney credentials, availability, fees, and secure ways to share documents directly with the attorney.</div></div>
    </div>

    <form method="POST" action="{{ route('consultation.store') }}">
      @csrf
      <div class="form-field">
        <label>Full name</label>
        <input type="text" name="full_name" placeholder="Juan Dela Cruz" value="{{ old('full_name') }}" required>
        @error('full_name')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@email.com" value="{{ old('email') }}" required>
        @error('email')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Phone number</label>
        <input type="text" name="phone" placeholder="+63 9XX XXX XXXX" value="{{ old('phone') }}" required>
        @error('phone')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Client type</label>
        <select name="client_type" required>
          <option value="">Select a client type</option>
          <option value="individual" @selected(old('client_type') === 'individual')>Individual</option>
          <option value="business" @selected(old('client_type') === 'business')>Business</option>
          <option value="organization" @selected(old('client_type') === 'organization')>Organization</option>
        </select>
        @error('client_type')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>City or area where you need service</label>
        <input type="text" name="client_location" placeholder="e.g. Makati City" value="{{ old('client_location') }}" required>
        @error('client_location')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Practice area</label>
        <select name="practice_area_id" required>
          <option value="">Select a practice area</option>
          @foreach($practiceAreas as $area)
            <option value="{{ $area->id }}" @selected(old('practice_area_id') == $area->id)>{{ $area->name }}</option>
          @endforeach
        </select>
        @error('practice_area_id')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Preferred date</label>
        <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" required>
        @error('preferred_date')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Preferred time</label>
        <select name="preferred_time" required>
          <option value="">Select a time</option>
          @foreach($timeSlots as $slot)
            <option value="{{ $slot }}" @selected(old('preferred_time') == $slot)>{{ $slot }}</option>
          @endforeach
        </select>
        @error('preferred_time')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Legal profile</label>
        <textarea name="legal_profile" placeholder="Briefly describe who is involved and any relevant business or personal context. Do not include confidential details." required>{{ old('legal_profile') }}</textarea>
        @error('legal_profile')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Clarify the case or legal issue</label>
        <textarea name="message" placeholder="What happened, and what do you need help understanding? Do not include confidential details." required>{{ old('message') }}</textarea>
        @error('message')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label>Client requirements</label>
        <textarea name="client_requirements" placeholder="List the help, documents, language, or accommodations you may need. Do not upload or include private records." required>{{ old('client_requirements') }}</textarea>
        @error('client_requirements')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="btn-primary" style="width:100%;">Request consultation</button>
    </form>
  </div>
@endsection
