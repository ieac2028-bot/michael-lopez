@extends('layouts.app')

@section('title', 'Register for the Client Portal')

@section('content')
  <div class="eyebrow">CLIENT PORTAL</div>
  <h1 class="page-title">Register as a new user</h1>
  <p class="lead">Create a client account. You’ll need to verify your email before viewing case information.</p>

  <form class="client-auth-form" method="POST" action="{{ route('client.register.store') }}">
    @csrf
    <div class="form-field">
      <label for="client-name">Full name</label>
      <input id="client-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="150" required autofocus>
      @error('name')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
      <label for="client-email">Email</label>
      <input id="client-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required>
      @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
      <label for="client-password">Password</label>
      <input id="client-password" type="password" name="password" autocomplete="new-password" required>
      <small class="auth-help">Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</small>
      @error('password')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
      <label for="client-password-confirmation">Confirm password</label>
      <input id="client-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
    </div>
    <button class="btn-primary" type="submit">Create client account</button>
  </form>
  <p class="client-auth-links"><a href="{{ route('client.login') }}">Already registered? Sign in</a><span aria-hidden="true">·</span><a href="{{ route('login') }}">Back to access options</a></p>
@endsection
