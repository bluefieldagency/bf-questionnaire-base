@extends('questionnaire::bf_layout')

@section('content')

    @if ($questionnaire->hasProgressPages())
        <div id="progress_bar">
            <span id="progression" style="width: 10%"></span>
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
                        <h3 class="aeonik24 grey">Hulp nodig?</h3>
                        <a class="aeonik22" href="mailto:hallo@bluefieldagency.com">hallo@bluefieldagency.com</a>
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

    <script>
        document.addEventListener('click', function (event) {
            if (event.target.matches('.extra-info--trigger')) {
                var element = document.getElementById(event.target.dataset.target);

                if (element) {
                    element.classList.toggle('hidden');
                }
            }
        });
    </script>

@endpush