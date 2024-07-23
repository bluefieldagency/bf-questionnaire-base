<input
    type="{{ (isset($type)) ? $type : 'text' }}"
    id="question_{{ $question->id }}_answer"
    name="question_{{ $question->id }}_answer"
    @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
        value="{{ session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') }}"
    @elseif ($question->hasOption('data_type') && session()->has('questionnaire.' . $question->getOption('data_type')))
        value="{{ session('questionnaire.' . $question->getOption('data_type')) }}"
    @endif
    @if ($question->placeholder)
        placeholder="{{ $question->placeholder }}"
    @elseif ($question->question_type->placeholder)
        placeholder="{{ $question->question_type->placeholder }}"
    @endif
    @if ($question->is_required && ( ! isset($child) || $child === false))
        required
    @endif
>
