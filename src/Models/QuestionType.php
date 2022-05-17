<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'default_options',
    ];

    protected $casts = [
        'default_options' => AsCollection::class,
    ];

}
