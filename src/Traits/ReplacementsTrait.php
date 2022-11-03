<?php

namespace Questionnaire\Traits;

use Questionnaire\Models\QuestionnaireEntry;

trait ReplacementsTrait
{

    public function doReplacements($value)
    {
        foreach(QuestionnaireEntry::$fixedDataTypes as $fixedDataType) {
            $value = str_replace(('[' . $fixedDataType . ']'), session('questionnaire.' . $fixedDataType), $value);
        }

        if (session()->has('handler_class')) {
            $handler = app(session('handler_class'));

            if (method_exists($handler, 'enrichTitle')) {
                $value = $handler->enrichTitle($value);
            }
        }

        return $value;
    }

}
