@include('questionnaire::question_types.text', ['type' => 'email'])

@if ( ! $question->hasOption('hide_disclaimer' || ($question->hasOption('hide_disclaimer') && $question->getOption('hide_disclaimer') !== true)))
    @push('email_disclaimer')
        <div class="disclaimer disclaimer--email">
            <p>@lang('bf::translations.disclaimer-email')</p>
        </div>
    @endpush
@endif