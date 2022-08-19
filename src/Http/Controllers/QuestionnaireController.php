<?php

namespace Questionnaire\Http\Controllers;

use Questionnaire\Models\Page;
use Questionnaire\Models\Questionnaire;

class QuestionnaireController extends Controller
{

    public function redirect()
    {
        $this->checkForQuestionnaireCode();
        
        $questionnaire = Questionnaire::where('slug', config('questionnaire.questionnaire_code'))
            ->with('pages', function($query) {
                $query->active();
            })
            ->firstOrFail();

        if ($questionnaire->has_intro) {
            return redirect(route('questionnaire.intro', [$questionnaire]));
        }

        return redirect($this->firstPageUrl($questionnaire));
    }

    public function startAgain()
    {
        session()->forget([
            'questionnaire.name',
            'questionnaire.email',
            'questionnaire.page',
        ]);

        return redirect(route('home'));
    }

    public function specificPage($pageCode)
    {
        $this->checkForQuestionnaireCode();

        $page = Page::where('slug', $pageCode)
            ->whereHas('questionnaire', function($query) {
                $query->where('slug', config('questionnaire.questionnaire_code'));
            })
            ->with('questionnaire')
            ->firstOrFail();

        return redirect(route('questionnaire.page', [$page->questionnaire->slug, $page->slug]));
    }

    public function index(Questionnaire $questionnaire)
    {
        $url = $this->firstPageUrl($questionnaire);

        return view('questionnaire.intro', compact('questionnaire', 'url'));
    }

    protected function firstPageUrl(Questionnaire $questionnaire)
    {
        $page = $questionnaire->pages()->active()->ordered()->first();

        return route('questionnaire.page', [$questionnaire->slug, $page->slug]);
    }

    protected function checkForQuestionnaireCode()
    {
        if (empty(config('questionnaire.questionnaire_code')) || ! config('questionnaire.questionnaire_code')) {
            exit('No questionnaire set');
        }
    }

}
