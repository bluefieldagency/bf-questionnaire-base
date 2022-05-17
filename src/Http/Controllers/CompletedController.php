<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\QuestionnaireEntry;

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
