<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0B1E3F">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Abogadokonek') — Attorney matching platform</title>
<script>
  try {
    const savedTheme = localStorage.getItem('abogadokonek-theme');
    if (savedTheme === 'light' || savedTheme === 'dark') {
      document.documentElement.dataset.theme = savedTheme;
    }
  } catch (_) {}
  try {
    const prefs = JSON.parse(localStorage.getItem('abogadokonek-a11y') || '{}');
    const root = document.documentElement;
    if (['default', 'large', 'largest'].includes(prefs.text)) root.dataset.a11yText = prefs.text;
    if (prefs.contrast) root.dataset.a11yContrast = 'true';
    if (prefs.motion) root.dataset.a11yMotion = 'true';
    if (prefs.links) root.dataset.a11yLinks = 'true';
    if (prefs.spacing) root.dataset.a11ySpacing = 'true';
    if (prefs.font) root.dataset.a11yFont = 'true';
  } catch (_) {}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<a class="skip-link" href="#mainContent">Skip to main content</a>
<div class="stripe-top"></div>
<div class="site-backdrop" aria-hidden="true"></div>
<svg class="ambient-lines" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
  <path d="M-100 180 C180 40 320 420 600 260 S980 60 1540 170"/>
  <path d="M-80 610 C240 760 360 360 700 520 S1120 840 1520 590"/>
  <path d="M220 -100 C420 140 120 400 360 620 S720 820 820 1020"/>
  <path d="M980 -100 C760 180 1160 320 920 540 S880 780 1100 1020"/>
</svg>

<header>
  <div class="header-inner">
    <a href="{{ route('home') }}" class="brand" style="text-decoration:none;">
      <img class="brand__safalaw-logo" data-theme-logo data-logo-dark="{{ asset('images/safalaw/safalaw-logo-transparent.png') }}" data-logo-light="{{ asset('images/safalaw/safalaw-logo-light.png') }}" src="{{ asset('images/safalaw/safalaw-logo-transparent.png') }}" alt="SafaLaw — Saludo Taleon Ocampo and Associates Law Offices">
    </a>
    <nav class="topnav">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
      <a href="{{ route('firm') }}" class="{{ request()->routeIs('firm') ? 'active' : '' }}">The Firm</a>
      <a href="{{ route('attorneys') }}" class="{{ request()->routeIs('attorneys') ? 'active' : '' }}">Attorneys</a>
      <a href="{{ route('practice-areas') }}" class="{{ request()->routeIs('practice-areas') ? 'active' : '' }}">Practice Areas</a>
      <a href="{{ route('legal-insights') }}" class="{{ request()->routeIs('legal-insights') ? 'active' : '' }}" aria-current="{{ request()->routeIs('legal-insights') ? 'page' : 'false' }}">Legal Insights</a>
      <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact*') ? 'active' : '' }}">Contact Us</a>
      <a href="{{ route('login') }}" class="consult-btn header-login {{ request()->routeIs('login', 'client.login', 'client.register', 'client-portal*', 'admin.login', 'admin.dashboard', 'attorney.login', 'attorney.*', 'verification.*', 'password.*') ? 'active' : '' }}" aria-label="Choose login access" aria-current="{{ request()->routeIs('login', 'client.login', 'client.register', 'client-portal*', 'admin.login', 'admin.dashboard', 'attorney.login', 'attorney.*', 'verification.*', 'password.*') ? 'page' : 'false' }}">Login</a>
    </nav>
    <button class="theme-toggle theme-toggle--header" type="button" data-theme-toggle aria-label="Toggle light and dark theme" aria-pressed="false">
      <span class="theme-toggle__icon" data-active-theme="light" aria-hidden="true">
        <svg class="theme-toggle__sun" viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
        <svg class="theme-toggle__moon" viewBox="0 0 24 24" focusable="false"><path d="M20.3 15.4A8.7 8.7 0 0 1 8.6 3.7 8.8 8.8 0 1 0 20.3 15.4Z"/></svg>
      </span><span class="theme-toggle__label">Dark mode</span>
    </button>
    <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">☰</button>
  </div>
</header>

