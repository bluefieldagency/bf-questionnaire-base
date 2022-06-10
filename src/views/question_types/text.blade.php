<div class="question-container">
    <input
        type="{{ (isset($type)) ? $type : 'text' }}"
        id="question_{{ $question->id }}_answer"
        name="question_{{ $question->id }}_answer"
        placeholder="{{ $question->placeholder }}"
        @if ($question->is_required)
            required
        @endif
    >
</div>
