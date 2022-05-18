<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;

class QuestionType extends Model
{
    use HasFactory;
    use SoftDeletes;
    use OptionsTrait;

    protected $fillable = [
        'type',
        'default_options',
    ];

    protected $casts = [
        'default_options' => AsCollection::class,
    ];

}
