<label for="question_{{ $question->id }}_answer">{!! $question->title !!}</label>

@include('questionnaire::question_types.' . $question->question_type->type)

@if (($question->hasOption('extra_info') && ! $question->hasOption('extra_info_triggered')) || $question->getOption('extra_info_triggered') !== true)
    <p class="extra-info">{!! $question->getOption('extra_info') !!}</p>
@endif

<div class="error-form">{{ $errors->first('question_' . $question->id . '_answer') }}</div>
