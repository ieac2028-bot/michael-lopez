@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
  <div class="eyebrow">ACCOUNT SECURITY</div>
  <h1 class="page-title">Reset your password</h1>
  <p class="lead">Request a recovery code by email, then use it or one of your saved backup codes to set a new password.</p>
  @if(session('status'))<div class="glance-card auth-status" role="status">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="glance-card admin-errors" role="alert">{{ session('error') }}</div>@endif
  @if(!filled(config('services.gmail_api.client_id')) || !filled(config('services.gmail_api.client_secret')) || !filled(config('services.gmail_api.refresh_token')) || !filled(config('services.gmail_api.sender_address')))
    <div class="glance-card admin-errors" role="status">Email recovery is not configured yet. A saved recovery code still works; Gmail API OAuth settings are required to send email codes.</div>
  @endif
  <form class="client-auth-form" method="POST" action="{{ route('password.email') }}" style="margin-bottom:18px;">
    @csrf
    <div class="form-field"><label for="reset-email">Email</label><input id="reset-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required autofocus>@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
    <button class="btn-primary" type="submit">Email me a recovery code</button>
  </form>
  <form class="client-auth-form" method="POST" action="{{ route('password.update') }}">
    @csrf
    <div class="form-field"><label for="reset-email-code">Account email</label><input id="reset-email-code" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required>@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
    <div class="form-field"><label for="recovery-code">Recovery code</label><input id="recovery-code" type="text" name="recovery_code" value="{{ old('recovery_code') }}" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="40" required>@error('recovery_code')<p class="form-error">{{ $message }}</p>@enderror</div>
    <div class="form-field"><label for="new-password">New password</label><input id="new-password" type="password" name="password" autocomplete="new-password" minlength="12" required>@error('password')<p class="form-error">{{ $message }}</p>@enderror</div>
    <div class="form-field"><label for="new-password-confirmation">Confirm new password</label><input id="new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required></div>
    <button class="btn-primary" type="submit">Reset password</button>
  </form>
  <p class="client-auth-links"><a href="{{ route('login') }}">Back to access options</a></p>
@endsection
