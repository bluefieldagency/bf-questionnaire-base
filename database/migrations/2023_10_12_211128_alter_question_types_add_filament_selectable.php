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
        if (Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->hasColumn('question_types', 'is_selectable')) {
            return;
        }

        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('question_types', function (Blueprint $table) {
            $table->boolean('is_selectable')->default('1')->after('options');
        });

        QuestionType::where('type', 'hidden')->update(['is_selectable' => '0']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('question_types', function (Blueprint $table) {
            $table->dropColumn('is_selectable');
        });
    }
};
