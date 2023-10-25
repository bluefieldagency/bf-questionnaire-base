<h4 class="group-label">{!! $question->title !!}</h4>

@include('questionnaire::components.question_extra_info')

@if (($question->hasOption('extra_info') && ! $question->hasOption('extra_info_triggered')) || $question->getOption('extra_info_triggered') !== true)
    <p class="extra-info">{!! $question->getOption('extra_info') !!}</p>
@endif

<div class="error-form">{{ $errors->first('question_' . $question->id . '_answer') }}</div>

<div class="question-answers-container">
    @if (sizeof($question->answers) < 1 && in_array($question->question_type->type, ['radio', 'checkbox', 'select']))
        @include('questionnaire::question_types.missing_answers')
    @else
        @include('questionnaire::question_types.' . $question->question_type->type)
    @endif
</div>
