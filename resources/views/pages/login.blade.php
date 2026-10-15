@extends('layouts.app')

@section('title', 'Choose your sign-in')

@section('content')
  <section class="access-gateway" aria-labelledby="access-title">
    <div class="eyebrow">SAFALAW ACCESS</div>
    <h1 class="page-title" id="access-title">How would you like to continue?</h1>
    <p class="lead">Choose the access that fits your role. Staff, attorney, and client accounts have separate permissions.</p>

    <div class="access-card-grid">
      <article class="access-card">
        <div class="access-card__icon" aria-hidden="true">
          <svg viewBox="0 0 32 32"><path d="M16 3 27 7v8c0 7-4.5 11.5-11 14C9.5 26.5 5 22 5 15V7l11-4Z"/><path d="m11 16 3.2 3.2L21 12.5"/></svg>
        </div>
        <h2>Law Firm Staff</h2>
        <p>Secure access for administrators and individually assigned attorneys.</p>
        <a class="access-card__action access-card__action--primary" href="{{ route('admin.login') }}">Admin Login <span aria-hidden="true">→</span></a>
        <a class="access-card__action access-card__action--secondary" href="{{ route('attorney.login') }}">Attorney Login <span aria-hidden="true">→</span></a>
      </article>

      <article class="access-card">
        <div class="access-card__icon" aria-hidden="true">
          <svg viewBox="0 0 32 32"><circle cx="16" cy="10" r="5"/><path d="M5 28c.8-6.3 4.5-9.5 11-9.5S26.2 21.7 27 28H5Z"/><path d="M23 8h5m-2.5-2.5v5"/></svg>
        </div>
        <h2>Client Portal</h2>
        <p>Access your account or register to get started.</p>
        <div class="access-card__actions">
          <a class="access-card__action access-card__action--primary" href="{{ route('client.login') }}">Existing Client Login <span aria-hidden="true">→</span></a>
          <a class="access-card__action access-card__action--secondary" href="{{ route('client.register') }}">Register as a New User</a>
        </div>
      </article>

      <article class="access-card">
        <div class="access-card__icon" aria-hidden="true">
          <svg viewBox="0 0 32 32"><rect x="4" y="6" width="24" height="17" rx="2"/><path d="M11 28h10m-5-5v5M10 12h12m-12 4h8"/></svg>
        </div>
        <h2>Request a Demo</h2>
        <p>Interested in learning more about the platform? Request a demonstration.</p>
        <a class="access-card__action access-card__action--secondary" href="{{ route('consultation') }}">Request a Demo <span aria-hidden="true">→</span></a>
      </article>
    </div>
  </section>
@endsection
