<div class="question-container">
    @foreach($question->answers as $answer)
        <div class="answer-container styled-radio">
            <input
                type="radio"
                id="question_{{ $question->id }}_answer_{{ $answer->id }}"
                name="question_{{ $question->id }}_answer"
                value="{{ $answer->id }}"
                @if ($question->is_required)
                    required
                @endif
                @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
                    @if (session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') == $answer->id)
                        checked
                    @endif
                @endif
            >
            <label for="question_{{ $question->id }}_answer_{{ $answer->id }}">{{ $answer->title }}</label>
        </div>
    @endforeach
</div>