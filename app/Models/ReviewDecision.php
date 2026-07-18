<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'workflow_stage_id', 'reviewer_id', 'decision', 'comments', 'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function workflowStage()
    {
        return $this->belongsTo(WorkflowStage::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
