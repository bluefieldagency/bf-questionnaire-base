<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Questionnaire\Models\QuestionType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('question_types', function (Blueprint $table) {
            $table->smallInteger('order_column')->after('options');
        });

        // define the new order based on the order in the following array
        $types = [
            'text',
            'email',
            'textarea',
            'radio',
            'checkbox',
            'select',
            'range',
            'stars',
            'nps',
            'ces',
            'file',
            'hidden',
        ];
        $typeIds = [];
        foreach($types as $type) {
            $questionType = QuestionType::where('type', $type)->first();
            if ($questionType) {
                $typeIds[] = $questionType->id;
            }
        }

        QuestionType::setNewOrder($typeIds);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('question_types', function (Blueprint $table) {
            $table->dropColumn('order_column');
        });
    }
};
