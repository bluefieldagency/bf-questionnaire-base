@extends('questionnaire::bf_layout')

@section('content')

    <div class="content-container grey-bg">
        <div class="content-center content-center--large">
            <div class="content-with-image">
                <div class="right">
                    <h1>{{ $questionnaire->title }}</h1>

                    <p>
                        Deze vragenlijst mist nog pagina's.
                        @if (Route::has('filament.admin.resources.pages.index'))
                            Voeg dit toe in de <a href="{{ route('filament.admin.resources.pages.index') }}?tableFilters[questionnaire][questionnaire]={{ $questionnaire->id }}">beheeromgeving</a>.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
