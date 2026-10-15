@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
  <div class="eyebrow">ACCOUNT SECURITY</div>
  <h1 class="page-title">Reset your password</h1>
  <p class="lead">Enter your account email and we’ll send a time-limited password setup link.</p>
  @if(session('status'))<div class="glance-card auth-status" role="status">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="glance-card admin-errors" role="alert">{{ session('error') }}</div>@endif
  <form class="client-auth-form" method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="form-field"><label for="reset-email">Email</label><input id="reset-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required autofocus>@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
    <button class="btn-primary" type="submit">Email password link</button>
  </form>
  <p class="client-auth-links"><a href="{{ route('login') }}">Back to access options</a></p>
@endsection
