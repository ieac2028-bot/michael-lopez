@extends('layouts.app')

@section('title', 'Client Login')

@section('content')
  <div class="eyebrow">CLIENT PORTAL</div>
  <h1 class="page-title">Existing client login</h1>
  <p class="lead">Sign in with the verified email address associated with your case.</p>

  <form class="client-auth-form" method="POST" action="{{ route('client.login.store') }}">
    @csrf
    <div class="form-field">
      <label for="client-email">Email</label>
      <input id="client-email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
      @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
      <label for="client-password">Password</label>
      <input id="client-password" type="password" name="password" autocomplete="current-password" required>
      @error('password')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <label class="admin-remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
    <button class="btn-primary" type="submit">Sign in to Client Portal</button>
  </form>
  <p class="client-auth-links"><a href="{{ route('password.request') }}">Forgot password?</a></p>
  <p class="client-auth-links"><a href="{{ route('client.register') }}">Register as a New User</a><span aria-hidden="true">·</span><a href="{{ route('login') }}">Back to access options</a></p>
@endsection
