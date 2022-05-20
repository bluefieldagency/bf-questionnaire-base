<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'handler_class',
        'company_name',
        'company_logo',
        'title',
        'slug',
        'intro',
        'intro',
        'start_button_label',
        'time_indicator',
        'is_active',
        'has_intro',
    ];

    protected $casts = [
        'time_indicator' => 'integer',
        'is_active' => 'boolean',
        'has_intro' => 'boolean',
    ];

    public function legal_page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function question_categories(): HasMany
    {
        return $this->hasMany(QuestionCategory::class);
    }

    public function questionnaire_entries(): HasMany
    {
        return $this->hasMany(QuestionnaireEntry::class);
    }

}
