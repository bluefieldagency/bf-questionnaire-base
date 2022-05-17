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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained();
            $table->unsignedBigInteger('question_category_id')->nullable()->index();
            $table->foreignId('question_type_id')->constrained();
            $table->string('title');
            $table->json('options')->nullable();
            $table->integer('order_column');
            $table->boolean('is_active')->default(1);
            $table->boolean('is_required');
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
        Schema::dropIfExists('questions');
    }
};
