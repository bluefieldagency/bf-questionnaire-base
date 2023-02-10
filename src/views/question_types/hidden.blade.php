<input
    type="hidden"
    id="question_{{ $question->id }}_answer"
    name="question_{{ $question->id }}_answer"
    @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
        value="{{ session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') }}"
    @elseif ($question->hasOption('data_type') && session()->has('questionnaire.' . $question->getOption('data_type')))
        value="{{ session('questionnaire.' . $question->getOption('data_type')) }}"
    @endif
>
