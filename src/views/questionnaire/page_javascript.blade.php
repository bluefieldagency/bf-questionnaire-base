<script>
    var checkIntermediateStoreLoading = false;

    document.addEventListener('click', function (event) {
        if (event.target.matches('.extra-info--trigger')) {
            let targetElement = document.getElementById(event.target.dataset.target);
            if (targetElement) {
                targetElement.classList.toggle('hidden');
                event.target.classList.toggle('open');
            }
        } else {
            if ( ! event.target.matches('.extra-info, .extra-info--background')) {
                // close info overlays which might be open, unless you click on the overlay itself
                let previousTrigger = document.querySelector('.extra-info--trigger.open');
                if (previousTrigger) {
                    previousTrigger.classList.remove('open');

                    let previousElement = previousTrigger.querySelector('.extra-info--container');
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
            // todo: multiple additional questions per element are not handled correctly right now (lean working)
            let nextQuestion = null;
            if (parent.classList.contains('has-children') && event.target.dataset.data_type !== undefined) {
                let additionalChildrenContainer = parent.querySelector('ul.additional-questions-container');
                if (additionalChildrenContainer) {
                    additionalChildrenContainer.classList.remove('visible');

                    // reset all the additional questions back to hidden
                    let additionalChildren = parent.querySelectorAll('li.additional-question-container')
                    if (additionalChildren) {
                        additionalChildren.forEach(function (element, index) {
                            element.classList.remove('visible');

                            element.querySelectorAll('input, textarea').forEach(function(input, index) {
                                input.required = false;
                            });
                        });
                    }

                    // and now make the relevant additional questions visible
                    additionalChildren = parent.querySelectorAll('li[data-answer_trigger="' + event.target.dataset.data_type + '"]');
                    if (additionalChildren) {
                        additionalChildrenContainer.classList.add('visible');

                        additionalChildren.forEach(function (element, index) {
                            if ( ! nextQuestion) {
                                nextQuestion = element;
                            }

                            element.classList.add('visible');
                            if (element.classList.contains('is-required')) {
                                element.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function (input, index) {
                                    input.required = true;
                                });
                            }
                        });
                    }
                }
            }

            if (event.target.matches('.skip-trigger')) {
                let skipToRequest = event.target.dataset.skip;
                let questionParent = event.target.closest('.form-line--parent');
                let allQuestionsContainer = event.target.closest('.questions');
                if (allQuestionsContainer) {
                    let questions = allQuestionsContainer.querySelectorAll('.form-line--parent');
                    if (questions) {
                        let skipTriggerIndex, skipToIndex = false;

                        // find the skipFrom and skipTo indexes
                        questions.forEach(function(element, index) {
                            if (element.dataset.question_id === questionParent.dataset.question_id) {
                                skipTriggerIndex = index;
                            }
                            if (element.dataset.question_id === skipToRequest) {
                                nextQuestion = element;
                                skipToIndex = index;
                            }
                        });

                        if (skipTriggerIndex !== false && skipToIndex !== false && skipToIndex > skipTriggerIndex) {
                            questions.forEach(function(element, index) {
                                // check the 'not relevant' answer and set the question 'answered' for skipped questions
                                if (index > skipTriggerIndex && index < skipToIndex) {
                                    let notRelevantAnswerElement = element.querySelector('*[data-data_type="not_relevant"]');
                                    if (notRelevantAnswerElement) {
                                        notRelevantAnswerElement.checked = true;
                                        element.classList.add('answered');
                                        element.classList.remove('disabled');
                                    }
                                }
                            });
                        }
                    }
                }
            }

            if (nextQuestion) {
                setNextCurrent(null, true, nextQuestion);
            } else {
                setNextCurrent();
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

                        setNextCurrent(event.target.dataset.check_method);
                    } else {
                        // uncheck logic for 'none of the above'
                        let answers = parent.querySelectorAll('input[type="checkbox"]');
                        answers.forEach(function(element) {
                            if (element.dataset.check_method === 'disable_rest') {
                                element.checked = false;
                            }
                        });

                        setNextCurrent(null, false);
                    }
                } else {
                    setNextCurrent();
                }
            } else {
                enableSubmitButton(false);
            }
        } else if (event.target.matches('.intermediate-store-link')) {
            event.preventDefault();

            if ( ! checkIntermediateStoreLoading) {
                const xmlhttp = new XMLHttpRequest();
                const url = '{{ route('questionnaire.intermediate-store', ['questionnaire' => $questionnaire, 'page' => $page]) }}';
                const form = document.getElementById('questionnaire_page_{{ $page->id }}');
                const formData = new FormData(form);
                form.classList.add('sending');
                checkIntermediateStoreLoading = true;

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
        } else if (event.target.matches('.file-preview-remove *')) {
            event.preventDefault();

            let element = event.target;
            if ( ! element.classList.contains('file-preview-remove')) {
                element = event.target.closest('.file-preview-remove');
            }

            const xmlhttp = new XMLHttpRequest();
            xmlhttp.open("POST", '{{ route('questionnaire.remove-file') }}');
            xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xmlhttp.setRequestHeader('Content-type', 'application/json');
            var data = {_token: document.querySelector('meta[name="csrf-token"]').content, file: element.dataset.remove};
            xmlhttp.send(JSON.stringify(data));

            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4) {
                    var jsonData = JSON.parse(this.responseText);

                    if (this.status === 200) {
                        if (jsonData.removed !== undefined) {
                            let element = document.querySelector('*[data-remove="' + jsonData.removed + '"]');
                            if (element) {
                                let parent = element.closest('.file-preview');
                                let parentsContainer = element.closest('.file-preview-container');
                                if (parent) {
                                    parent.parentNode.removeChild(parent);
                                }
                                if ( ! parentsContainer.querySelector('.file-preview')) {
                                    parentsContainer.parentNode.removeChild(parentsContainer);
                                }
                            }

                            let parent = element.closest('.file-preview');

                            Notifications.success('@lang('bf::translations.removed')');
                        }
                    } else if (this.status === 419) {
                        alert('{{ __('De sessie was verlopen, de pagina wordt opnieuw ingeladen.')  }}');

                        location.reload();
                    }
                }
            };
        }
    });

    function setNextCurrent(checkMethod, doScroll, fixedElement) {
        if (doScroll === undefined) {
            doScroll = true;
        }

        if (fixedElement === undefined) {
            fixedElement = null;
        }

        let currentElement = document.querySelector('.current');
        if (currentElement) {
            currentElement.classList.remove('current');
        }

        let elements = document.querySelectorAll('.form-line.visible');
        let nextIndex = 0;
        let notAnsweredFound = false;
        elements.forEach(function(element, index) {
            if ( ! element.classList.contains('answered') && ! notAnsweredFound) {
                fixedElement = element;
                notAnsweredFound = true;
            }
        });

        if (fixedElement) {
            elements.forEach(function (element, index) {
                if (element.dataset.question_id === fixedElement.dataset.question_id) {
                    nextIndex = index;
                }
            });
        }

        if (nextIndex >= 0 && elements[nextIndex]) {
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

        /*
        if (parent.dataset.question_type === 'checkbox' && checkMethod === 'disable_rest') {
            let element = document.querySelector('.form-line--parent.current');

            if (element) {
                General.scrollTo(element);
            }
        }
         */

        enableSubmitButton(doScroll);
        setProgress();
    }

    function setProgress() {
        @if ($questionnaire->hasProgressPages() && $questionnaire->showProgressForThisPage($page))
            @if ($questionnaire->getProgressPagesAmount() > 1)
                // nothing to do here, blade templates will handle this
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
        let warning = document.getElementById('submit_button_warning');
        let button = document.querySelector('.submit-button');

        if (answeredCount >= questionCount) {
            button.classList.remove('disabled');

            if (doScroll) {
                General.scrollTo(button);
            }
            if (warning) {
                warning.classList.remove('visible');
            }
        } else {
            button.classList.add('disabled');

            if (warning) {
                warning.classList.add('visible');

                if (document.querySelector(':invalid')) {
                    warning.innerText = '{{ __('Nog niet alle verplichte velden zijn ingevuld') }}';
                } else {
                    warning.innerText = '{{ __('Nog niet alle verplichte velden zijn correct ingevuld') }}';
                }
            }
        }
    }

    document.addEventListener('keyup', function (event) {
        handleTextableInput(event.target);
    });

    document.addEventListener('focusout', function (event) {
        handleTextableInput(event.target);
    });

    function handleTextableInput(element) {
        let parent = element.closest('.form-line');

        if (element.matches('input[type="text"]') || element.matches('input[type="email"]') || element.matches('textarea')) {
            if (parent && element.value.trim() !== '') {
                if ((element.matches('input[type="email"]') && validateEmail(element.value)) || ! element.matches('input[type="email"]')) {
                    parent.classList.add('answered');
                } else if ((element.matches('input[type="email"]') && ! validateEmail(element.value))) {
                    parent.classList.remove('answered');
                }

                setNextCurrent(null, false);
            } else if (parent && element.value.trim() === '') {
                parent.classList.remove('answered');
            }
        }
    }

    document.addEventListener('change', function (event) {
        if (event.target.matches('input[type="file"]')) {
            let parent = event.target.closest('.form-line');
            if (parent.classList.contains('question-type--file')) {
                if (parent && event.target.value !== '') {
                    parent.classList.add('answered');
                }

                setNextCurrent();
            } else if (parent.classList.contains('question-type--text')) {
                let textElement = parent.querySelector('input[type="text"]');
                if (textElement && textElement.required && textElement.value.trim() === '' && parent && event.target.value !== '') {
                    textElement.value = '{{ __('Zie bijlage') }}';
                    parent.classList.add('answered');
                } else if (textElement && textElement.required && textElement.value.trim() === '{{ __('Zie bijlage') }}' && parent && event.target.value === '') {
                    textElement.value = '';
                    parent.classList.add('answered');
                }

                setNextCurrent();
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => {
            let elements = document.querySelectorAll('.form-line--parent');
            let fixedIndex = 0;
            let fixedElement = 0;

            if (elements) {
                elements.forEach(function(element, index) {
                    if (element.classList.contains('question-type--radio') || element.classList.contains('question-type--checkbox')) {
                        let answered = element.querySelector('input:checked');
                        if (answered) {
                            element.classList.remove('disabled');
                            element.classList.add('answered');

                            fixedIndex = index + 1;

                            // questions can have additonal questions (children), which are triggered by specific answer data types
                            if (element.classList.contains('has-children')) {
                                if (answered.dataset.data_type !== undefined) {
                                    let additionalChildrenContainer = element.querySelector('ul.additional-questions-container');
                                    let additionalChildren = element.querySelectorAll('li.form-line--child');

                                    if (additionalChildrenContainer && additionalChildren) {
                                        additionalChildren.forEach(function (child, index) {
                                            if (child.dataset.answer_trigger === answered.dataset.data_type) {
                                                additionalChildrenContainer.classList.add('visible');
                                                child.classList.add('visible');

                                                if (child.classList.contains('is-required')) {
                                                    child.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function (input, index) {
                                                        input.required = true;
                                                    });
                                                }

                                                if (child.value !== '') {
                                                    child.classList.add('answered');
                                                    child.classList.remove('disabled');
                                                }
                                            } else {
                                                additionalChildren.forEach(function (child, index) {
                                                    child.querySelectorAll('input, textarea').forEach(function (input, index) {
                                                        input.required = false;
                                                    });
                                                });
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    } else if (element.classList.contains('question-type--text') || element.classList.contains('question-type--email')) {
                        let input = element.querySelector('input');
                        if (input && input.value !== '') {
                            element.classList.add('answered');

                            fixedIndex = index + 1;
                        }
                    } else if (element.classList.contains('question-type--textarea')) {
                        let input = element.querySelector('textarea');
                        if (input && input.value !== '') {
                            element.classList.add('answered');

                            fixedIndex = index + 1;
                        }
                    }
                });

                setNextCurrent();
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