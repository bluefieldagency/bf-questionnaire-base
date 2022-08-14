@if ($question->hasOption('extra_info') && $question->hasOption('extra_info_triggered') && $question->getOption('extra_info_triggered') === true)
    <span class="extra-info--trigger" data-target="extra_info_{{ $question->id }}">
        <div id="extra_info_{{ $question->id }}" class="extra-info--container hidden">
            <div class="extra-info--background">
                <em class="extra-info">{!! $question->getOption('extra_info') !!}</em>
            </div>
        </div>
    </span>
@endif