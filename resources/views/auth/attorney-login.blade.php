@extends('layouts.app')

@section('title', 'Attorney Login')

@section('content')
  <div class="eyebrow">SAFALAW ATTORNEY ACCESS</div>
  <h1 class="page-title">Attorney sign in</h1>
  <p class="lead">Use the individual account issued by the firm. Your workspace only shows matters assigned to your attorney profile.</p>

  <form class="client-auth-form" method="POST" action="{{ route('attorney.login.store') }}">
    @csrf
    <div class="form-field">
      <label for="attorney-email">Work email</label>
      <input id="attorney-email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" maxlength="150" required autofocus>
      @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
      <label for="attorney-password">Password</label>
      <input id="attorney-password" type="password" name="password" autocomplete="current-password" required>
      @error('password')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <label class="admin-remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
    <button class="btn-primary" type="submit">Sign in to Attorney Workspace</button>
  </form>
  <p class="client-auth-links"><a href="{{ route('password.request') }}">Forgot password?</a><span aria-hidden="true">·</span><a href="{{ route('login') }}">Back to access options</a></p>
@endsection
