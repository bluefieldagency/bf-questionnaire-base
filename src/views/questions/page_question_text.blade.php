@include('questionnaire::question_types.' . $question->question_type->type)

<label for="question_{{ $question->id }}_answer">{{ $question->title }}</label>

@if (($question->hasOption('extra_info') && ! $question->hasOption('extra_info_triggered')) || $question->getOption('extra_info_triggered') !== true)
    <p class="extra-info">{!! $question->getOption('extra_info') !!}</p>
@endif

@if (($question->hasOption('allow_additional_uploads') && $question->getOption('allow_additional_uploads') === true))
    @include('questionnaire::question_types.multi_file', [
        'min' => $question->getOption('additional_upload_min'),
        'max' => $question->getOption('additional_upload_max')
    ])
@endif

<div class="error-form">{{ $errors->first('question_' . $question->id . '_answer') }}</div>
