<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Questionnaire extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'progress_page_ids',
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
        'show_progress_text',
        'has_intro',
    ];

    protected $casts = [
        'time_indicator' => 'integer',
        'is_active' => 'boolean',
        'show_progress_text' => 'boolean',
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

    public function hasProgressPages(): bool
    {
        if ($this->progress_page_ids) {
            return true;
        }

        return false;
    }

    public function getProgressPages(): Collection
    {
        if ($this->hasProgressPages()) {
            return Pages::whereIn('id', $this->progress_page_ids)->get();
        }

        return new Collection();
    }

}
