<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Questionnaire\Traits\OptionsTrait;
use Questionnaire\Traits\ReplacementsTrait;
use Questionnaire\Factories\QuestionnaireFactory;

class Questionnaire extends Model
{
    use HasFactory;
    use SoftDeletes;
    use OptionsTrait;
    use ReplacementsTrait;

    protected $progressPages;

    protected $fillable = [
        'progress_page_ids',
        'handler_class',
        'company_name',
        'company_logo',
        'title',
        'slug',
        'intro',
        'start_button_label',
        'time_indicator',
        'questionnaire_owner_email',
        'options',
        'is_active',
        'show_progress_text',
        'has_intro',
    ];

    protected $casts = [
        'options' => AsCollection::class,
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

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            self::generateHash($model);
        });
    }

    protected static function generateHash($model)
    {
        if ( ! $model->hash) {
            $model->hash = md5(implode(',', [$model->title, $model->start_button_label, $model->tenant_id, time()]));
        }
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return QuestionnaireFactory::new();
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

    public function questionnaire_invites(): HasMany
    {
        return $this->hasMany(QuestionnaireInvite::class);
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
            $progressPageIds = explode(',', $this->progress_page_ids);

            if (session()->has('questionnaire.' . $this->id . '.loaded_pages')) {
                $progressPages = [];
                foreach(session('questionnaire.' . $this->id . '.loaded_pages') as $loadedPage) {
                    if (in_array($loadedPage->id, $progressPageIds)) {
                        $progressPages[] = $loadedPage;
                    }
                }

                $this->progressPages = collect($progressPages);
            } else {
                $this->progressPages = Page::whereIn('id', $progressPageIds)->with(['questions' => function($query) {
                    $query->whereNull('parent_id');
                }])->get();
            }

            return $this->progressPages;
        }

        return new Collection();
    }

    public function getProgressStepThisPage(Page $page): int
    {
        $this->getProgressPages();

        if ($this->progressPages) {
            for($i = 0; $i < sizeof($this->progressPages); $i++) {
                if ($page->id == $this->progressPages[$i]->id) {
                    return $i + 1;
                }
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

    public function getRoutePrefix(): mixed
    {
        if ($this->hasOption('route_prefix')) {
            return $this->getOption('route_prefix');
        }

        return 'questionnaire.';
    }

    public function getRouteNameFor($suffix): string
    {
        $prefix = rtrim($this->getRoutePrefix(), '.') . '.';

        return $prefix . $suffix;
    }

    static public function resetSession()
    {
        session()->forget([
            'tenant.id',
            'questionnaire.id',
            'questionnaire.entry_id',
            'questionnaire.page',
            'questionnaire.hidden_inputs',
            'questionnaire.file',
            'questionnaire.invite_id',
            'questionnaire.for_departments',
            'questionnaire.project_name',
            'questionnaire.progress',
        ]);

        foreach(QuestionnaireEntry::$fixedDataTypes as $dataType) {
            session()->forget([
                ('questionnaire.' . $dataType),
            ]);
        }
    }

}
