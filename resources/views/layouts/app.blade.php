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
      <a href="{{ route('attorneys') }}" class="{{ request()->routeIs('attorneys') ? 'active' : '' }}">Attorneys</a>
      <a href="{{ route('practice-areas') }}" class="{{ request()->routeIs('practice-areas') ? 'active' : '' }}">Practice Areas</a>
      <a href="{{ route('client-portal') }}" class="{{ request()->routeIs('client-portal*') ? 'active' : '' }}">Client Portal</a>
      <a href="{{ route('firm') }}" class="{{ request()->routeIs('firm') ? 'active' : '' }}">The Firm</a>
      <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact*') ? 'active' : '' }}">Contact Us</a>
      <a href="{{ route('consultation') }}" class="request-demo-link {{ request()->routeIs('consultation*') ? 'active' : '' }}">View Request Demo</a>
    </nav>
    <a href="{{ route('admin.login') }}" class="consult-btn" aria-label="Admin sign in">Admin Login</a>
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
  <a class="mn-item {{ request()->routeIs('attorneys') ? 'active' : '' }}" href="{{ route('attorneys') }}">Attorneys</a>
  <a class="mn-item {{ request()->routeIs('practice-areas') ? 'active' : '' }}" href="{{ route('practice-areas') }}">Practice Areas</a>
  <a class="mn-item {{ request()->routeIs('client-portal*') ? 'active' : '' }}" href="{{ route('client-portal') }}">Client Portal</a>
  <a class="mn-item {{ request()->routeIs('firm') ? 'active' : '' }}" href="{{ route('firm') }}">The Firm</a>
  <a class="mn-item {{ request()->routeIs('contact*') ? 'active' : '' }}" href="{{ route('contact') }}">Contact Us</a>
  <a class="mn-item {{ request()->routeIs('consultation*') ? 'active' : '' }}" href="{{ route('consultation') }}">View Request Demo</a>
  <button class="theme-toggle theme-toggle--mobile" type="button" data-theme-toggle aria-label="Toggle light and dark theme" aria-pressed="false">
    <span class="theme-toggle__icon" data-active-theme="light" aria-hidden="true">
      <svg class="theme-toggle__sun" viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
      <svg class="theme-toggle__moon" viewBox="0 0 24 24" focusable="false"><path d="M20.3 15.4A8.7 8.7 0 0 1 8.6 3.7 8.8 8.8 0 1 0 20.3 15.4Z"/></svg>
    </span><span class="theme-toggle__label">Dark mode</span>
  </button>
  <a class="mn-book" href="{{ route('admin.login') }}" style="text-decoration:none;display:block;text-align:center;">Admin Login</a>
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

  <button class="launcher" id="launcher" aria-label="Click to open the Abogadokonek assistant" aria-controls="chatPanel" aria-expanded="false">
  <span class="launcher__prompt" aria-hidden="true">How can we assist you?</span>
  <svg class="launcher__justice" viewBox="0 0 72 84" aria-hidden="true" focusable="false">
    <defs>
      <linearGradient id="justice-bronze" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#F4FCFF"/><stop offset=".35" stop-color="#9CB7C8"/><stop offset=".68" stop-color="#536B7C"/><stop offset="1" stop-color="#DFEFF7"/></linearGradient>
      <linearGradient id="justice-dark-bronze" x1="0" y1="0" x2="1" y2="0"><stop stop-color="#17222C"/><stop offset=".5" stop-color="#607687"/><stop offset="1" stop-color="#121A23"/></linearGradient>
    </defs>
    <g class="launcher__justice-figure">
      <path d="M34 13h4v51h-4zM27 63h18v4H27zM21 69h30v5H21zM16 76h40v4H16z" fill="url(#justice-dark-bronze)" transform="translate(1 1)"/>
      <path d="M34 13h4v51h-4zM27 63h18v4H27zM21 69h30v5H21zM16 76h40v4H16z" fill="url(#justice-bronze)" stroke="#DDF7FF" stroke-width=".8" stroke-linejoin="round"/>
      <path d="M35 15v46m-6 5h14m-26 12h40" stroke="#FFF1C7" stroke-width="1" opacity=".7"/>
      <circle cx="36" cy="10" r="5" fill="url(#justice-dark-bronze)" transform="translate(1 1)"/>
      <circle cx="36" cy="10" r="5" fill="url(#justice-bronze)" stroke="#DDF7FF" stroke-width="1"/>
      <circle cx="34.5" cy="8.5" r="1.2" fill="#FFF" opacity=".9"/>
      <g class="launcher__justice-pan launcher__justice-pan--left">
        <path d="M10 17v28m-1-27L3 43m7-25 7 25" fill="none" stroke="url(#justice-dark-bronze)" stroke-width="3" stroke-linecap="round"/>
        <path d="M10 17v28m-1-27L3 43m7-25 7 25" fill="none" stroke="url(#justice-bronze)" stroke-width="1.5" stroke-linecap="round"/>
        <path d="M2 43c1.2 7 14.8 7 16 0" fill="#263744" stroke="url(#justice-bronze)" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M5 44c3 2 8 2 11 0" fill="none" stroke="#EAF9FF" stroke-width=".8"/>
      </g>
      <g class="launcher__justice-pan launcher__justice-pan--right">
        <path d="M62 17v28m-1-27-7 25m7-25 7 25" fill="none" stroke="url(#justice-dark-bronze)" stroke-width="3" stroke-linecap="round"/>
        <path d="M62 17v28m-1-27-7 25m7-25 7 25" fill="none" stroke="url(#justice-bronze)" stroke-width="1.5" stroke-linecap="round"/>
        <path d="M54 43c1.2 7 14.8 7 16 0" fill="#263744" stroke="url(#justice-bronze)" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M57 44c3 2 8 2 11 0" fill="none" stroke="#EAF9FF" stroke-width=".8"/>
      </g>
      <path d="M7 16h58" stroke="url(#justice-dark-bronze)" stroke-width="6" stroke-linecap="round" transform="translate(0 1.5)"/>
      <path d="M7 16h58" stroke="url(#justice-bronze)" stroke-width="4" stroke-linecap="round"/>
      <path d="M13 15h46" stroke="#EAF9FF" stroke-width=".8" stroke-linecap="round" opacity=".8"/>
      <rect x="29" y="19" width="14" height="13" rx="4" fill="#17222C" stroke="url(#justice-bronze)" stroke-width="1.4"/>
      <path d="M31 22h10" stroke="#718999" stroke-width=".7"/>
      <circle class="launcher__robot-eye" cx="33" cy="25" r="1.35" fill="#5CE1F1"/>
      <circle class="launcher__robot-eye" cx="39" cy="25" r="1.35" fill="#5CE1F1"/>
      <path d="M33 29h6" stroke="#E85A60" stroke-width="1.2" stroke-linecap="round"/>
      <path d="M31 32v2m10-2v2" stroke="#9CB7C8" stroke-width="1.3" stroke-linecap="round"/>
      <path class="launcher__scale-glint" d="m63 7 1.4 3.6L68 12l-3.6 1.4L63 17l-1.4-3.6L58 12l3.6-1.4z" fill="#5CE1F1"/>
    </g>
  </svg>
  <span class="launcher__click-label" aria-hidden="true">Click me</span>
</button>
<div class="chat-panel" id="chatPanel" data-endpoint="{{ route('chat.respond') }}">
  <div class="chat-head">
    <div class="dot"></div>
    <div class="titles"><div class="name">Abogadokonek Assistant</div><div class="sub">Client intake guide</div></div>
    <button class="close" id="closeChat" aria-label="Close">✕</button>
  </div>
  <div class="chat-scope">I can guide you to a practice-area category and explain planned matching, legal-aid, checklist, and referral features. Responses are scripted; this demo gives no legal advice and connects to no real providers.</div>
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
