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

    protected $progressPages;

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
        'questionnaire_owner_email',
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

    public function __construct(array $attributes = [])
    {
        $this->setConnection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'));

        parent::__construct($attributes);
    }

    public function legal_page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->ordered();
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

    public function getProgressPagesAmount(): int
    {
        if ($this->hasProgressPages()) {
            $this->getProgressPages();

            return sizeof($this->progressPages);
        }

        return 0;
    }

    public function getProgressPages(): Collection
    {
        if ($this->progressPages) {
            return $this->progressPages;
        }

        if ($this->hasProgressPages()) {
            $this->progressPages = Page::whereIn('id', explode(',', $this->progress_page_ids))->with('questions')->get();

            return $this->progressPages;
        }

        return new Collection();
    }

    public function getProgressStepThisPage(Page $page): int
    {
        $this->getProgressPages();

        for($i = 0; $i < sizeof($this->progressPages); $i++) {
            if ($page->id == $this->progressPages[$i]->id) {
                return $i + 1;
            }
        }

        return 0;
    }

    public function showProgressForThisPage(Page $page): bool
    {
        $this->getProgressPages();

        foreach($this->progressPages as $progressPage) {
            if ($page->id == $progressPage->id) {
                return true;
            }
        }

        return false;
    }

}
