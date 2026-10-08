@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
  <div class="eyebrow">GET IN TOUCH</div>
  <h1 class="page-title">Contact Us</h1>
  <p class="lead">Contact Saludo Taleon Ocampo and Associates Law Offices by phone, email, or visit the Makati office.</p>

  @if(session('status'))
    <div class="contact-status" role="status">{{ session('status') }}</div>
  @endif

  <section class="contact-us-grid" aria-label="SAFALAW contact information and location">
    <div class="contact-details-card">
      <div class="eyebrow">SAFALAW</div>
      <h2>Contact Information</h2>
      <p class="contact-firm-name">Saludo Taleon Ocampo and Associates Law Offices</p>
      <address class="contact-address">
        <div class="contact-detail">
          <span class="contact-detail__icon" aria-hidden="true">⌖</span>
          <div><strong>Office</strong><a href="https://maps.google.com/maps?q=Safa%20Bldg%2C%205858%20Alfonso%20Cor%20Fermina%2C%20Makati%2C%201210%20Metro%20Manila%2C%20Philippines&amp;t=m&amp;z=15" target="_blank" rel="noopener noreferrer">SAFA Building, 5858 Alfonso cor. Fermina Streets, Poblacion, Makati City 1210, Metro Manila, Philippines</a></div>
        </div>
        <div class="contact-detail">
          <span class="contact-detail__icon" aria-hidden="true">☎</span>
          <div><strong>Telephone</strong><a href="tel:+63288970527">+63 2 8897 0527 to 30</a></div>
        </div>
        <div class="contact-detail">
          <span class="contact-detail__icon" aria-hidden="true">▣</span>
          <div><strong>Mobile</strong><a href="tel:+639171640184">+63 917 164 0184</a></div>
        </div>
        <div class="contact-detail">
          <span class="contact-detail__icon" aria-hidden="true">✉</span>
          <div><strong>Email</strong><a href="mailto:safa@safalaw.com.ph">safa@safalaw.com.ph</a></div>
        </div>
        <div class="contact-detail">
          <span class="contact-detail__icon" aria-hidden="true">◷</span>
          <div><strong>Office hours</strong><span>Monday–Friday, 9 AM–5 PM</span></div>
        </div>
      </address>
      <a class="contact-directions" href="https://www.google.com/maps/dir/?api=1&amp;destination=SAFA%20Building%2C%205858%20Alfonso%20cor.%20Fermina%20Streets%2C%20Poblacion%2C%20Makati%20City%201210%2C%20Metro%20Manila%2C%20Philippines" target="_blank" rel="noopener noreferrer">Get directions <span aria-hidden="true">↗</span></a>
    </div>

    <div class="contact-map-card">
      <iframe
        title="Map showing the SAFALAW office at SAFA Building in Makati City"
        src="https://maps.google.com/maps?q=Safa%20Bldg%2C%205858%20Alfonso%20Cor%20Fermina%2C%20Makati%2C%201210%20Metro%20Manila%2C%20Philippines&amp;t=m&amp;z=15&amp;output=embed&amp;iwloc=near"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allowfullscreen>
      </iframe>
    </div>
  </section>

  <section class="contact-message-section" aria-labelledby="contact-message-title">
    <div>
      <div class="eyebrow">SEND US A MESSAGE</div>
      <h2 id="contact-message-title">Send Us a Message</h2>
      <p>This form saves your message in the project. It is not yet connected to SAFALAW’s email. For a direct response, call or email the office using the details above.</p>
    </div>
    <form method="POST" action="{{ route('contact.store') }}">
      @csrf
      <div class="contact-name-title">Name <span aria-hidden="true">*</span></div>
      <div class="contact-name-fields">
        <div class="form-field">
          <label for="contact-first-name">First</label>
          <input id="contact-first-name" type="text" name="first_name" placeholder="First" autocomplete="given-name" value="{{ old('first_name') }}" required>
          @error('first_name')<p class="contact-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-field">
          <label for="contact-last-name">Last</label>
          <input id="contact-last-name" type="text" name="last_name" placeholder="Last" autocomplete="family-name" value="{{ old('last_name') }}" required>
          @error('last_name')<p class="contact-error">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="form-field">
        <label for="contact-email">Email <span aria-hidden="true">*</span></label>
        <input id="contact-email" type="email" name="email" placeholder="you@example.com" autocomplete="email" value="{{ old('email') }}" required>
        @error('email')<p class="contact-error">{{ $message }}</p>@enderror
      </div>
      <div class="form-field">
        <label for="contact-message">Message</label>
        <textarea id="contact-message" name="message" placeholder="How can we help?" required>{{ old('message') }}</textarea>
        @error('message')<p class="contact-error">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="btn-primary contact-submit">Submit</button>
    </form>
  </section>
@endsection