<div class="mobile-nav-backdrop" id="mnBackdrop"></div>
<nav class="mobile-nav" id="mobileNav">
  <button class="mn-close" id="mnClose" aria-label="Close menu">✕</button>
  <a class="mn-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
  <a class="mn-item {{ request()->routeIs('firm') ? 'active' : '' }}" href="{{ route('firm') }}">The Firm</a>
  <a class="mn-item {{ request()->routeIs('attorneys') ? 'active' : '' }}" href="{{ route('attorneys') }}">Attorneys</a>
  <a class="mn-item {{ request()->routeIs('practice-areas') ? 'active' : '' }}" href="{{ route('practice-areas') }}">Practice Areas</a>
  <a class="mn-item {{ request()->routeIs('legal-insights') ? 'active' : '' }}" href="{{ route('legal-insights') }}" aria-current="{{ request()->routeIs('legal-insights') ? 'page' : 'false' }}">Legal Insights</a>
  <a class="mn-item {{ request()->routeIs('contact*') ? 'active' : '' }}" href="{{ route('contact') }}">Contact Us</a>
  <button class="theme-toggle theme-toggle--mobile" type="button" data-theme-toggle aria-label="Toggle light and dark theme" aria-pressed="false">
    <span class="theme-toggle__icon" data-active-theme="light" aria-hidden="true">
      <svg class="theme-toggle__sun" viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
      <svg class="theme-toggle__moon" viewBox="0 0 24 24" focusable="false"><path d="M20.3 15.4A8.7 8.7 0 0 1 8.6 3.7 8.8 8.8 0 1 0 20.3 15.4Z"/></svg>
    </span><span class="theme-toggle__label">Dark mode</span>
  </button>
  <a class="mn-book {{ request()->routeIs('login', 'client.login', 'client.register', 'client-portal*', 'admin.login', 'admin.dashboard', 'attorney.login', 'attorney.*', 'verification.*', 'password.*') ? 'active' : '' }}" href="{{ route('login') }}" aria-label="Choose login access" style="text-decoration:none;display:block;text-align:center;">Login</a>
</nav>

<main class="page" id="mainContent" tabindex="-1">
  @yield('content')
</main>

<footer class="site-footer">
  <div class="footer-inner">
    <a class="footer-safalaw-logo" href="{{ route('home') }}" aria-label="SafaLaw home">
      <img src="{{ asset('images/safalaw/safalaw-logo-transparent.png') }}" alt="SafaLaw — Saludo Taleon Ocampo and Associates Law Offices">
    </a>
    <div class="footer-brand">
      <div class="tagline">A Legacy of Excellence in Philippine Law</div>
      <address class="footer-contact">
        SAFA Building, 5858 Alfonso cor. Fermina Streets<br>
        Poblacion, Makati City 1210<br>
        Metro Manila, Philippines<br>
        <a href="tel:+63288970527">+63 2 8897 0527 to 30</a><br>
        <a href="tel:+639171640184">+63 917 164 0184</a><br>
        <a href="mailto:safa@safalaw.com.ph">safa@safalaw.com.ph</a>
      </address>
      <div class="footer-app-note">Abogadokonek client discovery app</div>
      <div class="footer-social">
        <a href="#" aria-label="Facebook" onclick="return false;">f</a>
      </div>
    </div>

    <div class="footer-col">
      <h4>About</h4>
      <a href="{{ route('firm') }}">The Firm</a>
      <a href="{{ route('attorneys') }}">Attorneys</a>
      <a href="{{ route('practice-areas') }}">Practice Areas</a>
      <a href="{{ route('legal-insights') }}">Legal Insights</a>
      <a href="{{ route('client-portal') }}">Client Portal</a>
      <a href="{{ route('contact') }}">Contact</a>
      <div class="footer-hours">
        <h4>Office Hours</h4>
        <p class="footer-office-hours">9 AM – 5 PM</p>
      </div>
    </div>

    <div class="footer-col">
      <h4>Practice Areas</h4>
      @php
        $sortedFooterAreas = ($footerAreas ?? \App\Models\PracticeArea::all())
          ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
          ->values();
        $footerAreaColumns = $sortedFooterAreas->chunk(max(1, (int) ceil($sortedFooterAreas->count() / 2)));
      @endphp
      <div class="footer-practice-list">
        @foreach($footerAreaColumns as $areaColumn)
          <div class="footer-practice-column">
            @foreach($areaColumn as $area)
              <a href="{{ route('practice-areas') }}">{{ $area->name }}</a>
            @endforeach
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-inner">© {{ date('Y') }} Abogadokonek</div>
  </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top" onclick="window.scrollTo({top:0, behavior:'smooth'})">▲</button>

