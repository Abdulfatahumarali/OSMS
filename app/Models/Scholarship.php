<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'award_value', 'opens_at', 'closes_at',
        'min_gpa', 'programme_of_study', 'min_year_of_study', 'nationality',
        'requires_financial_need', 'is_published', 'created_by',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'is_published' => 'boolean',
        'requires_financial_need' => 'boolean',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function workflowStages()
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('stage_order');
    }

    /**
     * BR-05: applications are only accepted within the configured window.
     */
    public function isOpenForApplications(): bool
    {
        $now = now();

        return $this->is_published
            && $now->greaterThanOrEqualTo($this->opens_at)
            && $now->lessThanOrEqualTo($this->closes_at);
    }
}
