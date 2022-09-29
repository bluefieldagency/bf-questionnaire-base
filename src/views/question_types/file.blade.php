<input
    type="file"
    id="question_{{ $question->id }}_answer"
    name="question_{{ $question->id }}_answer"
    tabindex="-1"
    @if ($question->is_required && ( ! isset($child) || $child === false))
        required
    @endif
>

@include('questionnaire::components.added_files')