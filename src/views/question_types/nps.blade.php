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

@include('questionnaire::question_types.range')
