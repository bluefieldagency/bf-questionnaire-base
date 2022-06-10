<div class="question-container">
    @foreach($question->answers as $answer)
        <div class="answer-container styled-checkbox">
            <input
                type="checkbox"
                id="question_{{ $question->id }}_answer_{{ $answer->id }}"
                name="question_{{ $question->id }}_answer[{{ $answer->id }}]"
                value="1"
                data-answer_id="{{ $answer->id }}"
                @if ($answer->hasOption('check_method'))
                    data-check_method="{{ $answer->getOption('check_method') }}"
                @endif
            >

            <label for="question_{{ $question->id }}_answer_{{ $answer->id }}">{{ $answer->title }}</label>

            @if ($answer->hasOption('extra_info'))
                <span class="extra-info--trigger" data-target="extra_info_{{ $question->id }}_{{ $answer->id }}"></span>
                <div id="extra_info_{{ $question->id }}_{{ $answer->id }}" class="extra-info--container hidden">
                    <em class="extra-info">{{ $answer->getOption('extra_info') }}</em>
                </div>
            @endif
        </div>
    @endforeach
</div>