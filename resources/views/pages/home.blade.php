@extends('layouts.app')

@section('title', 'Home')

@section('content')
  <div class="hero">
    <div>
      <h1>Find legal support that fits <span class="hero-title__accent">your situation.</span></h1>
      <p>With more than 60 years of experience, Ocampo &amp; Associates Law Offices provides trusted legal counsel across a wide range of practice areas. Explore our services or contact our team to discuss your legal needs.</p>
      <div class="hero-actions">
        <a href="{{ route('login') }}" class="btn-primary" style="text-decoration:none;display:inline-block;">Client Login to Get Matched</a>
        <a href="{{ route('practice-areas') }}" class="btn-ghost" style="text-decoration:none;display:inline-block;">See practice areas</a>
      </div>
    </div>
    <div class="hero-card">
      <div class="hero-card__art" aria-hidden="true" data-pointer-art>
        <svg class="hero-card__lines" viewBox="0 0 600 360" preserveAspectRatio="none">
          <path class="justice-outline" d="M110 155 300 50l190 105M145 160h310M165 160v120m90-120v120m90-120v120m90-120v120M140 285h320m-350 20h390" />
          <path class="network-line network-line--one" d="M0 84h90l38 38h100l28 28h344" />
          <path class="network-line network-line--two" d="M0 246h88l42-42h80l36-36h354" />
          <path class="network-line network-line--three" d="M80 360v-42l52-52v-66l28-28V0m360 360v-46l-45-45v-50l-40-40V0" />
          <circle class="art-node" cx="90" cy="84" r="4" />
          <circle class="art-node" cx="130" cy="204" r="4" />
          <circle class="art-node" cx="472" cy="159" r="4" />
          <circle class="art-node" cx="460" cy="272" r="4" />
        </svg>
        <span class="hero-card__pointer-ring"></span>
      </div>
      <div class="hero-card__content">
        <p class="hero-card__eyebrow">OCAMPO &amp; ASSOCIATES</p>
        <h2 class="safalaw-hero-title">OCAMPO <span>&amp; Associates Law Offices</span></h2>
        <p class="safalaw-hero-tagline">A Legacy of Excellence in Philippine Law – Trusted for Over 60 Years</p>
        <a href="{{ route('contact') }}" class="safalaw-message-button">MESSAGE US</a>
      </div>
    </div>
  </div>

  @include('partials.firm-profile', ['headingLevel' => 'h2', 'showDescription' => false, 'showPracticeAreas' => true])

  @if(auth()->check())
  <section class="home-process" aria-labelledby="home-process-title">
    <div class="section-label">CLIENT MATCHING</div>
    <h2 id="home-process-title">From legal concern to consultation</h2>
    <p class="lead">Follow each step of your request, from signing in to attorney confirmation.</p>
    @include('partials.consultation-process', ['steps' => ['Client login', 'Admin live chat', 'Admin collects details', 'Identify legal category', 'Search attorney matches', 'Display recommendations', 'System sends best match', 'Attorney responds', 'Consultation confirmed'], 'doneThrough' => 0, 'currentStep' => null, 'ariaLabel' => 'Client to attorney matching process', 'flowExample' => true])
    <a href="{{ route('login') }}" class="btn-primary" style="text-decoration:none;display:inline-block;">Start with Client Login</a>
  </section>
  @endif

  <div class="section-label">How We Can Help</div>
  <div class="glance">
    <div class="glance-card"><div class="k">Discover</div><div class="v">Browse legal practice areas and attorney profiles in one place.</div></div>
    <div class="glance-card"><div class="k">Compare</div><div class="v">Review available profile details to identify a suitable contact.</div></div>
    <div class="glance-card"><div class="k">Connect</div><div class="v">Send a general inquiry or request a consultation through the site.</div></div>
  </div>

  <div class="section-label">Designed around a simple first step</div>
  <div class="badge-grid">
    <div class="badge"><div class="mark">Find</div><div class="full">Explore relevant legal services</div></div>
    <div class="badge"><div class="mark">Learn</div><div class="full">Review attorney profiles</div></div>
    <div class="badge"><div class="mark">Ask</div><div class="full">Get answers to basic platform questions</div></div>
    <div class="badge"><div class="mark">Connect</div><div class="full">Send a consultation inquiry</div></div>
  </div>

  <section class="home-about" aria-labelledby="home-about-title">
    <div class="eyebrow">ABOUT US</div>
    <h2 class="home-about__title" id="home-about-title">OCAMPO &amp; ASSOCIATES LAW OFFICES</h2>
    <p class="home-about__lead"><strong>With over 60 years of experience, the firm brings depth, continuity, and familiarity with both longstanding and emerging legal issues in the Philippines.</strong></p>
    <p>The firm provides legal services designed to meet a wide range of client needs. With coordinated legal divisions, it handles matters across multiple areas of law, offering cohesive legal support through each stage of a case or transaction. Its work focuses on practical results aligned with client objectives, guided by clear strategies and informed assessments.</p>
    <p>The firm also maintains an active litigation practice, with lawyers regularly appearing before courts and administrative bodies in a variety of cases.</p>

    <a class="home-about__link" href="{{ route('about-us') }}" target="_blank" rel="noopener">Read More about the Firm <span aria-hidden="true">↗</span></a>
  </section>

  @include('partials.recognition-logos')

@endsection
