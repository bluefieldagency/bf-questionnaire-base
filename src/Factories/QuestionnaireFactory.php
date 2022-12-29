<?php

namespace Questionnaire\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Questionnaire\Models\Questionnaire;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Questionnaire\Models\Questionnaire>
 */
class QuestionnaireFactory extends Factory
{

    protected $model = Questionnaire::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'company_name' => $this->faker->name(),
            'company_logo' => $this->faker->image(),
            'title' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'questionnaire_owner_email' => $this->faker->email(),
        ];
    }

}
