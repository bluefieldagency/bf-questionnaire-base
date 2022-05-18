<?php

namespace Questionnaire\Models;

use GregoryDuckworth\Encryptable\EncryptableTrait;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireEntry extends Model
{
    use HasFactory;
    use EncryptableTrait;

    protected $givenAnswers = null;
    protected $scoredScores = null;

    protected $fillable = [
        'name',
        'email',
        'answers',
        'scores',
    ];

    protected $casts = [
//        'answers' => AsCollection::class, // do not use this, the resulting value will always be null
    ];

    /**
     * Encryptable Rules
     *
     * @var array
     */
    protected $encryptable = [
        'name',
        'email',
        'answers',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function getAnswers()
    {
        if ( ! $this->givenAnswers) {
            $this->givenAnswers = json_decode($this->answers, true);
        }

        return $this->givenAnswers;
    }

    public function getAnswer($question)
    {
        $this->getAnswers();

        if (isset($this->givenAnswers[$question->page_id]) && isset($this->givenAnswers[$question->page_id]['question_' . $question->id . '_answer'])) {
            return $this->givenAnswers[$question->page_id]['question_' . $question->id . '_answer'];
        }

        return null;
    }

    public function getScores()
    {
        if ( ! $this->scoredScores) {
            $this->scoredScores = json_decode($this->scores, true);
        }

        return $this->scoredScores;
    }

    public function getScore($category)
    {
        $this->getScores();

        if (isset($this->scoredScores[$category])) {
            return $this->scoredScores[$category];
        }

        return 0;
    }

}
