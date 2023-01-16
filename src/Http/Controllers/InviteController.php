<?php

namespace Questionnaire\Http\Controllers;

use Questionnaire\Models\Page;
use Questionnaire\Models\QuestionnaireEntry;
use Questionnaire\Models\QuestionnaireInvite;

class InviteController extends QuestionnaireController
{

    public function handleInvite(QuestionnaireInvite $questionnaireInvite)
    {
        foreach(QuestionnaireEntry::$fixedDataTypes as $fixedDataType) {
            session([
                ('questionnaire.' . $fixedDataType) => $questionnaireInvite->getAttribute($fixedDataType),
            ]);
        }

        session(['questionnaire.invite_id' => $questionnaireInvite->id]);
        session()->save();
    }

    public function requiresInvite()
    {
        return view('questionnaire.requires-invite');
    }

    protected function handlePageIds($questionnaireInvite)
    {
        // if this questionnaire uses fixed pages, we have to load them (in the correct order)
        if ($questionnaireInvite->hasOption('page_ids')) {
            $questionnaire = $questionnaireInvite->questionnaire;

            $handler = app($questionnaire->handler_class);

            $pageIds = explode(',', $questionnaireInvite->getOption('page_ids'));

            if (sizeof($pageIds)) {
                $pages = Page::where('questionnaire_id', $questionnaire->id)->active()->get()->keyBy('id');

                foreach($pageIds as $key => $pageId) {
                    if (isset($pages[$pageId])) {
                        if ( ! $questionnaire->relationLoaded('pages')) {
                            $pagesArray = [$pages[$pageId]];
                            $questionnaire->setRelation('pages', collect($pagesArray));
                        } else {
                            $questionnaire->pages->push($pages[$pageId]);
                        }
                    } else {
                        unset($pageIds[$key]);
                    }
                }

                if (sizeof($pageIds)) {
                    session(['questionnaire.loaded_pages' => $questionnaire->pages]);

                    return route($questionnaireInvite->questionnaire->getRouteNameFor('page'), [$questionnaireInvite->questionnaire->slug, $questionnaire->pages->first()->slug]);
                }
            }
        }

        return null;
    }

}
