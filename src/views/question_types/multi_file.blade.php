@for($i = 1; $i <= (($question->hasOption('additional_upload_max')) ? $question->getOption('additional_upload_max') : 1); $i++)
    <input
        type="file"
        id="question_{{ $question->id }}_answer"
        name="question_{{ $question->id }}_answer_file[]"
        @if ($question->hasOption('additional_upload_min') && $question->getOption('additional_upload_min') >= $i)
            required
        @endif
    >
@endfor