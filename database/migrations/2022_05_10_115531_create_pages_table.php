<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained();
            $table->string('title')->nullable();
            $table->string('slug');
            $table->text('intro')->nullable();
            $table->string('continue_button_label')->nullable();
            $table->string('custom_view_template')->nullable();
            $table->tinyInteger('order_column');
            $table->boolean('is_active')->default(1);
            $table->boolean('show_help_aside')->default(0);
            $table->boolean('show_questions_numbered')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
};
