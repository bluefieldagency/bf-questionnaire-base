@extends('questionnaire::bf_layout')

@section('content')

    <div class="content-container grey-bg questions-padding">
        <div id="questionnaire_page" class="content-center--small">
            @if ( ! empty($page->title))
                <h1 class="page-title">{{ $page->title }}</h1>
            @endif

            @if ( ! empty($page->intro))
                <p class="page-intro">{!! $page->intro !!}</p>
            @endif

            <form id="questionnaire_page_{{ $page->id }}" method="POST">
                @csrf

                @foreach($page->questions as $question)
                    <div class="form-line">
                        <h4><label for="question_{{ $question->id }}_answer">{{ $question->title }}</label></h4>

                        @if ($question->hasOption('extra_info'))
                            <p class="extra-info">{{ $question->getOption('extra_info') }}</p>
                        @endif

                        <div class="error-form">{{ $errors->first('question_' . $question->id . '_answer') }}</div>

                        @include('questionnaire::question_types.' . $question->question_type->type)
                    </div>
                @endforeach

                @component('questionnaire::components.button')
                    @slot('type')
                        submit
                    @endslot

                    @slot('label')
                        {{ $page->continue_button_label ?? __('Continue questionnaire') }}
                    @endslot
                @endcomponent
            </form>
        </div>
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