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

    protected $fillable = [
        'name',
        'email',
        'answers',
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

}
