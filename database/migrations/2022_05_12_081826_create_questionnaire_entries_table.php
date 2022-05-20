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
        Schema::create('questionnaire_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained();
            $table->text('name')->nullable();
            $table->text('email')->nullable();
            $table->longText('answers');
            $table->json('scores')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questionnaire_entries');
    }
};
