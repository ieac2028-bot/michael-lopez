@extends('layouts.app')

@section('title', 'Legal Insights')

@section('content')
<section class="container" aria-labelledby="insights-title">
  <div class="eyebrow">SAFALAW KNOWLEDGE CENTER</div>
  <h1 class="page-title" id="insights-title">Legal Insights</h1>
  <p class="lead" style="max-width:760px;">
    Browse general legal education, practical resources, and answers to common questions.
  </p>

  <nav aria-label="Legal Insights categories" class="insights-category-nav" style="display:flex;flex-wrap:wrap;gap:10px;margin:24px 0 36px;">
    <a class="btn-ghost" href="#legal-articles">Legal Articles</a>
    <a class="btn-ghost" href="#legal-updates">Legal Updates</a>
    <a class="btn-ghost" href="#educational-resources">Educational Resources</a>
    <a class="btn-ghost" href="#insights-faqs">FAQs</a>
  </nav>

  <section id="legal-articles" aria-labelledby="articles-title" style="max-width:1040px;margin-bottom:32px;scroll-margin-top:24px;">
    <div class="eyebrow">READ AND LEARN</div>
    <h2 id="articles-title" style="margin:0 0 14px;">Legal Articles</h2>
    <article class="glance-card">
      <h3 style="margin:0 0 8px;font-size:1.1rem;">Preparing for an initial conversation with a lawyer</h3>
      <p class="v">Write down a short timeline, the people involved, and the main questions you want answered. Gather relevant documents for your own reference and share them only through a channel the firm has asked you to use.</p>
    </article>
  </section>

  <section id="legal-updates" aria-labelledby="updates-title" style="max-width:1040px;margin-bottom:32px;scroll-margin-top:24px;">
    <div class="eyebrow">RECENT INFORMATION</div>
    <h2 id="updates-title" style="margin:0 0 14px;">Legal Updates</h2>
    <div class="glance-card" role="status">
      <p class="v" style="margin:0;">No legal updates have been published yet. Updates will include a publication date and links to their source materials.</p>
    </div>
  </section>

  <section id="educational-resources" aria-labelledby="resources-title" style="max-width:1040px;margin-bottom:32px;scroll-margin-top:24px;">
    <div class="eyebrow">PRACTICAL STARTING POINTS</div>
    <h2 id="resources-title" style="margin:0 0 14px;">Educational Resources</h2>
    <div class="glance">
      <article class="glance-card">
        <h3 style="margin:0 0 8px;font-size:1.1rem;">Not sure which practice area fits?</h3>
        <p class="v">Describe your concern in your own words when you contact the firm. The practice-area list is a navigation aid, not a legal assessment or recommendation.</p>
        <a href="{{ route('practice-areas') }}" style="display:inline-block;margin-top:12px;">Browse practice areas</a>
      </article>
      <article class="glance-card">
        <h3 style="margin:0 0 8px;font-size:1.1rem;">Understanding the inquiry process</h3>
        <p class="v">A submitted inquiry can be reviewed by the firm. Submission alone does not confirm representation, create an attorney-client relationship, or guarantee a response time or outcome.</p>
        <a href="{{ route('contact') }}" style="display:inline-block;margin-top:12px;">Contact the firm</a>
      </article>
    </div>
  </section>

  <section id="insights-faqs" aria-labelledby="faqs-title" style="max-width:1040px;margin-bottom:32px;scroll-margin-top:24px;">
    <div class="eyebrow">QUICK ANSWERS</div>
    <h2 id="faqs-title" style="margin:0 0 14px;">FAQs</h2>
    <details class="glance-card" style="margin-bottom:10px;">
      <summary style="cursor:pointer;font-weight:600;">Does reading an article or sending an inquiry make me a client?</summary>
      <p class="v" style="margin-top:10px;">No. Website content and an inquiry submission do not by themselves create an attorney-client relationship or confirm that the firm has accepted a matter.</p>
    </details>
    <details class="glance-card" style="margin-bottom:10px;">
      <summary style="cursor:pointer;font-weight:600;">What if I do not know which practice area to choose?</summary>
      <p class="v" style="margin-top:10px;">You can explain your concern in your own words through the contact form. You do not need to determine a legal classification before reaching out.</p>
    </details>
    <details class="glance-card">
      <summary style="cursor:pointer;font-weight:600;">Can I rely on this information for my specific situation?</summary>
      <p class="v" style="margin-top:10px;">No. The content is general education, not legal advice. Speak with a qualified lawyer about your circumstances.</p>
    </details>
  </section>

  <aside class="glance-card" style="max-width:1040px;border-left:3px solid var(--red);" aria-label="Legal information notice">
    <strong>General information only.</strong> These sample guides are educational and are not legal advice. For advice about a specific situation, contact a qualified lawyer. Do not submit confidential or time-sensitive information through this prototype.
  </aside>
</section>
@endsection
