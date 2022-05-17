<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Models\Page;
use App\Models\Questionnaire;
use App\Models\QuestionnaireEntry;

class PageController extends Controller
{

    public function index(Questionnaire $questionnaire, Page $page)
    {
        return view('questionnaire.page', compact('questionnaire', 'page'));
    }

    public function store(PageRequest $request, Questionnaire $questionnaire, Page $page)
    {
        session([('questionnaire.page.' . $page->id) => $request->except('_token')]);

        $this->storeSpecificValues($request, $page);

        // Determine the next page to show to the attendee
        $nextPage = $this->getNextPage($questionnaire, $page);
        if ($nextPage) {
            return redirect(route('questionnaire.page', [$questionnaire->slug, $nextPage->slug]));
        }

        // No more pages to do? Then we are done!
        $this->completeQuestionnaire($questionnaire, $page);

        return redirect(route('questionnaire.completed', [$questionnaire->slug]));
    }

    protected function storeSpecificValues($request, Page $page)
    {
        $specificValues = [
            'name',
            'email',
        ];

        foreach ($specificValues as $specificValue) {
            foreach ($page->questions as $question) {
                if (isset($question->options['data_type']) && $question->options['data_type'] == $specificValue) {
                    session([('questionnaire.' . $specificValue) => $request->input('question_' . $question->id . '_answer')]);
                }
            }
        }
    }

    protected function getNextPage(Questionnaire $questionnaire, Page $page)
    {
        $questionnairePages = $questionnaire->pages()->ordered()->get();

        $foundKey = null;
        foreach ($questionnairePages as $key => $questionnairePage) {
            if ($page->id == $questionnairePage->id) {
                $foundKey = $key;
            }
        }
        $nextKey = ++$foundKey;

        if (isset($questionnairePages[$nextKey])) {
            return $questionnairePages[$nextKey];
        }

        return null;
    }

    protected function completeQuestionnaire(Questionnaire $questionnaire, Page $page)
    {
        $questionnaireEntry = $this->storeEntry($questionnaire);

        session([('questionnaire.filled.' . $questionnaireEntry->id) => $questionnaireEntry->id]);

        $this->handler = app($questionnaire->handler_class);

        $this->handler->complete($questionnaire);

        session()->forget([
            'questionnaire.name',
            'questionnaire.email',
            'questionnaire.page',
        ]);
    }

    protected function storeEntry(Questionnaire $questionnaire)
    {
        $questionnaireEntry = new QuestionnaireEntry();

        if (session()->has('questionnaire.name')) {
            $questionnaireEntry->name = session('questionnaire.name');
        }

        if (session()->has('questionnaire.email')) {
            $questionnaireEntry->email = session('questionnaire.email');
        }

        $questionnaireEntry->answers = json_encode(session('questionnaire.page'));

        $questionnaire->questionnaire_entries()->save($questionnaireEntry);

        return $questionnaireEntry;
    }

}
