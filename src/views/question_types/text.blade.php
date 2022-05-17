<div class="question-container">
    <label for="question_{{ $question->id }}_answer">{{ $question->title }}</label>
    <input type="text" id="question_{{ $question->id }}_answer" name="question_{{ $question->id }}_answer" placeholder="{{ optional($question->options)['placeholder'] }}">
</div>
