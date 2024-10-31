<?php

namespace Questionnaire\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;
use Questionnaire\Factories\QuestionnaireEntryFactory;

class QuestionnaireEntry extends Model
{

    use HasFactory;
    use SoftDeletes;
    use OptionsTrait;

    protected $givenAnswers = null;
    protected $givenFiles = null;
    protected $scoredScores = null;

    protected $fillable = [
        'hash',
        'name',
        'email',
        'project_name',
        'answers',
        'files',
        'scores',
        'progress',
        'options',
    ];

    protected $casts = [
        'options' => AsCollection::class,
        'scores' => AsCollection::class,
        'name' => 'encrypted',
        'email' => 'encrypted',
        'project_name' => 'encrypted',
        'answers' => 'encrypted',
        'files' => 'encrypted:collection',
    ];

    static public array $fixedDataTypes = [
        'name',
        'email',
        'project_name'
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'));

        parent::__construct($attributes);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ( ! $model->hash) {
                $model->hash = md5(implode('', [
                    $model->name,
                    $model->email,
                    $model->project_name,
                    env('HASH_SALT'),
                ]));
            }
        });
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return QuestionnaireEntryFactory::new();
    }

    public function questionnaire() : BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function user() : BelongsTo
    {
        return $this->setConnection('mysql')->belongsTo(User::class);
    }

    public function questionnaire_invite() : HasOne
    {
        return $this->hasOne(QuestionnaireInvite::class);
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

    public function getFiles()
    {
        if ( ! $this->givenFiles) {
            $this->givenFiles = json_decode($this->files, true);
        }

        return $this->givenFiles;
    }

    public function getFile($question)
    {
        $this->getFiles();

        if (isset($this->givenFiles[$question->page_id]) && isset($this->givenFiles[$question->page_id][$question->id])) {
            return $this->givenFiles[$question->page_id][$question->id];
        }

        return null;
    }

    public function getScores()
    {
        if ( ! $this->scoredScores) {
            $this->scoredScores = $this->scores;
        }

        return $this->scoredScores;
    }

    public function getScore($category)
    {
        $this->getScores();

        if (isset($this->scoredScores[$category])) {
            return (int) $this->scoredScores[$category];
        }

        return false;
    }

    public function hasScore($category)
    {
        $this->getScores();

        if (isset($this->scoredScores[$category])) {
            return true;
        }

        return false;
    }

    public function setScores($scores)
    {
        $this->scores = $scores;

        $this->scoredScores = $scores;
    }

    public function setScore($key, $value, $makeFloat = true)
    {
        $this->getScores();

        if ($makeFloat) {
            $this->scoredScores[$key] = floatval($value);
        } else {
            $this->scoredScores[$key] = $value;
        }

        $this->scores = $this->scoredScores;
    }

    public function isComplete()
    {
        return $this->progress >= 100;
    }

    public function getFormattedDateAttribute()
    {
        return ucfirst($this->updated_at->translatedFormat('d F Y - h:i:s'));
    }

    public function getAnswersContent()
    {
        $pagesAnswered = [];
        $questions = [];
        $questionnaire = $this->questionnaire;
        $handler = app($questionnaire->handler_class);
        $questionnaire->loadMissing([
            'pages.questions.question_type',
            'pages.questions.answers',
        ]);
        $givenAnswers = json_decode($this->answers, true);

        foreach ($questionnaire->pages as $page) {
            foreach ($page->questions as $question) {
                foreach ($givenAnswers as $givenAnswer) {
                    if ($question->question_type->type == 'hidden') {
                        continue;
                    }

                    if (in_array($question->question_type->type, ['radio', 'checkbox']) && is_array($givenAnswer['question_' . $question->id . '_answer'])) {
                        $selectedAnswers = [];
                        foreach($givenAnswer['question_' . $question->id . '_answer'] as $answerId => $value) {
                            foreach($question->answers as $answer) {
                                if ($answer->id == $answerId) {
                                    $selectedAnswers[] = $answer->title;
                                }
                            }
                        }
                        $foundAnswer = implode(', ', $selectedAnswers);
                    } else if (isset($givenAnswer['question_' . $question->id . '_answer'])) {
                        $foundAnswer = null;

                        foreach ($question->answers as $answer) {
                            if ($answer->id == $givenAnswer['question_' . $question->id . '_answer']) {
                                $foundAnswer = $answer->title;
                            }
                        }

                        if ( ! $foundAnswer) {
                            $foundAnswer = $givenAnswer['question_' . $question->id . '_answer'];
                        }
                    }

                    $questions[$question->id] = $foundAnswer;

                    $pagesAnswered[$page->id] = true;
                }
            }
        }

        $reponse = [
            'properties' => [],
            'fixed_data_types' => [],
            'pages' => [],
            'options' => [],
            'scores' => [],
        ];

        $reponse['properties']['id'] = $this->id;
        $reponse['properties']['created_at'] = $this->created_at;
        $reponse['properties']['updated_at'] = $this->updated_at;

        foreach(QuestionnaireEntry::$fixedDataTypes as $fixedDataType) {
            $reponse['fixed_data_types'][$fixedDataType] = $this->getAttribute($fixedDataType);
        }

        foreach($this->options as $key => $option) {
            $reponse['options'][$key] = $option;
        }

        foreach($this->scores as $key => $score) {
            $reponse['scores'][$key] = $score;
        }

        foreach($questionnaire->pages as $page) {
            if ($pagesAnswered[$page->id]) {
                $reponse['pages'][$page->id] = [
                    'questions' => [],
                ];

                foreach ($page->questions as $question) {
                    if (isset($questions[$question->id])) {
                        $reponse['pages'][$page->id]['questions'][] = [
                            'id' => $question->id,
                            'given_answer_value' => $questions[$question->id],
                            'title' => $handler->enrichTitle($question, $question->title),
                            'title_raw' => $question->title,
                        ];
                    }
                }
            }
        }

        return $reponse;
    }

}
