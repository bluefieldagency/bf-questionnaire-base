<select
    id="question_{{ $question->id }}"
    name="question_{{ $question->id }}_answer"
    class="@if ($question->is_required && ( ! isset($child) || $child === false)) is-required @endif"
>
    @if ($question->hasOption('placeholder'))
        <option value="">{{ $question->getOption('placeholder') }}</option>
    @endif
    @foreach($question->answers as $answer)
        <option
            value="{{ $answer->id }}"
            @if (session()->has('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer'))
                @if (session('questionnaire.page.' . $page->id . '.question_' . $question->id . '_answer') == $answer->id)
                    selected
                @endif
            @endif
            @if ($answer->hasOption('data_type'))
                data-data_type="{{ $answer->getOption('data_type') }}"
            @endif
            @if ($answer->hasOption('extra_info'))
                data-extra_info="{{ $answer->getOption('extra_info') }}"
            @endif
        >
            {{ $answer->title }}
        </option>
    @endforeach
</select>

<div class="extra-info extra-info--option"></div>
