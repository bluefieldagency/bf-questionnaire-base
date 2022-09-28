@if (session()->has('questionnaire.file.' . $page->id . '.' . $question->id))
    <div class="file-preview-container">

        @foreach(session('questionnaire.file.' . $page->id . '.' . $question->id) as $file)
            <div class="file-preview">
                {{ $file['original_name'] }}

                <span class="group pointer file-preview-remove" title="Verwijderen" data-remove="{{ $file['stored_as'] }}">
                    @svg('delete')
                </span>
            </div>
        @endforeach

    </div>
@endif