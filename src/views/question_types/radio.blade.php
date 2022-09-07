@foreach($question->answers as $answer)
    <div class="answer-container styled-radio">
        <input
            type="radio"
            id="question_{{ $question->id }}_answer_{{ $answer->id }}"
            name="question_{{ $question->id }}_answer"
            value="{{ $answer->id }}"
            class="
                @if ($answer->hasOption('skip_to')) skip-trigger @endif
                @if ($question->is_required) is-required @endif
            "
            @if ($question->is_required)
                required
            @endif
            @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
                @if (session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') == $answer->id)
                    checked
                @endif
            @endif
            @if ($answer->hasOption('skip_to'))
                data-skip="{{ $answer->getOption('skip_to') }}"
            @endif
            @if ($answer->hasOption('data_type'))
                data-data_type="{{ $answer->getOption('data_type') }}"
            @endif
        >
        <label for="question_{{ $question->id }}_answer_{{ $answer->id }}">{{ $answer->title }}</label>
    </div>
@endforeach
