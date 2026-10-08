@extends('layouts.app')

@section('title', 'About Us')

@section('content')
  <div class="eyebrow">ABOUT US</div>
  <h1 class="page-title">SALUDO TALEON OCAMPO AND ASSOCIATES LAW OFFICES</h1>
  <p class="lead">With over 60 years of experience, the firm brings depth, continuity, and familiarity with both longstanding and emerging legal issues in the Philippines.</p>

  <div class="home-about home-about--detail">
    <p>The firm provides legal services designed to meet a wide range of client needs. With coordinated legal divisions, it handles matters across multiple areas of law, offering cohesive legal support throughout each stage of a case or transaction. Its work focuses on practical results aligned with client objectives, guided by clear strategies and informed assessments.</p>
    <p>The firm also maintains an active litigation practice, with lawyers regularly appearing before courts and administrative bodies in a variety of cases.</p>

    <h2>Full and Integrated Service Approach</h2>
    <p>The firm offers an integrated legal service for the diverse and evolving needs of its clients. Practitioners across fields coordinate to provide cohesive advice and representation. From corporate transactions and regulatory compliance to dispute resolution and specialized practice areas, the firm provides end-to-end support within a unified framework.</p>

    <h2>Results-Oriented Representation</h2>
    <p>Legal services are directed toward outcomes aligned with each client’s objectives. The firm evaluates matters based on their merits, legal context, and practical implications, then develops actionable strategies that address risks and opportunities and move matters toward resolution.</p>

    <h2>Courtroom Experience</h2>
    <p>The firm’s lawyers appear before trial and appellate courts, as well as administrative and quasi-judicial bodies. Its litigation practice covers civil, criminal, commercial, and labor disputes, informed by experience in courtroom procedure, evidence, and legal argument.</p>

    <h2>60-Year Track Record</h2>
    <p>Established in 1963, the firm has served clients across industries and sectors for over six decades. Its longstanding presence reflects continuity, institutional knowledge, and a sustained commitment to legal service. The firm has handled a broad range of cases and transactions while adapting to legislative and regulatory developments in the Philippines.</p>
  </div>

  <a class="home-about__link" href="{{ route('home') }}">← Back to Home</a>
@endsection
