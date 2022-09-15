<input
    type="{{ (isset($type)) ? $type : 'text' }}"
    id="question_{{ $question->id }}_answer"
    name="question_{{ $question->id }}_answer"
    @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
        value="{{ session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') }}"
    @endif
    placeholder=" "
    @if ($question->is_required && ( ! isset($child) || $child === false))
        required
    @endif
>