<div class="accessibility" data-accessibility>
  <button class="accessibility__trigger" type="button" aria-label="Open accessibility settings" aria-haspopup="dialog" aria-expanded="false" aria-controls="accessibilityPanel" data-a11y-trigger>
    <span class="accessibility__trigger-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="5.5" r="3"/><path d="M4 11h24M16 9v11m0-8-8 5m8-5 8 5m-8 3-6 9m6-9 6 9"/></svg></span>
    <span class="accessibility__trigger-copy"><strong>Accessibility</strong><small>Text, contrast &amp; more</small></span>
  </button>
  <section class="accessibility__panel" id="accessibilityPanel" role="dialog" aria-label="Accessibility options" aria-modal="false" hidden data-a11y-panel>
    <div class="accessibility__head"><div><strong>Accessibility</strong><span>Choose what works for you</span></div><button type="button" aria-label="Close accessibility options" data-a11y-close>×</button></div>
    <div class="accessibility__row"><span>Text size</span><div class="accessibility__sizes"><button type="button" data-a11y-size="default" aria-label="Default text size">A</button><button type="button" data-a11y-size="large" aria-label="Larger text">A<sup>+</sup></button><button type="button" data-a11y-size="largest" aria-label="Largest text">A<sup>++</sup></button></div></div>
    <button class="accessibility__option" type="button" data-a11y-toggle="contrast" aria-pressed="false"><span><strong>High contrast</strong><small>Make text and borders clearer</small></span><i aria-hidden="true"></i></button>
    <button class="accessibility__option" type="button" data-a11y-toggle="motion" aria-pressed="false"><span><strong>Reduce motion</strong><small>Limit moving effects</small></span><i aria-hidden="true"></i></button>
    <button class="accessibility__option" type="button" data-a11y-toggle="links" aria-pressed="false"><span><strong>Underline links</strong><small>Make links easier to spot</small></span><i aria-hidden="true"></i></button>
    <button class="accessibility__option" type="button" data-a11y-toggle="spacing" aria-pressed="false"><span><strong>More text spacing</strong><small>Increase letter, word, and line spacing</small></span><i aria-hidden="true"></i></button>
    <button class="accessibility__option" type="button" data-a11y-toggle="font" aria-pressed="false"><span><strong>Plain reading font</strong><small>Use familiar system fonts</small></span><i aria-hidden="true"></i></button>
    <button class="accessibility__reset" type="button" data-a11y-reset>Reset options</button>
  </section>
</div>

  <button class="launcher" id="launcher" aria-label="Chat with SafaLaw" aria-controls="chatPanel" aria-expanded="false">
  <span class="launcher__icon" aria-hidden="true">
    <svg viewBox="0 0 48 48" focusable="false">
      <path d="M9 10.5A5.5 5.5 0 0 1 14.5 5h19a5.5 5.5 0 0 1 5.5 5.5v15a5.5 5.5 0 0 1-5.5 5.5H22l-9 7v-7.6a5.5 5.5 0 0 1-4-5.3v-14.6Z"/>
      <circle cx="18" cy="18" r="1.7"/><circle cx="24" cy="18" r="1.7"/><circle cx="30" cy="18" r="1.7"/>
      <path class="launcher__sparkle" d="m39 28 1.6 4.2L45 34l-4.4 1.7L39 40l-1.7-4.3L33 34l4.3-1.8L39 28Z"/>
    </svg>
  </span>
  <span class="launcher__copy"><span class="launcher__title">Chat with SafaLaw</span><span class="launcher__subtitle">Get help finding a starting point</span></span>
</button>
<div class="chat-panel" id="chatPanel" data-endpoint="{{ route('chat.respond') }}">
  <div class="chat-head">
    <div class="dot"></div>
    <div class="titles"><div class="name">SafaLaw Assistant</div><div class="sub">Site and intake guide</div></div>
    <button class="close" id="closeChat" aria-label="Close">✕</button>
  </div>
  <div class="chat-scope">I provide general information about the firm and this website. I can’t give legal advice or assess an individual case.</div>
  <div class="chat-body" id="chatBody"></div>
  <div class="quick-replies" id="quickReplies"></div>
  <div class="chat-input"><input type="text" id="chatInput" placeholder="Ask something…"><button id="sendBtn">Send</button></div>
</div>

<svg class="pointer-orbit" data-pointer-orbit aria-hidden="true" focusable="false">
  <g class="pointer-orbit__cursor">
    <circle class="pointer-orbit__ring" cx="0" cy="0" r="18" />
    <circle class="pointer-orbit__dot" cx="0" cy="0" r="2" />
  </g>
</svg>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
