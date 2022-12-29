<?php

namespace Questionnaire\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Questionnaire\Models\QuestionnaireEntry;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Questionnaire\Models\QuestionnaireEntry>
 */
class QuestionnaireEntryFactory extends Factory
{

    protected $model = QuestionnaireEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'answers' => 'test',
        ];
    }

}
