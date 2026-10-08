@extends('layouts.app')

@section('title', 'Request Demo')

@section('content')
  <div class="eyebrow">SAMPLE REQUEST FLOW</div>
  <h1 class="page-title">Request a consultation</h1>
  <p class="lead">Use this sample form to explore how a consultation request could work in the Abogadokonek prototype. Do not enter real or sensitive legal information; submissions are only stored by the demo app.</p>

  @if(session('status'))
    <div class="glance-card" style="border-left:3px solid var(--green);margin-bottom:24px;max-width:560px;">
      {{ session('status') }}
    </div>
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
        <label>Briefly describe your matter</label>
        <textarea name="message" placeholder="Sample summary for the prototype (do not include confidential details).">{{ old('message') }}</textarea>
        @error('message')<p style="color:var(--red);font-size:0.78rem;">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="btn-primary" style="width:100%;">Request consultation</button>
    </form>
  </div>
@endsection
