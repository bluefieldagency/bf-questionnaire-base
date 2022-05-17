<div class="question-container">
    @foreach($question->answers as $answer)
        <div class="answer-container styled-radio">
            <input type="radio" id="question_{{ $question->id }}_answer_{{ $answer->id }}" name="question_{{ $question->id }}_answer" value="{{ $answer->id }}">
            <label for="question_{{ $question->id }}_answer_{{ $answer->id }}">{{ $answer->title }}</label>
        </div>
    @endforeach
</div>