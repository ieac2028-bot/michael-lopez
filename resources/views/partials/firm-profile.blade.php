<section class="firm-profile" aria-labelledby="firm-profile-title">
  <div class="firm-profile__image-frame" data-pointer-zoom>
    <img class="firm-profile__image" src="{{ asset('images/safalaw/firm-team.png') }}" alt="The Saludo Taleon Ocampo and Associates Law Offices team">
  </div>
  @if($showPracticeAreas ?? false)
    <div class="section-label firm-profile__practice-heading">
      <span>Our Areas of Practice</span>
      <a href="{{ route('practice-areas') }}">{{ \App\Models\PracticeArea::count() }} practice areas — see all →</a>
    </div>
    <div class="practice-preview firm-profile__practice-preview">
      @foreach($practiceAreas as $area)
        <div class="mini-card">
          <div class="t">{{ $area->name }}</div>
          <div class="d">{{ \Illuminate\Support\Str::limit($area->description, 90) }}</div>
          <a class="mini-card__more" href="{{ route('practice-areas') }}#practice-area-{{ $area->id }}">Learn more <span aria-hidden="true">→</span></a>
        </div>
      @endforeach
    </div>
  @endif
  @if($showDescription ?? true)
    <div class="firm-profile__copy">
      <div class="eyebrow">SALUDO TALEON OCAMPO &amp; ASSOCIATES LAW OFFICES</div>
      @if(($headingLevel ?? 'h1') === 'h2')
        <h2 class="page-title" id="firm-profile-title">The Firm</h2>
      @else
        <h1 class="page-title" id="firm-profile-title">The Firm</h1>
      @endif
      <p><strong>SALUDO TALEON OCAMPO &amp; ASSOCIATES LAW OFFICES</strong> has always operated on the principle that an organization sustained by the trust and patronage of others carries a moral obligation to give its very best at all times. We recognize that the value of any legal service is ultimately defined by those who seek it—and that each client is entitled to the full measure of their counsel’s dedication, knowledge, experience, and perspective.</p>
      <p>The relationship between lawyer and client is built on absolute trust and confidentiality. It requires not only professional competence but also full concern, undivided attention, and consistent availability. This standard of service is not episodic; it is the product of daily effort and long-term commitment.</p>
      <p>Professionalism, in our view, demands continuous learning. We believe that no legal service organization can remain relevant without the ongoing refinement of its skills and the readiness to engage with change. In this spirit, we place importance on our ability to adapt—not only to developments in law but also to advancements in technology that affect the practice and delivery of legal services.</p>
      <p>Our more than 60-year history is rooted in this philosophy, supported by the integrity of our work and the strength of the lawyers who carry it forward. We continue to measure our performance by our commitment to professional excellence, the quality of our service, and the trust placed in us by our clients.</p>
    </div>
  @endif

  @if($showDescription ?? true)
    @include('partials.recognition-logos')
  @endif
</section>
