<h4><label for="question_{{ $question->id }}_answer">{{ $question->title }}</label></h4>

@if ($question->hasOption('extra_info'))
    <p class="extra-info">{{ $question->getOption('extra_info') }}</p>
@endif

<div class="error-form">{{ $errors->first('question_' . $question->id . '_answer') }}</div>

@include('questionnaire::question_types.' . $question->question_type->type)
