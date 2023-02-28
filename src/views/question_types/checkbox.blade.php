<div class="question-container @if($question->hasOption('columns')) answers-columns-{{ $question->getOption('columns') }} @endif">
    @foreach($question->answers as $answer)
        <div class="answer-container styled-checkbox">
            <input
                type="checkbox"
                id="question_{{ $question->id }}_answer_{{ $answer->id }}"
                name="question_{{ $question->id }}_answer[{{ $answer->id }}]"
                value="1"
                class="
                    @if ($question->is_required) is-required @endif
                "
                data-answer_id="{{ $answer->id }}"
                @if ($answer->hasOption('check_method'))
                    data-check_method="{{ $answer->getOption('check_method') }}"
                @endif
                @if ($question->is_required && ( ! isset($child) || $child === false))
                    required
                @endif
                @if ($answer->hasOption('data_type'))
                    data-data_type="{{ $answer->getOption('data_type') }}"
                @endif
                @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
                    @if (is_array(session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer')))
                        @foreach (session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') as $answerId => $boolean)
                            @if ($answerId == $answer->id)
                                checked
                            @endif
                        @endforeach
                    @elseif (session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') == $answer->id)
                        checked
                    @endif
                @endif
            >

            <label for="question_{{ $question->id }}_answer_{{ $answer->id }}" class="option-label">{{ $answer->title }}</label>

            @if ($answer->hasOption('extra_info'))
                <span class="extra-info--trigger" data-target="extra_info_{{ $question->id }}_{{ $answer->id }}" data-answer_id="{{ $answer->id }}">
                    <div id="extra_info_{{ $question->id }}_{{ $answer->id }}" class="extra-info--container hidden">
                        <div class="extra-info--background">
                            <em class="extra-info">{{ $answer->getOption('extra_info') }}</em>
                        </div>
                    </div>
                </span>
            @endif
        </div>
    @endforeach
</div>
