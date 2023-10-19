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
        QuestionType::where('type', 'file')->update(['is_selectable' => '0']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        QuestionType::where('type', 'file')->update(['is_selectable' => '1']);
    }
};
