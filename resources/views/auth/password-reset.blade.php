@extends('layouts.app')

@section('title', 'Choose a new password')

@section('content')
  <div class="eyebrow">ACCOUNT SECURITY</div>
  <h1 class="page-title">Choose a new password</h1>
  <p class="lead">Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</p>
  <form class="client-auth-form" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="form-field"><label for="reset-email">Email</label><input id="reset-email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" maxlength="150" required>@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
    <div class="form-field"><label for="new-password">New password</label><input id="new-password" type="password" name="password" autocomplete="new-password" required>@error('password')<p class="form-error">{{ $message }}</p>@enderror</div>
    <div class="form-field"><label for="new-password-confirmation">Confirm new password</label><input id="new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
    <button class="btn-primary" type="submit">Save new password</button>
  </form>
@endsection
