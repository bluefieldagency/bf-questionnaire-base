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
                    let additionalChildren = parent.querySelectorAll('li.additional-question-container');
                    if (additionalChildren.length > 0) {
                        additionalChildren.forEach(function (element, index) {
                            element.classList.remove('visible');

                            element.querySelectorAll('input, textarea').forEach(function(input, index) {
                                input.required = false;
                            });
                        });
                    }

                    // and now make the relevant additional questions visible
                    additionalChildren = parent.querySelectorAll('li[data-answer_trigger="' + event.target.dataset.data_type + '"]');
                    if (additionalChildren.length > 0) {
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
                let allQuestionsContainer = event.target.closest('.questions-container');

                if (allQuestionsContainer) {
                    let questions = allQuestionsContainer.querySelectorAll('.form-line--parent');
                    if (questions.length > 0) {
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
        } else if (event.target.matches('input[type="range"]')) {
            let parent = event.target.closest('.form-line');
            if (parent) {
                parent.classList.add('answered');

                setNextCurrent();
            }
        } else if (event.target.matches('input[type="checkbox"]')) {
            let parent = event.target.closest('.form-line');
            let answeredCheckbox = parent.querySelector('input:checked');
            let elements = parent.querySelectorAll('input[type="checkbox"]');

            if (answeredCheckbox && parent) {
                parent.classList.add('answered');

                // set the checkboxes required to false, so you can submit this question if at lease on of the checkboxes is checked
                if (elements.length > 0) {
                    elements.forEach(function(element) {
                        element.required = false;
                    });
                }
            } else {
                parent.classList.remove('answered');

                // set the checkboxes required, so the browser helps you filling in the form correctly
                if (elements.length > 0 && parent.classList.contains('is-required')) {
                    elements.forEach(function(element) {
                        element.required = true;
                    });
                }
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
        } else if (event.target.matches('.additional-uploads-trigger-container, .additional-uploads-trigger-container *')) {
            let parent = event.target.closest('.form-line');
            let additionalUploadsContainer = parent.querySelector('.additional-uploads-container');
            if (additionalUploadsContainer) {
                additionalUploadsContainer.classList.remove('hidden');
            }
        } else if (event.target.matches('.intermediate-store-link')) {
            event.preventDefault();

            @if (Route::has('questionnaire.intermediate-store'))
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
                                    Notifications.success('@lang('bf::translations.stored') <a href="{{ route('questionnaire-entries.index') }}">Terug naar het overzicht?</a>');
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
            @endif
        } else if (event.target.matches('.file-preview-remove *')) {
            event.preventDefault();

            @if (Route::has('questionnaire.remove-file'))
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
            @endif
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

        let elements = document.querySelectorAll('.form-line--parent, .form-line--child.required');
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
            if ( ! elements[nextIndex].classList.contains('is_required')) {
                let neighbour = elements[nextIndex + 1 ];
                if (neighbour) {
                    neighbour.classList.remove('disabled');
                }
            }

            @if ($questionnaire->getProgressPagesAmount() == 1)
                if (document.getElementById('current_indicator')) {
                    document.getElementById('current_indicator').innerText = nextIndex + 1;
                }
            @endif

            if (doScroll) {
                General.scrollTo(elements[nextIndex]);
            }
        }

        enableSubmitButton(doScroll);
        setProgress();
    }

    function setNextEnabled() {
        let elements = document.querySelectorAll('.form-line.visible');
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
            // button.classList.add('disabled');

            if (warning) {
                if (inputChanged) {
                    warning.classList.add('visible');
                }

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
                    textElement.value = '@lang('bf::translations.see-attachment')';
                    parent.classList.add('answered');
                } else if (textElement && textElement.required && textElement.value.trim() === '@lang('bf::translations.see-attachment')' && parent && event.target.value === '') {
                    textElement.value = '';
                    parent.classList.add('answered');
                }

                setNextCurrent();
            }
        } else if (event.target.matches('select')) {
            let selectedOption = event.target.options[event.target.selectedIndex];
            let parent = event.target.closest('.form-line');
            if (parent) {
                parent.classList.add('answered');
            }

            let additionalChildrenContainer = parent.querySelector('ul.additional-questions-container');
            if (additionalChildrenContainer) {
                additionalChildrenContainer.classList.remove('visible');
            }

            // questions can have additonal questions (children), which are triggered by specific answer data types
            // todo: multiple additional questions per element are not handled correctly right now (lean working)
            let nextQuestion = null;
            if (parent.classList.contains('has-children') && selectedOption.dataset.data_type !== undefined) {
                if (additionalChildrenContainer) {
                    // reset all the additional questions back to hidden
                    let additionalChildren = parent.querySelectorAll('li.additional-question-container');
                    if (additionalChildren.length > 0) {
                        additionalChildren.forEach(function (element, index) {
                            element.classList.remove('visible');

                            element.querySelectorAll('input, textarea').forEach(function(input, index) {
                                input.required = false;
                            });
                        });
                    }

                    // and now make the relevant additional questions visible
                    additionalChildren = parent.querySelectorAll('li[data-answer_trigger="' + selectedOption.dataset.data_type + '"]');
                    if (additionalChildren.length > 0) {
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

            if (nextQuestion) {
                setNextCurrent(null, true, nextQuestion);
            } else {
                setNextCurrent();
            }
        }

        if (event.target.matches('input, textarea')) {
            inputChanged = true;
        }
    });

    let inputChanged = false;
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => {
            let elements = document.querySelectorAll('.form-line--parent');

            if (elements.length > 0) {
                elements.forEach(function(element, index) {
                    if (element.classList.contains('question-type--radio') || element.classList.contains('question-type--stars') || element.classList.contains('question-type--checkbox')) {
                        let answered = element.querySelector('input:checked');
                        if (answered) {
                            inputChanged = true;
                            element.classList.remove('disabled');
                            element.classList.add('answered');

                            if (element.classList.contains('question-type--checkbox')) {
                                let answeredCheckbox = element.querySelector('input:checked');
                                let checkboxes = element.querySelectorAll('input[type="checkbox"]');

                                if (answeredCheckbox) {
                                    // set the checkboxes required to false, so you can submit this question if at lease on of the checkboxes is checked
                                    if (checkboxes.length > 0) {
                                        checkboxes.forEach(function(checkbox) {
                                            checkbox.required = false;
                                        });
                                    }
                                }
                            }

                            // questions can have additonal questions (children), which are triggered by specific answer data types
                            if (element.classList.contains('has-children')) {
                                if (answered.dataset.data_type !== undefined) {
                                    let additionalChildrenContainer = element.querySelector('ul.additional-questions-container');
                                    let additionalChildren = element.querySelectorAll('li.form-line--child');

                                    if (additionalChildrenContainer && additionalChildren.length > 0) {
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
                        let input = element.querySelector('.question-input input');
                        if (input && input.value !== '') {
                            inputChanged = true;
                            element.classList.add('answered');
                        }
                    } else if (element.classList.contains('question-type--textarea')) {
                        let input = element.querySelector('textarea');
                        if (input && input.value !== '') {
                            inputChanged = true;
                            element.classList.add('answered');
                        }
                    } else if (element.classList.contains('question-type--range')) {
                        element.classList.add('answered');
                    }
                });

                setNextCurrent(null, false);
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
