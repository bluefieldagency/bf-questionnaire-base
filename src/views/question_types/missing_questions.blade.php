@extends('questionnaire::bf_layout')

@section('content')

    <div class="content-container grey-bg">
        <div class="content-center content-center--large">
            <div class="content-with-image">
                <div class="right">
                    <h1>{{ $questionnaire->title }}</h1>

                    <p>
                        Deze pagina mist nog vragen.
                        @if (Route::has('filament.admin.resources.questions.index'))
                            voeg dit toe in de <a href="{{ route('filament.admin.resources.questions.index') }}?tableFilters[page][page]={{ $page->id }}">beheeromgeving</a>.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
