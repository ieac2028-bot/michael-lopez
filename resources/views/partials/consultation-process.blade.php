@php
  $flowExample = $flowExample ?? false;
  $showSummary = $showSummary ?? false;
  $summaryNote = $summaryNote ?? null;
  $doneThrough = $doneThrough ?? 0;
  $currentStep = $currentStep ?? null;
  $totalSteps = count($steps);
  $percent = $flowExample ? 0 : (int) round(min($doneThrough, $totalSteps) / max($totalSteps, 1) * 100);
  $currentLabel = $currentStep ? ($steps[$currentStep - 1] ?? null) : null;
  $processIcons = [
    1 => '<circle cx="12" cy="8" r="3.2"/><path d="M5.5 20c.6-3.6 2.8-5.5 6.5-5.5s5.9 1.9 6.5 5.5"/>',
    2 => '<path d="M4 5.5h16v11H11l-4.5 3v-3H4z"/><path d="M8 9.5h8M8 12.5h5"/>',
    3 => '<rect x="5" y="4.5" width="14" height="17" rx="2"/><path d="M9 4.5v-2h6v2M8.5 10h7M8.5 14h7M8.5 18h4"/>',
    4 => '<path d="M12 4v16M6 7h12M8 7l-4 7h8L8 7Zm8 0-4 7h8l-4-7ZM8 20h8"/>',
    5 => '<circle cx="10.5" cy="10.5" r="6"/><path d="m15 15 5 5"/>',
    6 => '<rect x="4" y="4" width="16" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M6.5 16c.3-1.5 1.1-2.2 2.5-2.2s2.2.7 2.5 2.2M14 9h3M14 13h3M14 17h3"/>',
    7 => '<circle cx="12" cy="12" r="8.5"/><path d="m8 12 2.6 2.6L16.5 9"/>',
    8 => '<path d="M6 3.5h8l4 4V20a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20z"/><path d="M14 3.5v4h4M9 13h6M9 16.5h3"/>',
    9 => '<path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
  ];
  $checkIcon = '<path d="m5 12.5 4.5 4.5L19 7.5"/>';
@endphp
@if($showSummary && ! $flowExample)
  <div class="cp-progress__head">
    <div>
      <p class="cp-progress__kicker">Your progress</p>
      <h2 class="cp-progress__title">
        @if($currentLabel)
          Step {{ $currentStep }} of {{ $totalSteps }} · {{ $currentLabel }}
        @else
          All {{ $totalSteps }} steps complete
        @endif
      </h2>
    </div>
    @if($summaryNote)<p class="cp-progress__note">{{ $summaryNote }}</p>@endif
  </div>
  <div class="cp-progress__bar" role="progressbar" aria-label="Overall progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}"><span style="width: {{ $percent }}%"></span></div>
@endif
<ul class="consultation-process{{ $flowExample ? ' is-flow-example' : '' }}" style="--process-step-count: {{ $totalSteps }}" aria-label="{{ $ariaLabel }}" tabindex="0">
  @foreach($steps as $index => $label)
    @php
      $number = $index + 1;
      $isDone = ! $flowExample && $number <= $doneThrough;
      $isCurrent = ! $flowExample && ! $isDone && $number === $currentStep;
      $state = $isDone ? 'is-done' : ($isCurrent ? 'is-current' : '');
    @endphp
    <li class="{{ $state }}" @if($isCurrent) aria-current="step" @endif>
      <span class="consultation-process__node" aria-hidden="true">
        <svg viewBox="0 0 24 24" focusable="false">{!! $isDone ? $checkIcon : ($processIcons[$number] ?? $processIcons[9]) !!}</svg>
      </span>
      <span class="consultation-process__copy">
        <strong>{{ $label }}</strong>
        <small>{{ $flowExample ? 'Request stage' : ($isDone ? 'Complete' : ($isCurrent ? 'In progress' : 'Upcoming')) }}</small>
      </span>
    </li>
  @endforeach
</ul>
