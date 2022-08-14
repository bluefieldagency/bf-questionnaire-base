@extends('questionnaire::bf_layout')

@section('content')

    @if ($questionnaire->hasProgressPages() && $questionnaire->showProgressForThisPage($page))
        <div id="progress_bar">
            @if ($questionnaire->getProgressPagesAmount() > 1)
                <span id="progression" style="width: {{ ($questionnaire->getProgressStepThisPage($page) / $questionnaire->getProgressPagesAmount()) * 100 }}%"></span>
            @else
                <span id="progression" style="width: 5px"></span>
            @endif
        </div>
    @endif

    <div class="content-container grey-bg questions-padding">
        @if ($page->show_help_aside)
            <div id="questionnaire_page" class="page--with-aside">
                <main>
                    @include('questionnaire::questionnaire.page_questions')
                </main>
                <aside>
                    <div class="help-content sticky-content">
                        <h3 class="aeonik24 grey">@lang('bf::translations.need-help')</h3>
                        <a class="aeonik22" href="mailto:letstalk@bluefieldagency.com">letstalk@bluefieldagency.com</a>
                        <a class="aeonik22" href="tel:+31 85 401 51 65">+31 85 401 51 65</a>
                    </div>
                </aside>
            </div>
        @else
            <div id="questionnaire_page" class="content-center--small">
                @include('questionnaire::questionnaire.page_questions')
            </div>
        @endif
    </div>

@endsection

@push('javascript')

    @include('questionnaire::questionnaire.page_javascript')

@endpush