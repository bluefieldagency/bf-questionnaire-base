@php
    $min = 0;
    if ($question->hasOption('min')) {
        $min = $question->getOption('min');
    }
    $max = 100;
    if ($question->hasOption('max')) {
        $max = $question->getOption('max');
    }
    $step = 1;
    if ($question->hasOption('step')) {
        $step = $question->getOption('step');
    }

    $steps = [];
    for($i = $min; $i <= $max; $i += $step) {
        $steps[] = $i;
    }
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

    <datalist id="tickmarks_{{ $question->id }}">
        @for($i = 0; $i < sizeof($steps); $i++)
            <option
                value="{{ $steps[$i] }}"
                label="{{ $steps[$i] }}"
                style="left: {{ ($i * (100 / (sizeof($steps) - 1))) }}%"
            ></option>
        @endfor
    </datalist>
</div>
