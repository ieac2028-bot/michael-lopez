@extends('layouts.app')

@section('title', 'Staff sign in')

@section('content')
  <div class="eyebrow">SAFALAW STAFF</div>
  <h1 class="page-title">Administrative sign in</h1>
  <p class="lead">Use an administrator account to review intake, manage referrals, and update case tracking.</p>
  <form class="admin-login-form" method="POST" action="{{ route('admin.login.store') }}">
    @csrf
    <div class="form-field">
      <label for="admin-email">Email</label>
      <input id="admin-email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
      @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
      <label for="admin-password">Password</label>
      <input id="admin-password" type="password" name="password" autocomplete="current-password" required>
      @error('password')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <label class="admin-remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
    <button class="btn-primary" type="submit" style="width:100%;">Sign in</button>
  </form>
@endsection
