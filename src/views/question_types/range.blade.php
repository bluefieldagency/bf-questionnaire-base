@php
    $min = 1;
    if ($question->hasOption('min') && $question->getOption('min') !== null) {
        $min = $question->getOption('min');
    }
    $max = 10;
    if ($question->hasOption('max') && $question->getOption('max') !== null) {
        $max = $question->getOption('max');
    }
    $step = 1;
    if ($question->hasOption('step') && $question->getOption('step') !== null) {
        $step = $question->getOption('step');
    }

    $desiredLength = 10; // How many items we want in the array
    $maxLengthBeforeAdjustment = 20; // The max number of items before we adjust the step size

    // Calculate the total range
    $range = $max - $min;

    // Calculate the number of values the current setup will produce
    $currentLength = floor($range / $step) + 1;

    // If currentLength exceeds maxLengthBeforeAdjustment, recompute the step size
    if ($currentLength > $maxLengthBeforeAdjustment) {
        $step = $range / ($desiredLength - 1); // -1 because for 10 numbers, there are 9 intervals
    }

    $steps = [];
    for ($i = $min; $i < $max; $i += $step) {
        $steps[] = round($i); // Use round() to avoid potential floating point inaccuracies
    }
    $steps[] = $max;
@endphp

<div class="range-slider range-slider-container">
    <input
        type="range"
        id="question_{{ $question->id }}_answer"
        name="question_{{ $question->id }}_answer"
        class="range-slider"
{{--        list="tickmarks_{{ $question->id }}"--}}
        @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
            value="{{ session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') }}"
        @elseif ($question->hasOption('default_value'))
            value="{{ $question->getOption('default_value') }}"
        @endif

        min="{{ $min }}"
        max="{{ $max }}"
        step="{{ $step }}"

        @if ($question->is_required && ( ! isset($child) || $child === false))
            required
        @endif
    >

    <span
        id="tickmarks_{{ $question->id }}"
        class="datalist"
    >
        @for($i = 0; $i < sizeof($steps); $i++)
            <span
                class="option"
                style="left: {{ ($i * (100 / (sizeof($steps) - 1))) }}%"
            >
            {{ $steps[$i] }}
            </span>
        @endfor
    </span>
</div>
