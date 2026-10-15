@extends('layouts.app')

@section('title', 'Verify your email')

@section('content')
  <div class="eyebrow">{{ auth()->user()->isAttorney() ? 'ATTORNEY ACCESS' : 'CLIENT PORTAL' }}</div>
  <h1 class="page-title">Verify your email address</h1>
  <p class="lead">Open the verification link sent to <strong>{{ auth()->user()->email }}</strong> to securely access your {{ auth()->user()->isAttorney() ? 'attorney workspace' : 'Client Portal' }}.</p>

  @if(session('status'))
    <div class="glance-card auth-status" role="status">{{ session('status') }}</div>
  @endif

  <div class="client-auth-actions">
    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button class="btn-primary" type="submit">Resend verification email</button>
    </form>
    <form method="POST" action="{{ route(auth()->user()->isAttorney() ? 'attorney.logout' : 'client.logout') }}">
      @csrf
      <button class="btn-ghost" type="submit">Sign out</button>
    </form>
  </div>
@endsection
