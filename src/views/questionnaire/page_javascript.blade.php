<script>
    var checkIntermediateStoreLoading = false;

    document.addEventListener('click', function (event) {
        if (event.target.matches('.extra-info--trigger')) {
            let element = document.getElementById(event.target.dataset.target);
            if (element) {
                element.classList.toggle('hidden');
                event.target.classList.toggle('open');
            }
        } else {
            if ( ! event.target.matches('.extra-info, .extra-info--background')) {
                // close info overlays which might be open
                let previousTrigger = document.querySelector('.extra-info--trigger.open');
                let previousElement = null;
                if (previousTrigger) {
                    previousTrigger.classList.remove('open');

                    previousElement = previousTrigger.querySelector('.extra-info--container');
                    if (previousElement) {
                        previousElement.classList.add('hidden');
                    }
                }
            }
        }

        if (event.target.matches('input[type="radio"]')) {
            let parent = event.target.closest('.form-line');
            if (parent) {
                parent.classList.add('answered');
            }

            // questions can have additonal questions (children), which are triggered by specific answer data types
            if (parent.classList.contains('has-children') && event.target.dataset.data_type !== undefined) {
                let additionalChildrenContainer = parent.querySelector('ul.additional-questions-container');
                if (additionalChildrenContainer) {
                    additionalChildrenContainer.classList.remove('visible');

                    let additionalChildren = parent.querySelectorAll('li.additional-question-container')
                    if (additionalChildren) {
                        additionalChildren.forEach(function (element, index) {
                            element.classList.remove('visible');

                            element.querySelectorAll('input, textarea').forEach(function(input, index) {
                                input.required = false;
                            });
                        });
                    }

                    additionalChildren = parent.querySelectorAll('li[data-answer_trigger="' + event.target.dataset.data_type + '"]');
                    if (additionalChildren) {
                        additionalChildrenContainer.classList.add('visible');

                        additionalChildren.forEach(function (element, index) {
                            element.classList.add('visible');

                            element.querySelectorAll('input, textarea').forEach(function(input, index) {
                                input.required = true;
                            });
                        });
                    }
                }
            }

            if (event.target.matches('.skip-trigger')) {
                let skipToRequest = event.target.dataset.skip;
                let questionParent = event.target.closest('.form-line--parent');
                let questionsParent = event.target.closest('.questions');
                if (questionsParent) {
                    let questions = questionsParent.querySelectorAll('.form-line--parent');
                    if (questions) {
                        let skipTriggerIndex, skipToIndex = false;
                        questions.forEach(function(element, index) {
                            console.log(element, index);
                            if (element.dataset.question_id === questionParent.dataset.question_id) {
                                skipTriggerIndex = index;
                            }
                            if (element.dataset.question_id === skipToRequest) {
                                skipToIndex = index;
                            }
                        });

                        if (skipTriggerIndex !== false && skipToIndex !== false && skipToIndex > skipTriggerIndex) {
                            questions.forEach(function(element, index) {
                                if (index > skipTriggerIndex && index < skipToIndex) {
                                    element.classList.add('skipped');
                                }

                                if (index === (questions.length - 1)) {
                                    setNextCurrent(questionParent, null, true, 3);
                                }
                            });
                        }
                    }
                }
            } else {
                setNextCurrent(parent);
            }
        } else if (event.target.matches('input[type="checkbox"]')) {
            let parent = event.target.closest('.form-line');
            let answeredCheckbox = parent.querySelector('input:checked');

            if (answeredCheckbox && parent) {
                parent.classList.add('answered');
            } else {
                parent.classList.remove('answered');
            }

            if (event.target.checked) {
                if (parent.dataset.answer_count > 1 && parent.dataset.question_type === 'checkbox') {
                    if (event.target.dataset.check_method === 'disable_rest') {
                        // uncheck options
                        let answers = parent.querySelectorAll('input[type="checkbox"]');
                        answers.forEach(function(element) {
                            if (element.dataset.answer_id !== event.target.dataset.answer_id) {
                                element.checked = false;
                            }
                        });

                        setNextCurrent(parent, event.target.dataset.check_method);
                    } else {
                        // uncheck logic for 'none of the above'
                        let answers = parent.querySelectorAll('input[type="checkbox"]');
                        answers.forEach(function(element) {
                            if (element.dataset.check_method === 'disable_rest') {
                                element.checked = false;
                            }
                        });

                        setNextCurrent(parent, null, false);
                    }
                } else {
                    setNextCurrent(parent);
                }
            } else {
                enableSubmitButton(false);
            }
        } else if (event.target.matches('.intermediate-store-link')) {
            event.preventDefault();

            const xmlhttp = new XMLHttpRequest();
            const url = '{{ route('questionnaire.intermediate-store', ['questionnaire' => $questionnaire, 'page' => $page]) }}';
            const form = document.getElementById('questionnaire_page_{{ $page->id }}');
            const formData = new FormData(form);
            form.classList.add('sending');

            xmlhttp.open("POST", url);
            xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xmlhttp.send(formData);

            xmlhttp.onreadystatechange = function() {
                checkIntermediateStoreLoading = false;
                form.classList.remove('sending');

                if (this.readyState === 4) {
                    var jsonData = JSON.parse(this.responseText);

                    if (this.status === 200) {
                        if (jsonData.errors !== undefined) {
                            General.showErrors(jsonData.errors);
                        } else {
                            Notifications.success('@lang('bf::translations.stored')');
                        }
                    } else if (this.status === 419) {
                        alert('{{ __('De sessie was verlopen, de pagina wordt opnieuw ingeladen.')  }}');

                        location.reload();
                    } else {
                        General.showErrors(jsonData.errors);
                    }
                }
            };
        }
    });

    function setNextCurrent(parent, checkMethod, doScroll, fixedIndex) {
        if (doScroll === undefined) {
            doScroll = true;
        }

        if (fixedIndex === undefined) {
            fixedIndex = false;
        }

        let elements = document.querySelectorAll('.form-line--parent');

        if (parent.classList.contains('current')) {
            let nextIndex = 0;

            elements.forEach(function(element, index) {
                if (element.classList.contains('current') || nextIndex === 0) {
                    if (fixedIndex === false) {
                        nextIndex = index + 1;
                    } else {
                        nextIndex = fixedIndex;
                    }
                    element.classList.remove('current');
                }
            });

            if (nextIndex > 0) {
                if (elements[nextIndex]) {
                    elements[nextIndex].classList.add('current');
                    elements[nextIndex].classList.remove('disabled');

                    @if ($questionnaire->getProgressPagesAmount() == 1)
                        if (document.getElementById('current_indicator')) {
                            document.getElementById('current_indicator').innerText = nextIndex + 1;
                        }
                    @endif

                    if (doScroll) {
                        General.scrollTo(elements[nextIndex]);
                    }
                }
            }
        } else {
            if (parent.dataset.question_type === 'checkbox' && checkMethod === 'disable_rest') {
                let element = document.querySelector('.form-line--parent.current');

                if (element) {
                    General.scrollTo(element);
                }
            }
        }

        enableSubmitButton(doScroll);
        setProgress();
    }

    function setProgress() {
        @if ($questionnaire->hasProgressPages() && $questionnaire->showProgressForThisPage($page))
            @if ($questionnaire->getProgressPagesAmount() > 1)
            @else
                let elements = document.querySelectorAll('.form-line--parent');
                let questionCount = elements.length;

                let answeredElements = document.querySelectorAll('.form-line--parent.answered');
                let answeredCount = answeredElements.length;

                let width = (answeredCount / questionCount) * 100;

                document.getElementById('progression').style.width = width + '%';
            @endif
        @endif
    }

    function enableSubmitButton(doScroll) {
        if (doScroll === undefined) {
            doScroll = true;
        }

        let questionCount = 0;
        document.querySelectorAll('.form-line--parent').forEach(function(element, index) {
            if (element.querySelector(':required')) {
                questionCount += 1;
            }
        });

        let answeredElements = document.querySelectorAll('.form-line--parent.answered');
        let answeredCount = answeredElements.length;

        let button = document.querySelector('.submit-button');

        if (answeredCount >= questionCount) {
            button.classList.remove('disabled');

            if (doScroll) {
                General.scrollTo(button);
            }
        } else {
            button.classList.add('disabled');
        }
    }

    document.addEventListener('keyup', function (event) {
        let parent = event.target.closest('.form-line');

        if (event.target.matches('input[type="text"]') || event.target.matches('input[type="email"]') || event.target.matches('textarea')) {
            if (parent && event.target.value !== '') {
                if ((event.target.matches('input[type="email"]') && validateEmail(event.target.value)) || ! event.target.matches('input[type="email"]')) {
                    parent.classList.add('answered');

                    setNextCurrent(parent);
                }
            } else if (parent && event.target.value === '') {
                if ((event.target.matches('input[type="email"]') && validateEmail(event.target.value)) || ! event.target.matches('input[type="email"]')) {
                    parent.classList.remove('answered');
                }
            }
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches('input[type="file"]')) {
            let parent = event.target.closest('.form-line');
            if (parent.classList.contains('question-type--file')) {
                if (parent && event.target.value !== '') {
                    parent.classList.add('answered');
                }

                setNextCurrent(parent);
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => {
            let elements = document.querySelectorAll('.form-line--parent');
            let foundInput = false;
            let parent = null;
            let fixedIndex = 0;

            if (elements) {
                elements.forEach(function(element, index) {
                    if (element.classList.contains('question-type--radio') || element.classList.contains('question-type--checkbox')) {
                        let answered = element.querySelector('input:checked');
                        if (answered) {
                            element.classList.add('answered');

                            parent = element;
                            fixedIndex = index + 1;

                            // questions can have additonal questions (children), which are triggered by specific answer data types
                            if (element.classList.contains('has-children')) {
                                if (answered.dataset.data_type !== undefined) {
                                    let additionalChildrenContainer = element.querySelector('ul.additional-questions-container');
                                    if (additionalChildrenContainer) {
                                        let additionalChildren = element.querySelectorAll('li[data-answer_trigger="' + answered.dataset.data_type + '"]');
                                        if (additionalChildren) {
                                            additionalChildrenContainer.classList.add('visible');

                                            additionalChildren.forEach(function (element, index) {
                                                element.classList.add('visible');

                                                element.querySelectorAll('input, textarea').forEach(function(input, index) {
                                                    input.required = true;
                                                });
                                            });
                                        }
                                    }
                                }
                            }
                        }
                    } else if (element.classList.contains('question-type--text') || element.classList.contains('question-type--email')) {
                        let input = element.querySelector('input');
                        if (input && input.value !== '') {
                            element.classList.add('answered');

                            parent = element;
                            fixedIndex = index + 1;
                        }
                    } else if (element.classList.contains('question-type--textarea')) {
                        let input = element.querySelector('textarea');
                        if (input && input.value !== '') {
                            element.classList.add('answered');

                            parent = element;
                            fixedIndex = index + 1;
                        }
                    }

                    let element2 = element;

                    // questions can have additonal questions (children), which are triggered by specific answer data types
                    if (element.classList.contains('has-children') && (element.classList.contains('question-type--radio') || element.classList.contains('question-type--checkbox'))) {
                        let answered = element.querySelector('input:checked');
                        if (answered) {
                            element.classList.add('answered');

                            parent = element;
                            fixedIndex = index + 1;
                        }

                    }
                });

                if (parent) {
                    setNextCurrent(parent, null, true, fixedIndex);
                }
            }
        }, 500);
    });

    function validateEmail(email)
    {
        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
            return (true)
        }

        return (false)
    }
</script>