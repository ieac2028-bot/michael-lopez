@extends('layouts.app')

@section('title', 'Save recovery codes')

@section('content')
  <div class="eyebrow">ACCOUNT SECURITY</div>
  <h1 class="page-title">Save your recovery codes</h1>
  <p class="lead">Keep these codes somewhere private. Each code works once to reset your password if you cannot sign in. They are shown only on this page now.</p>

  <section class="client-auth-form" aria-label="One-time account recovery codes">
    @if(session('recovery_codes'))
      <ul class="recovery-code-list" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;list-style:none;padding:0;margin:0 0 20px;">
        @foreach(session('recovery_codes') as $code)
          <li><code style="display:block;padding:12px;border:1px solid var(--line);border-radius:8px;text-align:center;letter-spacing:.08em;user-select:all;">{{ $code }}</code></li>
        @endforeach
      </ul>
      <p class="form-error" style="color:var(--ink-soft);">Each code can be used once. Don’t share them except with someone you trust to help recover this account.</p>
    @else
      <p>These codes were already displayed or are unavailable. Contact the firm administrator to issue a replacement set after confirming your identity.</p>
    @endif
    <a class="btn-primary" style="display:block;text-align:center;text-decoration:none;" href="{{ route('client-portal') }}">Continue to client portal</a>
  </section>
@endsection
