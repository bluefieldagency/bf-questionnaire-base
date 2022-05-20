<?php

namespace Questionnaire\Http\Controllers;

use Questionnaire\Models\Questionnaire;

class QuestionnaireController extends Controller
{

    public function redirect()
    {
        if (empty(config('questionnaire.questionnaire_code')) || ! config('questionnaire.questionnaire_code')) {
            exit('No questionnaire set');
        }
        
        $questionnaire = Questionnaire::where('slug', config('questionnaire.questionnaire_code'))
            ->with('pages')
            ->firstOrFail();

        if ($questionnaire->has_intro) {
            return redirect(route('questionnaire.intro', [$questionnaire]));
        }

        return redirect($this->firstPageUrl($questionnaire));
    }

    public function index(Questionnaire $questionnaire)
    {
        $url = $this->firstPageUrl($questionnaire);

        return view('questionnaire.intro', compact('questionnaire', 'url'));
    }

    protected function firstPageUrl(Questionnaire $questionnaire)
    {
        $page = $questionnaire->pages()->ordered()->first();

        return route('questionnaire.page', [$questionnaire->slug, $page->slug]);
    }

}
