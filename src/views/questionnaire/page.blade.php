@extends('questionnaire::bf_layout')

@section('content')

    @if ($questionnaire->hasProgressPages() && $questionnaire->showProgressForThisPage($page))
        <div id="progress_bar">
            <span id="progression" style="width: 5px"></span>
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

        function setNextCurrent() {
            let elements = document.querySelectorAll('.form-line');
            let nextIndex = 0;

            elements.forEach(function(element, index) {
                if (element.classList.contains('current')) {
                    nextIndex = index + 1;
                    element.classList.remove('current');
                }
            });

            if (nextIndex > 0) {
                if (elements[nextIndex]) {
                    elements[nextIndex].classList.add('current');
                    elements[nextIndex].classList.remove('disabled');

                    @if ($questionnaire->getProgressPagesAmount() == 1)
                        document.getElementById('current_indicator').innerText = nextIndex + 1;
                    @endif

                    General.scrollTo(elements[nextIndex]);
                }
            }

            enableSubmitButton();
            setProgress();
        }

        function setProgress() {
            @if ($questionnaire->hasProgressPages() && $questionnaire->showProgressForThisPage($page))
                @if ($questionnaire->getProgressPagesAmount() > 1)
                @else
                    let elements = document.querySelectorAll('.form-line');
                    let questionCount = elements.length;

                    let answeredElements = document.querySelectorAll('.form-line.answered');
                    let answeredCount = answeredElements.length;

                    let width = (answeredCount / questionCount) * 100;

                    document.getElementById('progression').style.width = width + '%';
                @endif
            @endif
        }

        function enableSubmitButton() {
            let elements = document.querySelectorAll('.form-line');
            let questionCount = elements.length;

            let answeredElements = document.querySelectorAll('.form-line.answered');
            let answeredCount = answeredElements.length;

            let button = document.querySelector('.submit-button');

            if (questionCount == answeredCount) {
                button.classList.remove('disabled');

                General.scrollTo(button);
            } else {
                button.classList.add('disabled');
            }
        }

        document.addEventListener('click', function (event) {
            if (event.target.matches('input[type="radio"]')) {
                let parent = event.target.closest('.form-line');
                if (parent) {
                    parent.classList.add('answered');
                }

                setNextCurrent();
            } else if (event.target.matches('input[type="checkbox"]')) {
                let parent = event.target.closest('.form-line');
                let answeredCheckbox = parent.querySelector('input:checked');

                if (answeredCheckbox && parent) {
                    parent.classList.add('answered');
                } else {
                    parent.classList.remove('answered');
                }

                setNextCurrent();
            }
        });

        document.addEventListener('keyup', function (event) {
            if (event.target.matches('input[type="text"]') || event.target.matches('input[type="email"]')) {
                let parent = event.target.closest('.form-line');
                if (parent && event.target.value != '') {
                    parent.classList.add('answered');
                }

                setNextCurrent();
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                let elements = document.querySelectorAll('.form-line');
                let foundInput = false;

                if (elements) {
                    elements.forEach(function(element, index) {
                        if (element.classList.contains('question-type--radio') || element.classList.contains('question-type--checkbox')) {
                            answered = element.querySelector('input:checked');
                            if (answered) {
                                element.classList.add('answered');
                                element.classList.remove('current');
                                foundInput = true;
                            }
                        } else if (element.classList.contains('question-type--text') || element.classList.contains('question-type--email')) {
                            input = element.querySelector('input');
                            if (input && input.value != '') {
                                element.classList.add('answered');
                                element.classList.remove('current');
                                foundInput = true;
                            }
                        }
                    });

                    if (foundInput) {
                        setNextCurrent();
                    }
                }
            }, 500);
        });
    </script>

@endpush