<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Questionnaire\Traits\OptionsTrait;

class QuestionnaireInvite extends Model
{
    use HasFactory;
    use OptionsTrait;

    protected $fillable = [
        'name',
        'email',
        'project_name',
        'owner_email',
        'owner_name',
        'hash',
        'reminder_count',
        'options',
        'expires_at',
    ];

    protected $guarded = ['id'];
    protected $table = 'questionnaire_invites';

    protected $casts = [
        'name' => 'encrypted',
        'email' => 'encrypted',
        'project_name' => 'encrypted',
        'reminder_count' => 'integer',
        'options' => AsCollection::class,
        'expires_at' => 'datetime',
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
            $questionnaire = Questionnaire::where('slug', config('questionnaire.questionnaire_code'))->firstOrFail();

            $model->questionnaire()->associate($questionnaire);

            self::generateHash($model);
        });
    }

    protected static function generateHash($model)
    {
        if ( ! $model->hash) {
            $model->hash = md5(implode(',', [$model->name, $model->email, $model->project_name, time()]));
        }
    }

    public function questionnaire() : BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function questionnaire_entry() : BelongsTo
    {
        return $this->belongsTo(QuestionnaireEntry::class);
    }

}
