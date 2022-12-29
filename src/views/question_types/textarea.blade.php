<textarea
    type="{{ (isset($type)) ? $type : 'text' }}"
    id="question_{{ $question->id }}_answer"
    name="question_{{ $question->id }}_answer"
    @if ($question->hasOption('placeholder'))
        placeholder="{{ $question->getOption('placeholder') }}"
    @elseif ($question->question_type->hasOption('placeholder'))
        placeholder="{{ $question->question_type->getOption('placeholder') }}"
    @endif
    rows="3"
    @if ($question->is_required && ( ! isset($child) || $child === false))
        required
    @endif
>@if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
{{ session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') }}
@endif</textarea>
