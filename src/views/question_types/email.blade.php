@include('questionnaire::question_types.text', ['type' => 'email'])

@push('email_disclaimer')
    <div class="disclaimer disclaimer--email">
        <p>* Met het invullen van je e-mailadres geef je automatisch akkoord dat Blue Field Agency de resultaten en oplossingen per e-mail mag delen.</p>
    </div>
@endpush