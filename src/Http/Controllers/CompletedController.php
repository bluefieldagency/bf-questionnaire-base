<?php

namespace Bluefield\Questionnaire\Http\Controllers;

use Bluefield\Questionnaire\Models\Questionnaire;
use Bluefield\Questionnaire\Models\QuestionnaireEntry;

class CompletedController extends Controller
{

    public function index(Questionnaire $questionnaire)
    {
        $questionnaireEntry = null;
        if (session()->has('questionnaire.filled')) {
            $questionnaireEntry = QuestionnaireEntry::where('questionnaire_id', $questionnaire->id)
                ->find(last(session('questionnaire.filled')));
        }

        if ( ! $questionnaireEntry) {
            return redirect(route('questionnaire.intro', [$questionnaire]));
        }

        return view('questionnaire.completed', compact('questionnaire', 'questionnaireEntry'));
    }

}
