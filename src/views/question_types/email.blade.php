@include('questionnaire::question_types.text', ['type' => 'email'])

@push('email_disclaimer')
    <div class="disclaimer disclaimer--email">
        <p>@lang('bf::translations.disclaimer-email')</p>
    </div>
@endpush