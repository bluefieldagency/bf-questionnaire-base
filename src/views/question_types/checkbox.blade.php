<div class="question-container">
    @foreach($question->answers as $answer)
        <div class="answer-container styled-checkbox">
            <input type="checkbox" id="question_{{ $question->id }}_answer_{{ $answer->id }}" name="question_{{ $question->id }}_answer[{{ $answer->id }}]" value="1">
            <label for="question_{{ $question->id }}_answer_{{ $answer->id }}">{{ $answer->title }}</label>
        </div>
    @endforeach
</div>