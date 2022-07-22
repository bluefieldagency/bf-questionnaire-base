<input
    type="file"
    id="question_{{ $question->id }}_answer"
    name="question_{{ $question->id }}_answer"
    @if ($question->is_required)
        required
    @endif
>