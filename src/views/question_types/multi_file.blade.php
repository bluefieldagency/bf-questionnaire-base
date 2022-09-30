@for($i = 1; $i <= (($question->hasOption('additional_upload_max')) ? $question->getOption('additional_upload_max') : 1); $i++)
    <input
        type="file"
        id="question_{{ $question->id }}_answer"
        name="question_{{ $question->id }}_answer_file[]"
        tabindex="-1"
        @if (isset($min) && $min >= $i)
            required
        @elseif ($question->hasOption('additional_upload_min') && $question->getOption('additional_upload_min') >= $i)
            required
        @endif
    >
@endfor

@include('questionnaire::components.added_files')