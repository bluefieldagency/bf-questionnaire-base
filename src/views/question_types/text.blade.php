<input
    type="{{ (isset($type)) ? $type : 'text' }}"
    id="question_{{ $question->id }}_answer"
    name="question_{{ $question->id }}_answer"
    placeholder=" "
    @if ($question->is_required)
        required
    @endif
>